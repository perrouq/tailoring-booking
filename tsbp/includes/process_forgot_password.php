<?php
session_start();
require_once '../includes/config.php';

// Check if form was submitted 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $last_name = htmlspecialchars(trim($_POST['last_name']), ENT_QUOTES, 'UTF-8');

    // Validate last name
    if (empty($last_name)) {
        $_SESSION['flash_message'] = "Please enter your last name.";
        $_SESSION['flash_type'] = "alert-danger";
        header("Location: ../forgot_password.php");
        exit();
    }
    
    // Check if last name exists in the database
    $stmt = $conn->prepare("SELECT id, email, phone FROM customers WHERE last_name = ?");
    $stmt->bind_param("s", $last_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // No matching last name found
        $_SESSION['flash_message'] = "We couldn't find an account with that last name.";
        $_SESSION['flash_type'] = "alert-danger";
        header("Location: ../forgot_password.php");
        exit();
    }
    
    // Found at least one matching account
    // If multiple accounts with same last name, just use the first one for simplicity
    // In a real system, you might want to ask for additional identifying information
    $user = $result->fetch_assoc();
    
    // Store the actual values in session for verification later
    $_SESSION['actual_email'] = $user['email'];
    $_SESSION['actual_phone'] = $user['phone'];
    
    // Create masked versions of email and phone
    $_SESSION['masked_email'] = maskEmail($user['email']);
    $_SESSION['masked_phone'] = maskPhone($user['phone']);
    $_SESSION['show_verification'] = true;
    
    // Redirect back to forgot password page to show the verification form
    header("Location: ../forgot_password.php");
    exit();
    
} else {
    // If accessed directly without form submission
    header("Location: ../forgot_password.php");
    exit();
}

// Function to mask email address
function maskEmail($email) {
    $parts = explode('@', $email);
    $name = $parts[0];
    $domain = $parts[1] ?? '';
    
    $masked_name = substr($name, 0, 2) . str_repeat('*', max(strlen($name) - 3, 1)) . substr($name, -1);
    return $masked_name . '@' . $domain;
}

// Function to mask phone number
function maskPhone($phone) {
    $length = strlen($phone);
    
    // Keep first 2 and last 2 digits visible
    return substr($phone, 0, 2) . str_repeat('*', $length - 4) . substr($phone, -2);
}
?>
