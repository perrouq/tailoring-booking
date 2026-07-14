<?php
session_start();
require_once 'config.php';

// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: ../login.php');
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Initialize redirect location - default to profile measurements tab
$redirect_to = 'profile.php?tab=measurements';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Override redirect location if specified in POST
    if (isset($_POST['redirect_to']) && !empty($_POST['redirect_to'])) {
        $redirect_to = $_POST['redirect_to'];
    }
    
    // Sanitize and validate measurements
    $neck = !empty($_POST['neck']) ? filter_var($_POST['neck'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $chest = !empty($_POST['chest']) ? filter_var($_POST['chest'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $shoulder = !empty($_POST['shoulder']) ? filter_var($_POST['shoulder'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $sleeve = !empty($_POST['sleeve']) ? filter_var($_POST['sleeve'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $bicep = !empty($_POST['bicep']) ? filter_var($_POST['bicep'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $wrist = !empty($_POST['wrist']) ? filter_var($_POST['wrist'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $waist = !empty($_POST['waist']) ? filter_var($_POST['waist'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $hip = !empty($_POST['hip']) ? filter_var($_POST['hip'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $inseam = !empty($_POST['inseam']) ? filter_var($_POST['inseam'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $thigh = !empty($_POST['thigh']) ? filter_var($_POST['thigh'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $knee = !empty($_POST['knee']) ? filter_var($_POST['knee'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $ankle = !empty($_POST['ankle']) ? filter_var($_POST['ankle'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
    $notes = !empty($_POST['notes']) ? htmlspecialchars($_POST['notes']) : null;

    // Check if user already has measurements
    $check_stmt = $conn->prepare("SELECT id FROM customer_measurements WHERE customer_id = ?");
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing measurements
        $update_stmt = $conn->prepare("
            UPDATE customer_measurements SET 
                neck = ?, chest = ?, shoulder = ?, sleeve = ?, bicep = ?, wrist = ?,
                waist = ?, hip = ?, inseam = ?, thigh = ?, knee = ?, ankle = ?, notes = ?,
                updated_at = NOW()
            WHERE customer_id = ?
        ");
        $update_stmt->bind_param(
            "ddddddddddddsi",
            $neck, $chest, $shoulder, $sleeve, $bicep, $wrist,
            $waist, $hip, $inseam, $thigh, $knee, $ankle, $notes,
            $user_id
        );
        
        if ($update_stmt->execute()) {
            $_SESSION['measurements_updated'] = true;
            $_SESSION['profile_updated'] = true;
            $_SESSION['update_message'] = "Your measurements have been updated successfully!";
        } else {
            $_SESSION['measurements_error'] = "Failed to update measurements: " . $conn->error;
        }
        
        $update_stmt->close();
    } else {
        // Insert new measurements
        $insert_stmt = $conn->prepare("
            INSERT INTO customer_measurements 
            (customer_id, neck, chest, shoulder, sleeve, bicep, wrist, waist, hip, inseam, thigh, knee, ankle, notes, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $insert_stmt->bind_param(
            "idddddddddddds",
            $user_id, $neck, $chest, $shoulder, $sleeve, $bicep, $wrist,
            $waist, $hip, $inseam, $thigh, $knee, $ankle, $notes
        );
        
        if ($insert_stmt->execute()) {
            $_SESSION['measurements_updated'] = true;
            $_SESSION['profile_updated'] = true;
            $_SESSION['update_message'] = "Your measurements have been saved successfully!";
        } else {
            $_SESSION['measurements_error'] = "Failed to save measurements: " . $conn->error;
        }
        
        $insert_stmt->close();
    }
    
    $check_stmt->close();
    
    // Sanitize the redirect URL to prevent malicious redirects
    $allowed_pages = ['profile.php', 'checkout.php'];
    $redirect_page = basename($redirect_to);
    
    // Check if the page is in our allowed list
    $is_allowed = false;
    foreach ($allowed_pages as $allowed) {
        if (strpos($redirect_page, $allowed) === 0) {
            $is_allowed = true;
            break;
        }
    }

    if ($is_allowed) {
        // Safe to redirect to the requested page
        header("Location: ../" . $redirect_to);
    } else {
        // Default to profile page if not in allowed list
        header("Location: ../profile.php?tab=measurements");
    }
    exit;
}

// If not POST request, redirect to profile
header("Location: ../profile.php?tab=measurements");
exit;
?>