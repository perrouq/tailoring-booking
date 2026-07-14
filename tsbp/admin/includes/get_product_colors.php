<?php
// Admin helper file to fetch product colors via AJAX
session_start();
require_once('config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get product ID from request
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit();
}

// Fetch colors for the product
$query = "SELECT id, color_name, color_code, quantity 
          FROM product_colors 
          WHERE product_id = ? 
          ORDER BY color_name ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

$colors = [];
while ($row = $result->fetch_assoc()) {
    $colors[] = [
        'id' => $row['id'],
        'color_name' => $row['color_name'],
        'color_code' => $row['color_code'],
        'quantity' => $row['quantity']
    ];
}

$stmt->close();

echo json_encode([
    'success' => true,
    'colors' => $colors
]);
?>