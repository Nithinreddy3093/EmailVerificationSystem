<?php
session_start();
require_once 'functions.php';

$message = '';
$error = '';

// Handle unsubscribe form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email']) && !empty($_POST['unsubscribe_email'])) {
        $email = filter_var($_POST['unsubscribe_email'], FILTER_VALIDATE_EMAIL);
        if ($email) {
            // Check if email is registered in database
            $pdo = getDatabaseConnection();
            $isRegistered = false;
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM registered_emails WHERE email = ? AND is_active = TRUE");
                    $stmt->execute([$email]);
                    $isRegistered = $stmt->rowCount() > 0;
                } catch (PDOException $e) {
                    error_log("Database error checking email: " . $e->getMessage());
                }
            }
            if ($isRegistered) {
                $code = generateVerificationCode();
                $_SESSION['unsubscribe_email'] = $email;
                $_SESSION['unsubscribe_code'] = $code;
                $_SESSION['unsubscribe_timestamp'] = time();
                
                if (sendUnsubscribeConfirmationEmail($email, $code)) {
                    $message = 'Unsubscribe confirmation code sent! For development, your code is: <strong>' . $code . '</strong>';
                    $_SESSION['step'] = 'unsubscribe_verify';
                } else {
                    $error = 'Failed to send confirmation email. Please try again.';
                }
            } else {
                $error = 'Email not found in our subscription list.';
            }
        } else {
            $error = 'Please enter a valid email address.';
        }
    } elseif (isset($_POST['unsubscribe_verification_code'])) {
        if (isset($_SESSION['unsubscribe_code']) && isset($_SESSION['unsubscribe_email'])) {
            $enteredCode = trim($_POST['unsubscribe_verification_code']);
            $storedCode = $_SESSION['unsubscribe_code'];
            $timestamp = $_SESSION['unsubscribe_timestamp'];
            
            // Check if code is expired (10 minutes)
            if (time() - $timestamp > 600) {
                $error = 'Confirmation code has expired. Please request a new one.';
                unset($_SESSION['unsubscribe_code'], $_SESSION['unsubscribe_email'], $_SESSION['unsubscribe_timestamp'], $_SESSION['step']);
            } elseif ($enteredCode === $storedCode) {
                if (unsubscribeEmail($_SESSION['unsubscribe_email'])) {
                    $message = 'Successfully unsubscribed from GitHub timeline updates.';
                    unset($_SESSION['unsubscribe_code'], $_SESSION['unsubscribe_email'], $_SESSION['unsubscribe_timestamp'], $_SESSION['step']);
                } else {
                    $error = 'Failed to unsubscribe. Please try again.';
                }
            } else {
                $error = 'Invalid confirmation code. Please try again.';
            }
        } else {
            $error = 'Session expired. Please start the unsubscribe process again.';
            unset($_SESSION['step']);
        }
    }
}

$step = isset($_SESSION['step']) ? $_SESSION['step'] : 'unsubscribe';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - GitHub Timeline Notifications</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h1 class="mb-0"><i class="fas fa-user-minus me-2"></i>Unsubscribe</h1>
                        <p class="text-muted mb-0">Stop receiving GitHub timeline updates</p>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($step === 'unsubscribe'): ?>
                            <h3 class="mb-4">Enter Your Email</h3>
                            <form method="POST" class="mb-4">
                                <div class="mb-3">
                                    <label for="unsubscribe_email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="unsubscribe_email" id="unsubscribe_email" required placeholder="Enter your email address">
                                </div>
                                <button type="submit" id="submit-unsubscribe" class="btn btn-warning">
                                    <i class="fas fa-paper-plane me-2"></i>Send Confirmation Code
                                </button>
                            </form>
                        <?php elseif ($step === 'unsubscribe_verify'): ?>
                            <h3 class="mb-4">Confirm Unsubscription</h3>
                            <p class="text-muted mb-3">We've sent a confirmation code to <strong><?php echo htmlspecialchars($_SESSION['unsubscribe_email']); ?></strong></p>
                            <form method="POST" class="mb-4">
                                <div class="mb-3">
                                    <label for="unsubscribe_verification_code" class="form-label">Confirmation Code</label>
                                    <input type="text" class="form-control" name="unsubscribe_verification_code" id="unsubscribe_verification_code" maxlength="6" required placeholder="Enter 6-digit code">
                                </div>
                                <button type="submit" id="verify-unsubscribe" class="btn btn-danger">
                                    <i class="fas fa-check me-2"></i>Confirm Unsubscription
                                </button>
                                <a href="unsubscribe.php" class="btn btn-secondary ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </a>
                            </form>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="fas fa-home me-2"></i>Return to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
