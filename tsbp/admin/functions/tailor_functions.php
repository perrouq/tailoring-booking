<?php
/**
 * Get statistics for a tailor
 * 
 * @param mysqli $conn Database connection
 * @param int $tailor_id Tailor ID
 * @return array Statistics including total assigned, in progress, completed orders and due today
 */
function getTailorStats($conn, $tailor_id) {
    $stats = array(
        'total_assigned' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'due_today' => 0
    );
    
    // Get total assigned orders
    $sql = "SELECT COUNT(*) as total FROM order_tailor_assignments 
            WHERE tailor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tailor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['total_assigned'] = $row['total'];
    }
    
    // Get in progress orders
    $sql = "SELECT COUNT(*) as count FROM order_tailor_assignments 
            WHERE tailor_id = ? AND status = 'in_progress'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tailor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['in_progress'] = $row['count'];
    }
    
    // Get completed orders
    $sql = "SELECT COUNT(*) as count FROM order_tailor_assignments 
            WHERE tailor_id = ? AND status = 'completed'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tailor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['completed'] = $row['count'];
    }
    
    // Get orders due today
    $today = date('Y-m-d');
    $sql = "SELECT COUNT(*) as count FROM order_tailor_assignments 
            WHERE tailor_id = ? AND due_date = ? AND status != 'completed' AND status != 'cancelled'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $tailor_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['due_today'] = $row['count'];
    }
    
    return $stats;
}

/**
 * Get tailor's assigned orders
 * 
 * @param mysqli $conn Database connection
 * @param int $tailor_id Tailor ID
 * @param int $limit Number of orders to return
 * @return array List of assigned orders with details
 */
function getTailorAssignedOrders($conn, $tailor_id, $limit = 5) {
    $orders = array();
    
    $sql = "SELECT ota.assignment_id, ota.order_id as id, ota.status, ota.due_date, 
            CONCAT(c.first_name, ' ', c.last_name) as customer_name,
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as product_count
            FROM order_tailor_assignments ota
            JOIN orders o ON ota.order_id = o.id
            JOIN customers c ON o.user_id = c.id
            WHERE ota.tailor_id = ?
            ORDER BY ota.assignment_date DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $tailor_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    return $orders;
}

/**
 * Get recent messages for a tailor
 * 
 * @param mysqli $conn Database connection
 * @param int $tailor_id Tailor ID
 * @param int $limit Number of messages to return
 * @return array List of recent messages
 */
function getTailorRecentMessages($conn, $tailor_id, $limit = 5) {
    $messages = array();
    
    $sql = "SELECT cm.message_id, cm.order_id, cm.sender_type, cm.message, 
            cm.read_status, cm.created_at
            FROM chat_messages cm
            JOIN order_tailor_assignments ota ON cm.order_id = ota.order_id
            WHERE ota.tailor_id = ? AND 
            (cm.sender_type != 'tailor' OR cm.sender_id != ?)
            ORDER BY cm.created_at DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $tailor_id, $tailor_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    return $messages;
}

/**
 * Get upcoming deadlines for a tailor
 * 
 * @param mysqli $conn Database connection
 * @param int $tailor_id Tailor ID
 * @param int $days Number of days to look ahead
 * @return array List of upcoming deadlines
 */
function getUpcomingDeadlines($conn, $tailor_id, $days = 7) {
    $deadlines = array();
    
    $todayDate = date('Y-m-d');
    $futureDate = date('Y-m-d', strtotime("+$days days"));
    
    $sql = "SELECT ota.assignment_id, ota.order_id, ota.due_date,
            CONCAT(c.first_name, ' ', c.last_name) as customer_name
            FROM order_tailor_assignments ota
            JOIN orders o ON ota.order_id = o.id
            JOIN customers c ON o.user_id = c.id
            WHERE ota.tailor_id = ? 
            AND ota.due_date BETWEEN ? AND ?
            AND ota.status != 'completed' AND ota.status != 'cancelled'
            ORDER BY ota.due_date ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $tailor_id, $todayDate, $futureDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $deadlines[] = $row;
    }
    
    return $deadlines;
}

/**
 * Helper function to get CSS class based on order status
 * 
 * @param string $status Order status
 * @return string CSS class name
 */
function getAssignmentStatusClass($status) {
    switch ($status) {
        case 'assigned':
            return 'warning';
        case 'in_progress':
            return 'primary';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Calculate time elapsed since a date
 * 
 * @param string $datetime Date/time in MySQL format
 * @return string Human-readable time elapsed
 */
function timeElapsed($datetime) {
    $now = new DateTime();
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
?>