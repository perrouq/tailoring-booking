<?php
session_start();
require_once 'config.php';

// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: ../login.php');
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Get user data from form
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    
    // Update user profile information
    $stmt = $conn->prepare("UPDATE customers SET 
        first_name = ?, 
        last_name = ?, 
        phone = ?, 
        address = ?, 
        city = ?, 
        state = ?, 
        zip_code = ? 
        WHERE id = ?");
    
    $stmt->bind_param("sssssssi", 
        $first_name, 
        $last_name, 
        $phone, 
        $address, 
        $city, 
        $state, 
        $zip_code, 
        $user_id
    );
    
    $stmt->execute();
    
    // Update password if provided
    if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_new_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];
        
        // Verify that new passwords match
        if ($new_password !== $confirm_new_password) {
            $_SESSION['profile_error'] = "New passwords do not match";
            header('Location: ../profile.php');
            exit;
        }
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM customers WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!password_verify($current_password, $user['password'])) {
            $_SESSION['profile_error'] = "Current password is incorrect";
            header('Location: ../profile.php');
            exit;
        }
        
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        $stmt->execute();
    }
    
    $_SESSION['profile_updated'] = true;
    header('Location: ../profile.php');
    exit;
}
 
// If not a POST request, redirect to profile page
header('Location: ../profile.php');
exit;
?>