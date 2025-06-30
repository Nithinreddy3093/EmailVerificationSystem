<?php

// Database connection function
function getDatabaseConnection() {
    $host = $_ENV['PGHOST'] ?? 'localhost';
    $port = $_ENV['PGPORT'] ?? '5432';
    $dbname = $_ENV['PGDATABASE'] ?? 'postgres';
    $user = $_ENV['PGUSER'] ?? 'postgres';
    $password = $_ENV['PGPASSWORD'] ?? '';
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    
    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

function generateVerificationCode() {
    // Generate and return a 6-digit numeric code
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function registerEmail($email) {
    // Save verified email to database
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        return false;
    }
    
    try {
        // Check if email is already registered
        $stmt = $pdo->prepare("SELECT id FROM registered_emails WHERE email = ? AND is_active = TRUE");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            return true; // Already registered
        }
        
        // Add email to database
        $stmt = $pdo->prepare("INSERT INTO registered_emails (email) VALUES (?) ON CONFLICT (email) DO UPDATE SET is_active = TRUE, registered_at = CURRENT_TIMESTAMP");
        $stmt->execute([$email]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Database error in registerEmail: " . $e->getMessage());
        return false;
    }
}

function unsubscribeEmail($email) {
    // Remove email from database
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        return false;
    }
    
    try {
        // Check if email exists and is active
        $stmt = $pdo->prepare("SELECT id FROM registered_emails WHERE email = ? AND is_active = TRUE");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() === 0) {
            return false; // Email not found or already inactive
        }
        
        // Mark email as inactive instead of deleting
        $stmt = $pdo->prepare("UPDATE registered_emails SET is_active = FALSE WHERE email = ?");
        $stmt->execute([$email]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Database error in unsubscribeEmail: " . $e->getMessage());
        return false;
    }
}

function sendVerificationEmail($email, $code) {
    // Include mail configuration
    require_once __DIR__ . '/mail_config.php';
    
    // Send an email containing the verification code
    $subject = "Your Verification Code";
    $message = "<html><body>";
    $message .= "<h2>Email Verification</h2>";
    $message .= "<p>Your verification code is: <strong style='font-size: 24px; color: #007bff;'>$code</strong></p>";
    $message .= "<p>This code will expire in 10 minutes.</p>";
    $message .= "<p>If you didn't request this code, please ignore this email.</p>";
    $message .= "</body></html>";
    
    // Improved headers for better email delivery
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: GitHub Timeline Notifier <no-reply@github-timeline.com>" . "\r\n";
    $headers .= "Reply-To: no-reply@github-timeline.com" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "X-Priority: 1" . "\r\n";
    
    // Store the verification code in session for debugging (only if not CLI)
    if (php_sapi_name() !== 'cli') {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['debug_last_verification_code'] = $code;
        $_SESSION['debug_last_email'] = $email;
    }
    
    // Use the enhanced email sending function
    $mailResult = sendEmailReliably($email, $subject, $message, $headers);
    
    return $mailResult;
}

function sendEmailViaSMTP($email, $subject, $message) {
    // Alternative SMTP sending method using PHP streams
    // This is a basic implementation - in production you'd use PHPMailer or similar
    
    // Check if we can use SMTP
    $smtpHost = getenv('SMTP_HOST') ?: 'localhost';
    $smtpPort = getenv('SMTP_PORT') ?: 587;
    
    // For development, we'll use a simple socket connection if available
    $socket = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
    if (!$socket) {
        return false;
    }
    
    // Simple SMTP conversation
    fclose($socket);
    
    // For now, return false to indicate SMTP not fully configured
    // In production, you would implement full SMTP protocol here
    return false;
}

function sendUnsubscribeConfirmationEmail($email, $code) {
    // Include mail configuration
    require_once __DIR__ . '/mail_config.php';
    
    // Send unsubscribe confirmation email
    $subject = "Confirm Unsubscription";
    $message = "<html><body>";
    $message .= "<h2>Unsubscribe Confirmation</h2>";
    $message .= "<p>To confirm unsubscription from GitHub Timeline updates, use this code:</p>";
    $message .= "<p><strong style='font-size: 24px; color: #dc3545;'>$code</strong></p>";
    $message .= "<p>This code will expire in 10 minutes.</p>";
    $message .= "<p>If you didn't request this unsubscription, please ignore this email.</p>";
    $message .= "</body></html>";
    
    // Improved headers for better email delivery
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: GitHub Timeline Notifier <no-reply@github-timeline.com>" . "\r\n";
    $headers .= "Reply-To: no-reply@github-timeline.com" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "X-Priority: 1" . "\r\n";
    
    // Store the verification code in session for debugging (only if not CLI)
    if (php_sapi_name() !== 'cli') {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['debug_last_unsubscribe_code'] = $code;
        $_SESSION['debug_last_unsubscribe_email'] = $email;
    }
    
    // Use the enhanced email sending function
    $mailResult = sendEmailReliably($email, $subject, $message, $headers);
    
    return $mailResult;
}

function fetchGitHubTimeline() {
    // Fetch latest data from https://www.github.com/timeline
    $url = "https://www.github.com/timeline";
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: PHP-GitHub-Timeline-Fetcher/1.0',
                'Accept: application/json'
            ],
            'timeout' => 30
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        // If GitHub timeline is not accessible, return mock data for demonstration
        return json_encode([
            [
                'type' => 'PushEvent',
                'actor' => ['login' => 'testuser'],
                'repo' => ['name' => 'testuser/example-repo'],
                'created_at' => date('Y-m-d\TH:i:s\Z')
            ],
            [
                'type' => 'CreateEvent',
                'actor' => ['login' => 'developer'],
                'repo' => ['name' => 'developer/new-project'],
                'created_at' => date('Y-m-d\TH:i:s\Z', strtotime('-1 hour'))
            ]
        ]);
    }
    
    return $response;
}

function formatGitHubData($data) {
    // Convert fetched data into formatted HTML
    $events = json_decode($data, true);
    
    if (!is_array($events) || empty($events)) {
        return "<p>No recent GitHub activity found.</p>";
    }
    
    $html = "<h2>GitHub Timeline Updates</h2>\n";
    $html .= "<table border=\"1\">\n";
    $html .= "  <tr><th>Event</th><th>User</th></tr>\n";
    
    // Limit to first 10 events
    $limitedEvents = array_slice($events, 0, 10);
    
    foreach ($limitedEvents as $event) {
        $eventType = isset($event['type']) ? $event['type'] : 'Unknown';
        $username = isset($event['actor']['login']) ? $event['actor']['login'] : 'Unknown';
        
        // Clean event type for display
        $displayEvent = str_replace('Event', '', $eventType);
        
        $html .= "  <tr><td>" . htmlspecialchars($displayEvent) . "</td><td>" . htmlspecialchars($username) . "</td></tr>\n";
    }
    
    $html .= "</table>\n";
    
    return $html;
}

function sendGitHubUpdatesToSubscribers() {
    // Send formatted GitHub timeline to registered users from database
    
    $pdo = getDatabaseConnection();
    if (!$pdo) {
        return false;
    }
    
    try {
        // Get all active subscribers from database
        $stmt = $pdo->prepare("SELECT email FROM registered_emails WHERE is_active = TRUE");
        $stmt->execute();
        $subscribers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($subscribers)) {
            return true; // No subscribers, but not an error
        }
        
        // Fetch and format GitHub data
        $rawData = fetchGitHubTimeline();
        $formattedData = formatGitHubData($rawData);
        
        $subject = "Latest GitHub Updates";
        $baseUrl = isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : 'http://localhost:5000';
        $unsubscribeUrl = $baseUrl . '/unsubscribe.php';
        
        $messageBody = $formattedData . "\n<p><a href=\"$unsubscribeUrl\" id=\"unsubscribe-button\">Unsubscribe</a></p>";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@example.com" . "\r\n";
        
        $successCount = 0;
        foreach ($subscribers as $email) {
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if (mail($email, $subject, $messageBody, $headers)) {
                    $successCount++;
                }
            }
        }
        
        return $successCount > 0;
    } catch (PDOException $e) {
        error_log("Database error in sendGitHubUpdatesToSubscribers: " . $e->getMessage());
        return false;
    }
}
?>
