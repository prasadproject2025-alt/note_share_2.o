<?php
// Simple test script to call the app mailer and show recent email log entries.
// Usage: visit /tools/test_mail.php?to=you@example.com

require_once __DIR__ . '/../includes/mailer.php';

// allow running from browser only (simple guard)
if (php_sapi_name() !== 'cli') {
    // small auth guard - change or remove as needed
    if (!isset($_GET['secret']) || $_GET['secret'] !== 'localdev') {
        http_response_code(403);
        echo "Forbidden. Provide ?secret=localdev to run this test.";
        exit;
    }
}

$to = $_GET['to'] ?? getenv('FROM_EMAIL') ?: 'admin@localhost.localdomain';
$otp = rand(100000, 999999);

// Force debug=true so SMTP debug is enabled in mailer
$mailer = new GmailMailer(['debug' => true]);

$result = $mailer->sendOTP($to, $otp);

echo "Send result: " . ($result ? "OK" : "FAILED") . "\n";

$logFile = __DIR__ . '/../logs/email_log.txt';
if (file_exists($logFile)) {
    echo "\n--- Last email_log.txt entries ---\n";
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $last = array_slice($lines, -50);
    echo implode("\n", $last);
} else {
    echo "\nNo email log found at logs/email_log.txt\n";
}

// Also show last php error log (common locations vary). Try to show the SAPI error log if available
if (function_exists('error_get_last')) {
    $err = error_get_last();
    if ($err) {
        echo "\n--- Last PHP error (error_get_last) ---\n";
        print_r($err);
    }
}

?>