<?php
session_start();
require_once 'functions.php';

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if ($email) {
            $code = generateVerificationCode();
            $_SESSION['verification_email'] = $email;
            $_SESSION['verification_code'] = $code;
            $_SESSION['code_timestamp'] = time();
            
            if (sendVerificationEmail($email, $code)) {
                $message = 'Verification code sent! For development, your code is: <strong>' . $code . '</strong>';
                $_SESSION['step'] = 'verify';
            } else {
                $error = 'Failed to send verification email. Please try again.';
            }
        } else {
            $error = 'Please enter a valid email address.';
        }
    } elseif (isset($_POST['verification_code']) && !empty($_POST['verification_code'])) {
        if (isset($_SESSION['verification_code']) && isset($_SESSION['verification_email'])) {
            $enteredCode = trim($_POST['verification_code']);
            $storedCode = $_SESSION['verification_code'];
            $timestamp = $_SESSION['code_timestamp'];
            
            // Check if code is expired (10 minutes)
            if (time() - $timestamp > 600) {
                $error = 'Verification code has expired. Please request a new one.';
                unset($_SESSION['verification_code'], $_SESSION['verification_email'], $_SESSION['code_timestamp'], $_SESSION['step']);
            } elseif ($enteredCode === $storedCode) {
                if (registerEmail($_SESSION['verification_email'])) {
                    $message = 'Email successfully registered for GitHub timeline updates!';
                    unset($_SESSION['verification_code'], $_SESSION['verification_email'], $_SESSION['code_timestamp'], $_SESSION['step']);
                } else {
                    $error = 'Failed to register email. Please try again.';
                }
            } else {
                $error = 'Invalid verification code. Please try again.';
            }
        } else {
            $error = 'Session expired. Please start the verification process again.';
            unset($_SESSION['step']);
        }
    } elseif (isset($_POST['unsubscribe_email']) && !empty($_POST['unsubscribe_email'])) {
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

$step = isset($_SESSION['step']) ? $_SESSION['step'] : 'email';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Timeline Email Notifications</title>
    <link href="https://cdn.replit.com/agent/bootstrap-agent-dark-theme.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h1 class="mb-0"><i class="fab fa-github me-2"></i>GitHub Timeline Notifications</h1>
                        <p class="text-muted mb-0">Stay updated with the latest GitHub activity</p>
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

                        <?php if ($step === 'email'): ?>
                            <h3 class="mb-4">Subscribe to GitHub Timeline Updates</h3>
                            <form method="POST" class="mb-4">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" id="email" required placeholder="Enter your email address">
                                </div>
                                <button type="submit" id="submit-email" class="btn btn-primary">
                                    <i class="fas fa-envelope me-2"></i>Send Verification Code
                                </button>
                            </form>
                        <?php elseif ($step === 'verify'): ?>
                            <h3 class="mb-4">Verify Your Email</h3>
                            <p class="text-muted mb-3">We've sent a 6-digit verification code to <strong><?php echo htmlspecialchars($_SESSION['verification_email']); ?></strong></p>
                            <form method="POST" class="mb-4">
                                <div class="mb-3">
                                    <label for="verification_code" class="form-label">Verification Code</label>
                                    <input type="text" class="form-control" name="verification_code" id="verification_code" maxlength="6" required placeholder="Enter 6-digit code">
                                </div>
                                <button type="submit" id="submit-verification" class="btn btn-success">
                                    <i class="fas fa-check me-2"></i>Verify
                                </button>
                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Start Over
                                </a>
                            </form>
                        <?php elseif ($step === 'unsubscribe_verify'): ?>
                            <h3 class="mb-4">Confirm Unsubscription</h3>
                            <p class="text-muted mb-3">We've sent a confirmation code to <strong><?php echo htmlspecialchars($_SESSION['unsubscribe_email']); ?></strong></p>
                            <form method="POST" class="mb-4">
                                <div class="mb-3">
                                    <label for="unsubscribe_verification_code" class="form-label">Confirmation Code</label>
                                    <input type="text" class="form-control" name="unsubscribe_verification_code" id="unsubscribe_verification_code" maxlength="6" required placeholder="Enter 6-digit code">
                                </div>
                                <button type="submit" id="verify-unsubscribe" class="btn btn-warning">
                                    <i class="fas fa-check me-2"></i>Confirm Unsubscription
                                </button>
                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary ms-2">
                                    <i class="fas fa-arrow-left me-2"></i>Cancel
                                </a>
                            </form>
                        <?php else: ?>
                            <h3 class="mb-4">Subscribe to GitHub Timeline Updates</h3>
                            <form method="POST" class="mb-4">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" id="email" required placeholder="Enter your email address">
                                </div>
                                <button type="submit" id="submit-email" class="btn btn-primary">
                                    <i class="fas fa-envelope me-2"></i>Send Verification Code
                                </button>
                            </form>
                        <?php endif; ?>

                        <hr class="my-4">

                        <h3 class="mb-4">Unsubscribe</h3>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="unsubscribe_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="unsubscribe_email" id="unsubscribe_email" required placeholder="Enter email to unsubscribe">
                            </div>
                            <button type="submit" id="submit-unsubscribe" class="btn btn-outline-warning">
                                <i class="fas fa-user-minus me-2"></i>Unsubscribe
                            </button>
                        </form>
                    </div>
                    <div class="card-footer">
                        <div class="alert alert-info mb-2">
                            <strong><i class="fas fa-code me-2"></i>Development Mode:</strong> 
                            Email verification codes are captured locally. 
                            <a href="email_viewer.php" class="alert-link">View captured emails and OTP codes here</a>.
                        </div>
                        <div class="text-center text-muted">
                            <small>
                                <i class="fas fa-clock me-1"></i>GitHub timeline updates are sent every 5 minutes to verified subscribers
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
