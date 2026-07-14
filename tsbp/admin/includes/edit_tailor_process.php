<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $tailor_id = $_POST['tailor_id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $specialty = $_POST['specialty'];
    $status = $_POST['status'];
    
    // Check if email already exists for another tailor
    $check_email = "SELECT * FROM tailors WHERE email = '$email' AND tailor_id != $tailor_id";
    $result = $conn->query($check_email);
    
    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists for another tailor. Please use a different email.'); window.location.href='../tailors.php';</script>";
        exit;
    }
    
    // Handle password (only update if provided)
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_query = ", password = '$password'";
    } else {
        $password_query = "";
    }
    
    // SQL query to update tailor
    $query = "UPDATE tailors SET 
              fullname = '$fullname',
              email = '$email',
              phone = '$phone',
              address = '$address',
              specialty = '$specialty',
              status = '$status'
              $password_query
              WHERE tailor_id = $tailor_id";
    
    if ($conn->query($query) === TRUE) {
        echo "<script>alert('Tailor updated successfully!'); window.location.href='../tailors.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "'); window.location.href='../tailors.php';</script>";
    }
} else {
    // If not a POST request, redirect to tailors page
    header("Location: ../tailors.php");
    exit;
}
?>