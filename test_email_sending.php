<?php
/**
 * Test script to check if email sending is configured correctly
 * Run: php test_email_sending.php your-email@example.com
 */

require_once 'includes/env.php';
require_once 'includes/mailer.php';

// Get email from command line or use default
$test_email = $argv[1] ?? 'test@example.com';

echo "=== Email Configuration Test ===\n\n";

// Check if .env file exists
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    echo "✓ .env file found\n";
} else {
    echo "✗ .env file NOT found\n";
    echo "  Please create .env file with:\n";
    echo "  GMAIL_USERNAME=your-gmail@gmail.com\n";
    echo "  GMAIL_APP_PASSWORD=your-app-password\n";
    echo "  FROM_EMAIL=your-gmail@gmail.com\n";
    echo "  FROM_NAME=NoteShare\n\n";
}

// Check environment variables
echo "\n=== Environment Variables ===\n";
$username = getenv('GMAIL_USERNAME');
$password = getenv('GMAIL_APP_PASSWORD');
$from_email = getenv('FROM_EMAIL');

echo "GMAIL_USERNAME: " . ($username ? "✓ Set (ends with: " . substr($username, -10) . ")" : "✗ Not set") . "\n";
echo "GMAIL_APP_PASSWORD: " . ($password ? "✓ Set (" . strlen($password) . " chars)" : "✗ Not set") . "\n";
echo "FROM_EMAIL: " . ($from_email ? "✓ Set ($from_email)" : "✗ Not set") . "\n";

// Test mailer initialization
echo "\n=== Testing Mailer ===\n";
try {
    $mailer = new GmailMailer();
    echo "✓ Mailer initialized\n";
    
    // Test sending OTP
    echo "\n=== Testing OTP Email Send ===\n";
    echo "Sending test OTP to: $test_email\n";
    
    $otp = sprintf('%06d', mt_rand(0, 999999));
    $result = $mailer->sendOTP($test_email, $otp);
    
    if ($result) {
        echo "✓ Email sent successfully!\n";
        echo "✓ OTP: $otp\n";
        echo "✓ Check your inbox and spam folder\n";
    } else {
        echo "✗ Email sending failed!\n";
        echo "✗ Check logs/email_log.txt for details\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Logs ===\n";
echo "Email log: logs/email_log.txt\n";
echo "OTP log: logs/otp_log.txt\n";
echo "\n";
?>

