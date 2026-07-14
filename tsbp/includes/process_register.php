<?php
session_start();
require_once '../includes/config.php';

// Check if form was submitted 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data and sanitize inputs
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate data
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if user already exists
    $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Email already registered. Please login or use a different email.";
    }
    
    // If there are errors, redirect back with error message
    if (!empty($errors)) {
        $_SESSION['flash_message'] = implode("<br>", $errors);
        $_SESSION['flash_type'] = "alert-danger";
        header("Location: ../login.php?register=1");
        exit();
    }
    
    // If no errors, proceed with registration
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, password, phone) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $phone);
    
    if ($stmt->execute()) {
        // Registration successful
        $_SESSION['flash_message'] = "Registration successful! You can now login.";
        $_SESSION['flash_type'] = "alert-success";
        header("Location: ../login.php");
        exit();
    } else {
        // Registration failed
        $_SESSION['flash_message'] = "Registration failed. Please try again later.";
        $_SESSION['flash_type'] = "alert-danger";
        header("Location: ../login.php?register=1");
        exit();
    }
    
    $stmt->close();
} else {
    // If accessed directly without form submission
    header("Location: ../login.php");
    exit();
}
?>