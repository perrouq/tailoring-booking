<?php
session_start();
require_once '../includes/db_connect.php';

// Check if form was submitted 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']), ENT_QUOTES, 'UTF-8');
    
    // Check if verification info is in session
    if (!isset($_SESSION['actual_email']) || !isset($_SESSION['actual_phone'])) {
        $_SESSION['flash_message'] = "Verification session expired. Please start over.";
        $_SESSION['flash_type'] = "alert-danger";
        header("Location: ../forgot_password.php");
        exit();
    }
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_message'] = "Please enter a valid email address.";
        $_SESSION['flash_type'] = "alert-danger";
        header("Location: ../forgot_password.php");
        exit();
    }
    
    // Validate phone
    if (empty($phone)) {
        $_SESSION['flash_message'] = "Please enter your phone number.";
        $_SESSION['flash_type'] = "alert-danger";
        header("Location: ../forgot_password.php");
        exit();
    }
    
    // Check if provided email and phone match the stored values
    if ($email === $_SESSION['actual_email'] && $phone === $_SESSION['actual_phone']) {
        // Identity verified! Generate a unique token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in the database
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)");
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();
        
        // Set session to indicate verification was successful
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_token'] = $token;
        
        // Clean up verification session variables
        unset($_SESSION['show_verification']);
        unset($_SESSION['masked_email']);
        unset($_SESSION['masked_phone']);
        unset($_SESSION['actual_email']);
        unset($_SESSION['actual_phone']);
        
        // Redirect to the reset password page
        header("Location: ../reset_password.php");
        exit();
    } else {
        // Email or phone don't match
        $_SESSION['flash_message'] = "The provided information doesn't match our records.";
        $_SESSION['flash_type'] = "alert-danger";
        header("Location: ../forgot_password.php");
        exit();
    }
    
} else {
    // If accessed directly without form submission
    header("Location: ../forgot_password.php");
    exit();
}
?>
