<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/mailer.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['email'])) {
        throw new Exception('Email is required');
    }

    $email = trim($input['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Strictly enforce VIT email requirement (compatible with older PHP versions)
    $requiredDomain = '@vitstudent.ac.in';
    if (strlen($email) < strlen($requiredDomain) || substr($email, -strlen($requiredDomain)) !== $requiredDomain) {
        throw new Exception('Only VIT student emails (@vitstudent.ac.in) are allowed');
    }

    // Generate 6-digit OTP
    $otp = sprintf('%06d', mt_rand(0, 999999));

    // Store OTP in session with expiration (5 minutes)
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;
    $_SESSION['otp_time'] = time();

    // Configure mailer with Gmail settings from environment
    $mailer = new GmailMailer();

    // Try to send email
    $email_sent = $mailer->sendOTP($email, $otp);

    // Always log OTP for development/debugging
    $log_file = __DIR__ . '/../logs/otp_log.txt';
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $log_entry = date('Y-m-d H:i:s') . " - OTP for {$email}: {$otp} - Email sent: " . ($email_sent ? 'YES' : 'NO') . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);

    if ($email_sent) {
        echo json_encode([
            'success' => true,
            'message' => 'OTP sent successfully to your email! Please check your Gmail inbox (and spam folder).'
        ]);
    } else {
        // Email failed - don't show OTP, just inform user to try again
        // Capture recent email log lines to help debugging on hosts that block SMTP
        $emailLog = __DIR__ . '/../logs/email_log.txt';
        $debugInfo = '';
        if (file_exists($emailLog)) {
            $lines = file($emailLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $last = array_slice($lines, -50);
            $debugInfo = implode("\n", $last);
        }

        // Write detailed failure info to otp log for host-side inspection
        $failure_entry = date('Y-m-d H:i:s') . " - FAILED_SEND - Email: {$email} - Recent email log:\n" . $debugInfo . "\n---\n";
        file_put_contents($log_file, $failure_entry, FILE_APPEND);

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send OTP email. Server-side details were logged for debugging.'
        ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>