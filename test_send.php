<?php
require_once __DIR__ . '/includes/env.php';
require_once __DIR__ . '/includes/mailer.php';

$mailer = new GmailMailer();
$result = $mailer->sendOTP('your_test_email@gmail.com', '123456');
var_dump($result);
echo "\nDone\n";
