<?php
session_start();
header('Content-Type: application/json');

// Get the JSON data from the request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validate the required fields
if (!isset($data['first_name']) || !isset($data['last_name']) || !isset($data['email']) || 
    !isset($data['phone']) || !isset($data['address']) || !isset($data['city']) || 
    !isset($data['state']) || !isset($data['tx_ref'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Store customer data in session
$_SESSION['customer_data'] = [
    'first_name' => htmlspecialchars($data['first_name']),
    'last_name' => htmlspecialchars($data['last_name']),
    'email' => htmlspecialchars($data['email']),
    'phone' => htmlspecialchars($data['phone']),
    'address' => htmlspecialchars($data['address']),
    'city' => htmlspecialchars($data['city']),
    'state' => htmlspecialchars($data['state']),
    'zip_code' => isset($data['zip_code']) ? htmlspecialchars($data['zip_code']) : '',
    'delivery_instructions' => isset($data['delivery_instructions']) ? htmlspecialchars($data['delivery_instructions']) : '',
    'tx_ref' => htmlspecialchars($data['tx_ref'])
];

// Return success
echo json_encode(['status' => 'success']);
?>
