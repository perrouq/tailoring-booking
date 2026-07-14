<?php
// Initialize the session
session_start();

// Set header to indicate this is a JSON response
header('Content-Type: application/json');

// Include database connection
require_once 'includes/config.php';

// Check if this is an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    echo json_encode(['success' => false, 'message' => 'Direct access not allowed']);
    exit;
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if product_id and quantity are set
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate product_id and quantity
$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
    exit;
}

// Verify that the product exists in the database
$stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    $stmt->close();
    $conn->close();
    exit;
}

// Fetch the product details
$product = $result->fetch_assoc();
$stmt->close();

// Add or update product in cart
$product_exists = false;
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['id'] == $product_id) {
        $_SESSION['cart'][$key]['quantity'] += $quantity;
        $product_exists = true;
        break;
    }
}

if (!$product_exists) {
    $_SESSION['cart'][] = [
        'id' => $product_id,
        'name' => $product['name'],
        'price' => $product['price'],
        'image' => $product['image'],
        'quantity' => $quantity
    ];
}

// Calculate total items in cart
$cartCount = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartCount += $item['quantity'];
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Product added to cart successfully',
    'cartCount' => $cartCount,
    'product' => [
        'id' => $product_id,
        'name' => $product['name'],
        'price' => $product['price'],
        'image' => $product['image']
    ]
]);

// Close database connection
$conn->close();
?>