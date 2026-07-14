<?php
session_start();
require_once 'includes/config.php';

// Redirect to process_order.php with the payment details
if (isset($_GET['status']) && isset($_GET['tx_ref']) && isset($_GET['transaction_id'])) {
    $redirect_url = "process_order.php?status=" . urlencode($_GET['status']) . 
                    "&tx_ref=" . urlencode($_GET['tx_ref']) . 
                    "&transaction_id=" . urlencode($_GET['transaction_id']);
    
    header("Location: " . $redirect_url);
    exit();
} else {
    // Missing required parameters
    $_SESSION['payment_error'] = "Missing payment verification parameters";
    header("Location: payment_error.php");
    exit();
}
?>
