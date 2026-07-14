<?php
session_start();
require_once 'includes/config.php';

// Check if we have a valid transaction and customer data
if (!isset($_GET['status']) || !isset($_GET['tx_ref']) || !isset($_GET['transaction_id']) || !isset($_SESSION['customer_data'])) {
    header('Location: checkout.php');
    exit;
}

// Get payment details
$status = $_GET['status'];
$tx_ref = $_GET['tx_ref'];
$transaction_id = $_GET['transaction_id'];
$payment_status = ($status === 'successful') ? 'completed' : 'pending';

// Get customer data from session
$customer_data = $_SESSION['customer_data'];

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    $_SESSION['payment_error'] = 'Your cart is empty';
    header('Location: payment_error.php');
    exit;
}

// Verify the payment with Flutterwave
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.flutterwave.com/v3/transactions/{$transaction_id}/verify",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "Authorization: " . getenv('FLW_SECRET_KEY'), // secret key, set in Render env vars
        "Content-Type: application/json",
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    $_SESSION['payment_error'] = "cURL Error: " . $err;
    header("Location: payment_error.php");
    exit();
}

$transaction = json_decode($response, true);

// Check if verification was successful
if (isset($transaction['status']) && $transaction['status'] === "success" && isset($transaction['data']['status'])) {
    $verified_status = $transaction['data']['status'] === "successful" ? "completed" : "failed";
    
    // Get user ID if logged in
    $user_id = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] ? $_SESSION['user_id'] : null;
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Calculate total amount for security and validate stock
        $total_amount = 0;
        $cart_items_validated = [];
        
        foreach ($_SESSION['cart'] as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $color_id = isset($item['color_id']) ? $item['color_id'] : null;
            
            // Get product price
            $query = $conn->prepare("SELECT price FROM products WHERE id = ?");
            $query->bind_param("i", $product_id);
            $query->execute();
            $result = $query->get_result();
            $product = $result->fetch_assoc();
            
            if (!$product) {
                throw new Exception("Product not found: ID {$product_id}");
            }
            
            $price = $product['price'];
            
            // If color is specified, validate color stock
            if ($color_id) {
                $color_query = $conn->prepare("SELECT color_name, quantity FROM product_colors WHERE id = ? AND product_id = ?");
                $color_query->bind_param("ii", $color_id, $product_id);
                $color_query->execute();
                $color_result = $color_query->get_result();
                $color_data = $color_result->fetch_assoc();
                
                if (!$color_data) {
                    throw new Exception("Invalid color selection for product ID {$product_id}");
                }
                
                // Check if enough stock is available
                if ($color_data['quantity'] < $quantity) {
                    throw new Exception("Insufficient stock for color {$color_data['color_name']}. Only {$color_data['quantity']} available.");
                }
                
                $cart_items_validated[] = [
                    'product_id' => $product_id,
                    'color_id' => $color_id,
                    'color_name' => $color_data['color_name'],
                    'quantity' => $quantity,
                    'price' => $price
                ];
            } else {
                $cart_items_validated[] = [
                    'product_id' => $product_id,
                    'color_id' => null,
                    'color_name' => null,
                    'quantity' => $quantity,
                    'price' => $price
                ];
            }
            
            $total_amount += $price * $quantity;
        }
        
        // Insert order
        $delivery_instructions = isset($_POST['delivery_instructions']) ? $_POST['delivery_instructions'] : '';
        
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_status, payment_reference, delivery_address, delivery_city, delivery_state, delivery_zip, delivery_instructions, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->bind_param("idsssssss", 
            $user_id, 
            $total_amount, 
            $verified_status, 
            $transaction_id, 
            $customer_data['address'], 
            $customer_data['city'], 
            $customer_data['state'], 
            $customer_data['zip_code'], 
            $delivery_instructions
        );
        
        $stmt->execute();
        $order_id = $conn->insert_id;
        
        // Insert order items and update color stock
        foreach ($cart_items_validated as $item) {
            $product_id = $item['product_id'];
            $color_id = $item['color_id'];
            $color_name = $item['color_name'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            
            // Insert order item
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, color_id, color_name, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
            $item_stmt->bind_param("iiisid", $order_id, $product_id, $color_id, $color_name, $quantity, $price);
            $item_stmt->execute();
            
            // Update color stock if color was specified
            if ($color_id) {
                $update_stock = $conn->prepare("UPDATE product_colors SET quantity = quantity - ? WHERE id = ? AND product_id = ?");
                $update_stock->bind_param("iii", $quantity, $color_id, $product_id);
                $update_stock->execute();
                
                // Verify the update was successful
                if ($update_stock->affected_rows === 0) {
                    throw new Exception("Failed to update stock for color ID {$color_id}");
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Clear cart and customer data
        $_SESSION['cart'] = [];
        unset($_SESSION['customer_data']);
        
        // Set success message
        $_SESSION['order_success'] = "Order placed successfully! Your order ID is #{$order_id}";
        
        // Redirect to order confirmation
        header("Location: order_confirmation.php?order_id=" . $order_id);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $_SESSION['payment_error'] = "Order processing error: " . $e->getMessage();
        header("Location: payment_error.php");
        exit();
    }
} else {
    // Payment verification failed
    $_SESSION['payment_error'] = "Payment verification failed: " . ($transaction['message'] ?? 'Unknown error');
    header("Location: payment_error.php");
    exit();
}
?>