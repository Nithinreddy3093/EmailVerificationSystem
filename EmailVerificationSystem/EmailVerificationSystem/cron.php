<?php
// cron.php - Must handle sending GitHub updates via email
require_once __DIR__ . '/functions.php';

// Log the cron execution
$logMessage = "[" . date('Y-m-d H:i:s') . "] CRON job executed - ";

try {
    $result = sendGitHubUpdatesToSubscribers();
    
    if ($result) {
        $logMessage .= "GitHub updates sent successfully";
    } else {
        $logMessage .= "No subscribers or failed to send updates";
    }
    
    // Log to file
    file_put_contents(__DIR__ . '/cron.log', $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
    
    echo $logMessage . "\n";
    
} catch (Exception $e) {
    $errorMessage = $logMessage . "Error: " . $e->getMessage();
    file_put_contents(__DIR__ . '/cron.log', $errorMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
    echo $errorMessage . "\n";
}
?>
