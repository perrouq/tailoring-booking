<?php



/**
 * Log an activity in the system
 * 
 * @param int|null $userId The user ID (if applicable)
 * @param int|null $adminId The admin ID (if applicable)
 * @param string $action The action performed
 * @param string $description Description of the activity
 * @return bool True if logged successfully, false otherwise
 */
function logActivity($userId, $adminId, $action, $description) {
    global $conn;
    
    // Get IP address and user agent
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, admin_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $userId, $adminId, $action, $description, $ip, $userAgent);
    
    return $stmt->execute();
}

// Get statistics for tailoring platform
function getTailoringStats($conn) {
    $stats = array();
    
    // Total customers
    $query = "SELECT COUNT(*) as total FROM customers";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['total_customers'] = $row['total'];
    
    // Total orders
    $query = "SELECT COUNT(*) as total FROM orders";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['total_orders'] = $row['total'];
    
    // Total products
    $query = "SELECT COUNT(*) as total FROM products";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['total_products'] = $row['total'];
    
    // Total income
    $query = "SELECT SUM(total_amount) as total_income FROM orders WHERE payment_status = 'completed'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['total_income'] = $row['total_income'] ? $row['total_income'] : 0;
    
    // Pending orders
    $query = "SELECT COUNT(*) as total FROM orders WHERE payment_status = 'pending'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['pending_orders'] = $row['total'];
    
    return $stats;
}

// Get recent orders
function getRecentOrders($conn, $limit = 5) {
    $query = "SELECT o.id, o.total_amount, o.payment_status, o.created_at, 
              c.first_name, c.last_name
              FROM orders o
              LEFT JOIN customers c ON o.user_id = c.id
              ORDER BY o.created_at DESC
              LIMIT ?";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $orders = array();
    while($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    return $orders;
}

// Get recent activity logs (placeholder - you may need to create this table)
function getRecentActivityLogs($conn, $limit = 5) {
    // This is a placeholder. You would need to adapt this to your actual logs table
    $logs = array();
    
    // Example log data
    $logs[] = array(
        'action' => 'new_order',
        'description' => 'New order #6 was placed',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour'))
    );
    
    $logs[] = array(
        'action' => 'payment',
        'description' => 'Payment received for order #6',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
    );
    
    return $logs;
}

// Helper function for activity log icons
function getActivityIcon($action) {
    $icons = array(
        'new_order' => 'shopping-cart',
        'payment' => 'money-bill',
        'delivery' => 'truck',
        'customer' => 'user',
        'product' => 'tshirt'
    );
    
    return isset($icons[$action]) ? $icons[$action] : 'info-circle';
}

// Helper function for activity icon classes
function getActivityIconClass($action) {
    $classes = array(
        'new_order' => 'primary',
        'payment' => 'success',
        'delivery' => 'info',
        'customer' => 'warning',
        'product' => 'secondary'
    );
    
    return isset($classes[$action]) ? $classes[$action] : 'primary';
}

// Helper function for payment status badge class
function getPaymentStatusClass($status) {
    $classes = array(
        'completed' => 'success',
        'pending' => 'warning',
        'failed' => 'danger'
    );
    
    return isset($classes[$status]) ? $classes[$status] : 'secondary';
}

// Format time elapsed
function timeElapsed($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    } elseif ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    } elseif ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'Just now';
    }
}
