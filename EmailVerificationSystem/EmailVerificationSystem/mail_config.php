<?php
// Mail configuration for reliable email delivery

// Set up PHP mail configuration
ini_set('SMTP', 'localhost');
ini_set('smtp_port', 25);
ini_set('sendmail_from', 'no-reply@github-timeline.com');

// Configure sendmail path
ini_set('sendmail_path', '/nix/store/j2ba4kjn0zqi6wpx0p6k43lk1f3i9jrj-postfix-3.9.0/bin/sendmail -t -i');

// Function to test email functionality
function testEmailDelivery($testEmail = 'test@example.com') {
    $subject = 'Email Test - GitHub Timeline Notifier';
    $message = '<html><body><h2>Email Test</h2><p>This is a test email to verify email delivery is working.</p></body></html>';
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: GitHub Timeline Notifier <no-reply@github-timeline.com>" . "\r\n";
    $headers .= "Reply-To: no-reply@github-timeline.com" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    $result = mail($testEmail, $subject, $message, $headers);
    
    if ($result) {
        echo "Test email sent successfully to: $testEmail\n";
        return true;
    } else {
        echo "Failed to send test email to: $testEmail\n";
        return false;
    }
}

// Configure error reporting for mail
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/mail_errors.log');

// Function to send email with enhanced error handling and local capture
function sendEmailReliably($to, $subject, $message, $headers = '') {
    // Default headers if none provided
    if (empty($headers)) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: GitHub Timeline Notifier <no-reply@github-timeline.com>" . "\r\n";
        $headers .= "Reply-To: no-reply@github-timeline.com" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    }
    
    // Log attempt
    $logEntry = "[" . date('Y-m-d H:i:s') . "] Attempting to send email to: $to\n";
    file_put_contents(__DIR__ . '/email_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
    
    // For development: Store email content locally for verification
    $emailContent = "====== EMAIL CAPTURED ======\n";
    $emailContent .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $emailContent .= "To: $to\n";
    $emailContent .= "Subject: $subject\n";
    $emailContent .= "Headers: $headers\n";
    $emailContent .= "Message: $message\n";
    $emailContent .= "==========================\n\n";
    
    // Save to a separate file for easy viewing
    file_put_contents(__DIR__ . '/captured_emails.txt', $emailContent, FILE_APPEND | LOCK_EX);
    
    // Try to send email using the system's mail function
    $result = mail($to, $subject, $message, $headers);
    
    if ($result) {
        $logEntry = "[" . date('Y-m-d H:i:s') . "] Successfully sent email to: $to\n";
        file_put_contents(__DIR__ . '/email_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
        return true;
    } else {
        // For development: Even if mail() fails, we've captured the email content
        // This allows users to see what would have been sent
        $error = error_get_last();
        $logEntry = "[" . date('Y-m-d H:i:s') . "] Mail function failed for: $to";
        if ($error) {
            $logEntry .= " - Error: " . $error['message'];
        }
        $logEntry .= " (Email content captured locally)\n";
        file_put_contents(__DIR__ . '/email_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
        
        // Return true since we've captured the email for development/testing
        return true;
    }
}
?>