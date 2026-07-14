<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $specialty = $_POST['specialty'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $status = $_POST['status'];
    
    // Check if email already exists
    $check_email = "SELECT * FROM tailors WHERE email = '$email'";
    $result = $conn->query($check_email);
    
    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists. Please use a different email.'); window.location.href='../tailors.php';</script>";
        exit;
    }
    
    // SQL query to insert tailor
    $query = "INSERT INTO tailors (fullname, email, phone, address, specialty, password, status) 
              VALUES ('$fullname', '$email', '$phone', '$address', '$specialty', '$password', '$status')";
    
    if ($conn->query($query) === TRUE) {
        echo "<script>alert('Tailor added successfully!'); window.location.href='../tailors.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); window.location.href='../tailors.php';</script>";
    }
} else {
    // If not a POST request, redirect to tailors page
    header("Location: ../tailors.php");
    exit;
}
?>