<?php
// Start session and include database connection
session_start();
require_once 'config.php';
require_once 'unread_messages.php';

// Default response
$response = ['count' => 0];

// Check if user is logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user_id'])) {
    // Get unread message count
    $response['count'] = getUnreadMessageCount($pdo, $_SESSION['user_id']);
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;