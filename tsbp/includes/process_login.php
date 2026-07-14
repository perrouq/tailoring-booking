<?php
session_start();
require_once '../includes/config.php';

// Check if form was submitted 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Validate data
    $errors = [];
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If there are errors, redirect back with error message
    if (!empty($errors)) {
        $_SESSION['flash_message'] = implode("<br>", $errors);
        $_SESSION['flash_type'] = "error";
        header("Location: ../login.php");
        exit();
    }
    
    // Check if user exists and verify password
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Login successful - create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            
            // Redirect to homepage or dashboard
            header("Location: ../index.php");
            exit();
        } else {
            // Invalid password
            $_SESSION['flash_message'] = "Invalid email or password.";
            $_SESSION['flash_type'] = "error";
            header("Location: ../login.php");
            exit();
        }
    } else {
        // User not found
        $_SESSION['flash_message'] = "Invalid email or password.";
        $_SESSION['flash_type'] = "error";
        header("Location: ../login.php");
        exit();
    }
    
    $stmt->close();
} else {
    // If accessed directly without form submission
    header("Location: ../login.php");
    exit();
}
?>