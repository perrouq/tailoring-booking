<?php
session_start(); 
require_once 'includes/config.php';

// Set content type header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Invalid request'
];

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    $response = [
        'success' => false,
        'message' => 'User not logged in',
        'redirect' => true,
        'redirect_url' => 'login.php'
    ];
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'add':
            if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                $product_id = intval($_POST['product_id']);
                $quantity = intval($_POST['quantity']);
                $color_id = isset($_POST['color_id']) ? intval($_POST['color_id']) : null;
                $color_name = isset($_POST['color_name']) ? $_POST['color_name'] : null;
                
                // Validate quantity
                if ($quantity < 1) $quantity = 1;
                if ($quantity > 99) $quantity = 99;
                
                // Check if product exists
                $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    
                    // Check if color is required (product has colors)
                    $color_check = $conn->prepare("SELECT COUNT(*) as color_count FROM product_colors WHERE product_id = ?");
                    $color_check->bind_param("i", $product_id);
                    $color_check->execute();
                    $color_result = $color_check->get_result()->fetch_assoc();
                    $has_colors = $color_result['color_count'] > 0;
                    
                    // If product has colors, validate color selection and stock
                    if ($has_colors) {
                        if (!$color_id) {
                            $response = [
                                'success' => false,
                                'message' => 'Please select a color'
                            ];
                            break;
                        }
                        
                        // Check color stock
                        $color_stmt = $conn->prepare("SELECT * FROM product_colors WHERE id = ? AND product_id = ?");
                        $color_stmt->bind_param("ii", $color_id, $product_id);
                        $color_stmt->execute();
                        $color_result = $color_stmt->get_result();
                        
                        if ($color_result->num_rows === 0) {
                            $response = [
                                'success' => false,
                                'message' => 'Invalid color selection'
                            ];
                            break;
                        }
                        
                        $color_data = $color_result->fetch_assoc();
                        
                        // Check if color is out of stock
                        if ($color_data['quantity'] <= 0) {
                            $response = [
                                'success' => false,
                                'message' => 'This color is out of stock'
                            ];
                            break;
                        }
                        
                        // Calculate current cart quantity for this color
                        $cart_quantity = 0;
                        foreach ($_SESSION['cart'] as $item) {
                            if ($item['product_id'] === $product_id && 
                                isset($item['color_id']) && $item['color_id'] === $color_id) {
                                $cart_quantity = $item['quantity'];
                                break;
                            }
                        }
                        
                        // Check if requested quantity exceeds available stock
                        if (($cart_quantity + $quantity) > $color_data['quantity']) {
                            $available = $color_data['quantity'] - $cart_quantity;
                            $response = [
                                'success' => false,
                                'message' => "Only {$available} items available in {$color_data['color_name']}"
                            ];
                            break;
                        }
                    }
                    
                    // Check if product with same color is already in cart
                    $product_exists = false;
                    foreach ($_SESSION['cart'] as &$item) {
                        // Match by product_id and color_id (or both null for products without colors)
                        $color_match = ($has_colors) ? 
                            (isset($item['color_id']) && $item['color_id'] === $color_id) : 
                            (!isset($item['color_id']) || $item['color_id'] === null);
                        
                        if ($item['product_id'] === $product_id && $color_match) {
                            $item['quantity'] += $quantity;
                            if ($item['quantity'] > 99) $item['quantity'] = 99;
                            $product_exists = true;
                            break;
                        }
                    }
                    
                    // If product with this color is not in cart, add it
                    if (!$product_exists) {
                        $cart_item = [
                            'product_id' => $product_id,
                            'quantity' => $quantity
                        ];
                        
                        if ($has_colors && $color_id) {
                            $cart_item['color_id'] = $color_id;
                            $cart_item['color_name'] = $color_name;
                        }
                        
                        $_SESSION['cart'][] = $cart_item;
                    }
                    
                    $response = [
                        'success' => true,
                        'message' => 'Product added to cart',
                        'cart_count' => count($_SESSION['cart'])
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Product not found'
                    ];
                }
            }
            break;
            
        case 'update':
            if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                $product_id = intval($_POST['product_id']);
                $quantity = intval($_POST['quantity']);
                $color_id = isset($_POST['color_id']) ? intval($_POST['color_id']) : null;
                
                // Validate quantity
                if ($quantity < 1) $quantity = 1;
                if ($quantity > 99) $quantity = 99;
                
                // Check color stock if color is specified
                if ($color_id) {
                    $color_stmt = $conn->prepare("SELECT quantity FROM product_colors WHERE id = ?");
                    $color_stmt->bind_param("i", $color_id);
                    $color_stmt->execute();
                    $color_result = $color_stmt->get_result();
                    
                    if ($color_result->num_rows > 0) {
                        $color_data = $color_result->fetch_assoc();
                        if ($quantity > $color_data['quantity']) {
                            $response = [
                                'success' => false,
                                'message' => "Only {$color_data['quantity']} items available"
                            ];
                            break;
                        }
                    }
                }
                
                // Update product quantity in cart
                $found = false;
                foreach ($_SESSION['cart'] as &$item) {
                    $color_match = ($color_id) ? 
                        (isset($item['color_id']) && $item['color_id'] === $color_id) : 
                        (!isset($item['color_id']) || $item['color_id'] === null);
                    
                    if ($item['product_id'] === $product_id && $color_match) {
                        $item['quantity'] = $quantity;
                        $found = true;
                        break;
                    }
                }
                
                if ($found) {
                    $response = [
                        'success' => true,
                        'message' => 'Cart updated',
                        'cart_count' => count($_SESSION['cart'])
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Product not found in cart'
                    ];
                }
            }
            break;
            
        case 'remove':
            if (isset($_POST['product_id'])) {
                $product_id = intval($_POST['product_id']);
                $color_id = isset($_POST['color_id']) ? intval($_POST['color_id']) : null;
                
                // Remove product from cart
                $found = false;
                foreach ($_SESSION['cart'] as $key => $item) {
                    $color_match = ($color_id) ? 
                        (isset($item['color_id']) && $item['color_id'] === $color_id) : 
                        (!isset($item['color_id']) || $item['color_id'] === null);
                    
                    if ($item['product_id'] === $product_id && $color_match) {
                        unset($_SESSION['cart'][$key]);
                        // Reindex the array
                        $_SESSION['cart'] = array_values($_SESSION['cart']);
                        $found = true;
                        break;
                    }
                }
                
                if ($found) {
                    $response = [
                        'success' => true,
                        'message' => 'Product removed from cart',
                        'cart_count' => count($_SESSION['cart'])
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Product not found in cart'
                    ];
                }
            }
            break;
            
        case 'clear':
            // Clear cart
            $_SESSION['cart'] = [];
            $response = [
                'success' => true,
                'message' => 'Cart cleared',
                'cart_count' => 0
            ];
            break;
            
        default:
            $response = [
                'success' => false,
                'message' => 'Unknown action'
            ];
            break;
    }
}

// Ensure all numeric values are actually integers to avoid JSON issues
if (isset($response['cart_count'])) {
    $response['cart_count'] = intval($response['cart_count']);
}

// Send the response
echo json_encode($response);
exit;
?>