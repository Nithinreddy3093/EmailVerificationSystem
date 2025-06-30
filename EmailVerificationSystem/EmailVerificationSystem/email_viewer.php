<?php
// Email viewer for development - shows captured emails with OTP codes
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Viewer - GitHub Timeline Notifier</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">📧 Email Viewer (Development)</h1>
                <p class="text-muted">This page shows all emails that would have been sent, including OTP verification codes.</p>
                
                <?php
                // Check if captured emails file exists
                if (file_exists('captured_emails.txt')) {
                    echo '<div class="alert alert-info">';
                    echo '<strong>Note:</strong> In development mode, emails are captured locally instead of being sent to real email addresses.';
                    echo '</div>';
                    
                    // Read and display captured emails
                    $emailContent = file_get_contents('captured_emails.txt');
                    
                    if (!empty($emailContent)) {
                        // Split emails by the separator
                        $emails = explode('====== EMAIL CAPTURED ======', $emailContent);
                        $emailCount = 0;
                        
                        foreach ($emails as $email) {
                            if (trim($email) !== '') {
                                $emailCount++;
                                echo '<div class="card mb-3">';
                                echo '<div class="card-header">';
                                echo '<h5 class="card-title mb-0">Email #' . $emailCount . '</h5>';
                                echo '</div>';
                                echo '<div class="card-body">';
                                
                                // Parse email content
                                $lines = explode("\n", $email);
                                $to = $subject = $message = '';
                                $date = '';
                                
                                foreach ($lines as $line) {
                                    if (strpos($line, 'Date:') === 0) {
                                        $date = substr($line, 6);
                                    } elseif (strpos($line, 'To:') === 0) {
                                        $to = substr($line, 4);
                                    } elseif (strpos($line, 'Subject:') === 0) {
                                        $subject = substr($line, 9);
                                    } elseif (strpos($line, 'Message:') === 0) {
                                        $message = substr($line, 9);
                                    }
                                }
                                
                                echo '<div class="row">';
                                echo '<div class="col-md-6">';
                                echo '<p><strong>Date:</strong> ' . htmlspecialchars($date) . '</p>';
                                echo '<p><strong>To:</strong> ' . htmlspecialchars($to) . '</p>';
                                echo '<p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>';
                                echo '</div>';
                                echo '<div class="col-md-6">';
                                
                                // Extract OTP code if present
                                if (preg_match('/(\d{6})/', $message, $matches)) {
                                    echo '<div class="alert alert-success">';
                                    echo '<strong>🔑 OTP Code:</strong> <span class="h4">' . $matches[1] . '</span>';
                                    echo '</div>';
                                }
                                
                                echo '</div>';
                                echo '</div>';
                                
                                echo '<div class="mt-3">';
                                echo '<strong>Email Content:</strong>';
                                echo '<div class="border p-3 mt-2" style="background-color: #f8f9fa;">';
                                echo $message; // Display HTML content directly
                                echo '</div>';
                                echo '</div>';
                                
                                echo '</div>';
                                echo '</div>';
                            }
                        }
                        
                        if ($emailCount === 0) {
                            echo '<div class="alert alert-warning">';
                            echo '<strong>No emails found.</strong> Try registering an email address to see captured emails here.';
                            echo '</div>';
                        }
                        
                    } else {
                        echo '<div class="alert alert-warning">';
                        echo '<strong>No emails captured yet.</strong> Register an email address to see emails appear here.';
                        echo '</div>';
                    }
                    
                } else {
                    echo '<div class="alert alert-warning">';
                    echo '<strong>Email capture file not found.</strong> No emails have been sent yet.';
                    echo '</div>';
                }
                ?>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <a href="index.php" class="btn btn-primary">← Back to Registration</a>
                    </div>
                    <div class="col-md-6 text-end">
                        <?php if (file_exists('captured_emails.txt')): ?>
                            <form method="post" style="display: inline;">
                                <button type="submit" name="clear_emails" class="btn btn-outline-danger" 
                                        onclick="return confirm('Clear all captured emails?')">
                                    Clear Email History
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Handle clear emails request
    if (isset($_POST['clear_emails'])) {
        if (file_exists('captured_emails.txt')) {
            unlink('captured_emails.txt');
        }
        if (file_exists('email_log.txt')) {
            unlink('email_log.txt');
        }
        echo '<script>window.location.reload();</script>';
    }
    ?>
</body>
</html>