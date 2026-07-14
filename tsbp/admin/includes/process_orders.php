<?php
session_start();
require_once('config.php');
require_once('../functions/auth_functions.php');
require_once('../functions/admin_functions.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}


// Check if action is set
if (!isset($_GET['action']) && !isset($_POST['action'])) {
    $_SESSION['error'] = "No action specified";
    header("Location: ../orders.php");
    exit();
}

// Get the action from either GET or POST
$action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];

switch ($action) {
    case 'add':
        addOrder();
        break;
    case 'edit':
        editOrder();
        break;
    case 'delete':
        deleteOrder();
        break;
    case 'add_item':
        addOrderItem();
        break;
    case 'delete_item':
        deleteOrderItem();
        break;
    case 'assign_tailor':
        assignTailor();
        break;
    default:
        $_SESSION['error'] = "Invalid action";
        header("Location: ../orders.php");
        exit();
}

/**
 * Function to add a new order
 */
function addOrder() {
    global $conn;
    
    // Get form data
    $user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : NULL;
    $total_amount = $_POST['total_amount'];
    $payment_status = $_POST['payment_status'];
    $payment_reference = $_POST['payment_reference'];
    $delivery_address = $_POST['delivery_address'];
    $delivery_city = $_POST['delivery_city'];
    $delivery_state = $_POST['delivery_state'];
    $delivery_zip = $_POST['delivery_zip'];
    $delivery_instructions = $_POST['delivery_instructions'];
    
    // Insert new order
    $query = "INSERT INTO orders (user_id, total_amount, payment_status, payment_reference, 
              delivery_address, delivery_city, delivery_state, delivery_zip, delivery_instructions) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("idsssssss", $user_id, $total_amount, $payment_status, $payment_reference, 
                       $delivery_address, $delivery_city, $delivery_state, $delivery_zip, $delivery_instructions);
    
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;
        $_SESSION['success'] = "Order #$order_id created successfully!";
    } else {
        $_SESSION['error'] = "Error creating order: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: ../orders.php");
    exit();
}

/**
 * Function to edit an existing order
 */
function editOrder() {
    global $conn;
    
    // Get form data
    $id = $_POST['id'];
    $user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : NULL;
    $total_amount = $_POST['total_amount'];
    $payment_status = $_POST['payment_status'];
    $payment_reference = $_POST['payment_reference'];
    $delivery_address = $_POST['delivery_address'];
    $delivery_city = $_POST['delivery_city'];
    $delivery_state = $_POST['delivery_state'];
    $delivery_zip = $_POST['delivery_zip'];
    $delivery_instructions = $_POST['delivery_instructions'];
    
    // Update order
    $query = "UPDATE orders SET user_id = ?, total_amount = ?, payment_status = ?, payment_reference = ?, 
              delivery_address = ?, delivery_city = ?, delivery_state = ?, delivery_zip = ?, 
              delivery_instructions = ? WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("idsssssssi", $user_id, $total_amount, $payment_status, $payment_reference, 
                       $delivery_address, $delivery_city, $delivery_state, $delivery_zip, 
                       $delivery_instructions, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Order #$id updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating order: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: ../orders.php");
    exit();
}

/**
 * Function to assign a tailor to an order
 */
function assignTailor() {
    global $conn;
    
    // Get form data
    $order_id = $_POST['order_id'];
    $tailor_id = $_POST['tailor_id'];
    $status = $_POST['status'];
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : NULL;
    $notes = $_POST['notes'];
    $admin_id = $_SESSION['admin_id']; // Current logged in admin
    
    // First check if assignment already exists
    $checkQuery = "SELECT assignment_id FROM order_tailor_assignments WHERE order_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $order_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Assignment exists, update it
        $row = $checkResult->fetch_assoc();
        $assignment_id = $row['assignment_id'];
        
        $updateQuery = "UPDATE order_tailor_assignments 
                        SET tailor_id = ?, status = ?, due_date = ?, notes = ? 
                        WHERE assignment_id = ?";
        
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("isssi", $tailor_id, $status, $due_date, $notes, $assignment_id);
        
        if ($updateStmt->execute()) {
            $_SESSION['success'] = "Tailor assignment updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating tailor assignment: " . $updateStmt->error;
        }
        
        $updateStmt->close();
    } else {
        // Create new assignment
        $insertQuery = "INSERT INTO order_tailor_assignments 
                        (order_id, tailor_id, assigned_by, status, due_date, notes) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("iiisss", $order_id, $tailor_id, $admin_id, $status, $due_date, $notes);
        
        if ($insertStmt->execute()) {
            $_SESSION['success'] = "Tailor assigned to order successfully!";
            
            // Send notification to tailor if requested
            if (isset($_POST['notify_tailor']) && $_POST['notify_tailor'] == 1) {
                // Get tailor email
                $tailorQuery = "SELECT email FROM tailors WHERE tailor_id = ?";
                $tailorStmt = $conn->prepare($tailorQuery);
                $tailorStmt->bind_param("i", $tailor_id);
                $tailorStmt->execute();
                $tailorResult = $tailorStmt->get_result();
                
                if ($tailorResult->num_rows > 0) {
                    $tailorEmail = $tailorResult->fetch_assoc()['email'];
                    
                    // In a real system, you would send an email here
                    // For now, we'll just log that notification would be sent
                    $_SESSION['success'] .= " Notification would be sent to tailor.";
                }
                
                $tailorStmt->close();
            }
        } else {
            $_SESSION['error'] = "Error assigning tailor: " . $insertStmt->error;
        }
        
        $insertStmt->close();
    }
    
    $checkStmt->close();
    
    // Redirect back
    header("Location: ../orders.php");
    exit();
}

/**
 * Function to delete an order
 */
function deleteOrder() {
    global $conn;
    
    $id = $_GET['id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First delete related order items
        $query1 = "DELETE FROM order_items WHERE order_id = ?";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();
        
        // Delete any tailor assignments
        $query2 = "DELETE FROM order_tailor_assignments WHERE order_id = ?";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();
        
        // Then delete the order
        $query3 = "DELETE FROM orders WHERE id = ?";
        $stmt3 = $conn->prepare($query3);
        $stmt3->bind_param("i", $id);
        $stmt3->execute();
        $stmt3->close();
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Order #$id and all its data deleted successfully!";
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = "Error deleting order: " . $e->getMessage();
    }
    
    header("Location: ../orders.php");
    exit();
}

/**
 * Function to add an item to an order
 */
function addOrderItem() {
    global $conn;
    
    // Get form data
    $order_id = $_POST['order_id'];
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    
    // Insert new order item
    $query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
    
    if ($stmt->execute()) {
        // Update order total amount
        updateOrderTotal($order_id);
        $_SESSION['success'] = "Item added to order successfully!";
    } else {
        $_SESSION['error'] = "Error adding item to order: " . $stmt->error;
    }
    
    $stmt->close();
    
    // FIXED: Redirect back to the view_order.php with the correct id parameter
    $return = isset($_POST['return']) ? $_POST['return'] : '';
    if ($return === 'view_order') {
        header("Location: ../view_order.php?id=$order_id");
    } else {
        header("Location: ../orders.php");
    }
    exit();
}

/**
 * Function to delete an item from an order
 */
function deleteOrderItem() {
    global $conn;
    
    $item_id = $_GET['item_id'];
    $order_id = $_GET['order_id'];
    
    // Delete order item
    $query = "DELETE FROM order_items WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $item_id);
    
    if ($stmt->execute()) {
        // Update order total amount
        updateOrderTotal($order_id);
        $_SESSION['success'] = "Item removed from order successfully!";
    } else {
        $_SESSION['error'] = "Error removing item from order: " . $stmt->error;
    }
    
    $stmt->close();
    
    // FIXED: Redirect back to the view_order.php with the correct id parameter
    $return = isset($_GET['return']) ? $_GET['return'] : '';
    if ($return === 'view_order') {
        header("Location: ../view_order.php?id=$order_id");
    } else {
        header("Location: ../orders.php");
    }
    exit();
}

/**
 * Helper function to update order total amount
 */
function updateOrderTotal($order_id) {
    global $conn;
    
    // Calculate the sum of (quantity * price) for all items in this order
    $query = "SELECT SUM(quantity * price) as total FROM order_items WHERE order_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $new_total = $row['total'] ?? 0;
    $stmt->close();
    
    // Update the order's total amount
    $update_query = "UPDATE orders SET total_amount = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("di", $new_total, $order_id);
    $update_stmt->execute();
    $update_stmt->close();
}
?>