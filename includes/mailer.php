<?php
/**
 * Gmail SMTP Email Sender using PHPMailer
 */

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once 'env.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class GmailMailer {
    private $smtp_host;
    private $smtp_port;
    private $username;
    private $password;
    private $from_email;
    private $from_name;

    public function __construct($config = []) {
        $this->smtp_host = trim($config['host'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com');
        $this->smtp_port = (int)($config['port'] ?? getenv('SMTP_PORT') ?: 587);
        $this->username = trim($config['username'] ?? getenv('GMAIL_USERNAME') ?: '');

        // Normalize app password: users sometimes paste the 16-char Gmail app password
        // with spaces (e.g. "abcd efgh ijkl mnop"). Remove spaces so authentication works.
        $rawPassword = $config['password'] ?? getenv('GMAIL_APP_PASSWORD') ?: '';
        $this->password = str_replace(' ', '', trim($rawPassword));

        $this->from_email = trim($config['from_email'] ?? getenv('FROM_EMAIL') ?: '');
        $this->from_name = $config['from_name'] ?? getenv('FROM_NAME') ?: 'NoteShare';
        $this->debug = isset($config['debug']) ? (bool)$config['debug'] : (bool)getenv('MAIL_DEBUG');
    }

    public function send($to, $subject, $message, $headers = []) {
        // Check if credentials are configured
        $hasSmtpCreds = !empty($this->username) && !empty($this->password) && !empty($this->from_email);
        // If no SMTP creds but Gmail API creds are available, we'll try API send instead
        $hasGmailApi = getenv('GMAIL_CLIENT_ID') && getenv('GMAIL_CLIENT_SECRET') && getenv('GMAIL_REFRESH_TOKEN');
        if (!$hasSmtpCreds && !$hasGmailApi) {
            $error_msg = 'No email sending credentials found. Set SMTP (GMAIL_USERNAME/GMAIL_APP_PASSWORD) or Gmail API (GMAIL_CLIENT_ID/GMAIL_CLIENT_SECRET/GMAIL_REFRESH_TOKEN).';
            $this->logEmail($to, $subject, $message, 'FAILED - ' . $error_msg);
            error_log("GmailMailer Error: " . $error_msg);
            return false;
        }

        // Try actual SMTP sending with PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Debug output: enable via MAIL_DEBUG env var (true/1)
            if ($this->debug) {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $mail->Debugoutput = function($str, $level) {
                    error_log("SMTP Debug [{$level}]: {$str}");
                };
            } else {
                $mail->SMTPDebug = 0;
            }

            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtp_port;

            // Timeouts to prevent hanging
            $mail->Timeout = 30;
            $mail->SMTPKeepAlive = false;

            // Recipients
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;

            // Add additional headers
            foreach ($headers as $key => $value) {
                $mail->addCustomHeader($key, $value);
            }

            $result = $mail->send();

            if ($result) {
                $this->logEmail($to, $subject, $message, 'SUCCESS - Email sent via SMTP');
                return true;
            } else {
                $this->logEmail($to, $subject, $message, 'FAILED - Send returned false');
                // fallthrough to attempt API send if configured
            }

        } catch (Exception $e) {
            $error_msg = $mail->ErrorInfo ?: $e->getMessage();
            $this->logEmail($to, $subject, $message, 'EXCEPTION - ' . $error_msg);

            // Log to PHP error log as well
            error_log("PHPMailer Exception: " . $error_msg);

            // If SMTP fails but Gmail API credentials exist, try API fallback
            if ($hasGmailApi) {
                try {
                    $ok = $this->sendViaGmailAPI($to, $subject, $message);
                    if ($ok) {
                        $this->logEmail($to, $subject, $message, 'SUCCESS - Email sent via Gmail API after SMTP failure');
                        return true;
                    }
                } catch (
                    Exception $apiEx
                ) {
                    $this->logEmail($to, $subject, $message, 'GMAIL_API_EXCEPTION - ' . $apiEx->getMessage());
                }
            }

            // Return false on exception so we can detect email sending failures
            return false;
        }

        // If SMTP send returned false earlier and Gmail API is configured, try it now
        if ($hasGmailApi) {
            try {
                $ok = $this->sendViaGmailAPI($to, $subject, $message);
                if ($ok) {
                    $this->logEmail($to, $subject, $message, 'SUCCESS - Email sent via Gmail API (fallback)');
                    return true;
                }
            } catch (Exception $apiEx) {
                $this->logEmail($to, $subject, $message, 'GMAIL_API_EXCEPTION - ' . $apiEx->getMessage());
            }
        }

        return false;
    }

    private function getAccessTokenFromRefreshToken($clientId, $clientSecret, $refreshToken) {
        $url = 'https://oauth2.googleapis.com/token';
        $post = http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            throw new Exception('Failed to refresh access token: ' . $resp);
        }
        $data = json_decode($resp, true);
        if (empty($data['access_token'])) throw new Exception('No access_token in token response');
        return $data['access_token'];
    }

    private function sendViaGmailAPI($to, $subject, $htmlMessage) {
        $clientId = getenv('GMAIL_CLIENT_ID');
        $clientSecret = getenv('GMAIL_CLIENT_SECRET');
        $refreshToken = getenv('GMAIL_REFRESH_TOKEN');
        $from = $this->from_email ?: getenv('FROM_EMAIL');

        if (!$clientId || !$clientSecret || !$refreshToken || !$from) {
            throw new Exception('Gmail API credentials (client id/secret/refresh token) or FROM_EMAIL missing');
        }

        $accessToken = $this->getAccessTokenFromRefreshToken($clientId, $clientSecret, $refreshToken);

        // Build raw RFC 822 message
        $raw = "From: {$from}\r\n";
        $raw .= "To: {$to}\r\n";
        $raw .= "Subject: {$subject}\r\n";
        $raw .= "MIME-Version: 1.0\r\n";
        $raw .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $raw .= $htmlMessage;

        $rawb64 = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
        $payload = json_encode(['raw' => $rawb64]);

        $ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            return true;
        }
        throw new Exception('Gmail API send failed: HTTP ' . $code . ' - ' . $res);
    }

    private function logEmail($to, $subject, $message, $status) {
        $log_file = __DIR__ . '/../logs/email_log.txt';
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        $log_entry = date('Y-m-d H:i:s') . " - {$status} - Email to: {$to}\nSubject: {$subject}\nMessage: {$message}\n---\n";
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }

    public function sendOTP($to, $otp) {
        $subject = 'NoteShare - Your OTP for Account Verification';

        $message = "
        <html>
        <head>
            <title>NoteShare OTP</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .otp-code { font-size: 32px; font-weight: bold; color: #007bff; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 5px; margin: 20px 0; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Welcome to NoteShare!</h2>
                <p>Your One-Time Password (OTP) for account verification is:</p>
                <div class='otp-code'>{$otp}</div>
                <p>This OTP will expire in 5 minutes.</p>
                <p>If you didn't request this OTP, please ignore this email.</p>

                <div class='footer'>
                    <p>Best regards,<br>NoteShare Team</p>
                    <p><small>This is an automated message. Please do not reply to this email.</small></p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this->send($to, $subject, $message);
    }
}
?>