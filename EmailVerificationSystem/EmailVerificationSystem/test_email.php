<?php
// Test script to verify email functionality
require_once 'functions.php';
require_once 'mail_config.php';

echo "=== Email Functionality Test ===\n\n";

// Test 1: Basic email configuration
echo "1. Testing basic email configuration...\n";
echo "PHP mail function available: " . (function_exists('mail') ? "YES" : "NO") . "\n";
echo "Sendmail path: " . ini_get('sendmail_path') . "\n\n";

// Test 2: Generate verification code
echo "2. Testing verification code generation...\n";
$testCode = generateVerificationCode();
echo "Generated code: $testCode\n";
echo "Code length: " . strlen($testCode) . " (should be 6)\n\n";

// Test 3: Test email sending (you can replace with your real email)
echo "3. Testing email sending...\n";
$testEmail = 'your-email@example.com'; // Replace with your actual email to test
echo "Test email address: $testEmail\n";

if (testEmailDelivery($testEmail)) {
    echo "✓ Basic email test passed\n";
} else {
    echo "✗ Basic email test failed\n";
}

// Test 4: Test verification email
echo "\n4. Testing verification email sending...\n";
$verificationResult = sendVerificationEmail($testEmail, $testCode);
if ($verificationResult) {
    echo "✓ Verification email sent successfully\n";
} else {
    echo "✗ Verification email failed to send\n";
}

// Test 5: Test unsubscribe email
echo "\n5. Testing unsubscribe email sending...\n";
$unsubscribeResult = sendUnsubscribeConfirmationEmail($testEmail, $testCode);
if ($unsubscribeResult) {
    echo "✓ Unsubscribe email sent successfully\n";
} else {
    echo "✗ Unsubscribe email failed to send\n";
}

// Test 6: Check email log
echo "\n6. Checking email log...\n";
if (file_exists('email_log.txt')) {
    echo "Email log contents:\n";
    echo "-------------------\n";
    echo file_get_contents('email_log.txt');
} else {
    echo "No email log found\n";
}

echo "\n=== Test Complete ===\n";
echo "\nTo test with your actual email:\n";
echo "1. Edit this file and replace 'your-email@example.com' with your real email\n";
echo "2. Run: php test_email.php\n";
echo "3. Check your email inbox for the test messages\n";
?>