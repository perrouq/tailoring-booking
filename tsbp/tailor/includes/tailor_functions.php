<?php
/**
 * Get activity logs for a specific tailor
 * 
 * @param mysqli $conn Database connection
 * @param int $tailor_id The ID of the tailor
 * @param int $limit Optional limit for the number of logs to return (default: 10)
 * @return array Array of activity logs
 */
function getTailorActivityLogs($conn, $tailor_id, $limit = 10) {
    // Validate input
    $tailor_id = intval($tailor_id);
    $limit = intval($limit);
    
    if ($tailor_id <= 0 || $limit <= 0) {
        return [];
    }
    
    // Since the existing activity_logs table doesn't have a tailor_id column,
    // we'll use the user_id column to store tailor activities with a special action prefix
    // We can filter tailor-specific activities by using a naming convention in the action field
    $stmt = $conn->prepare("SELECT 
                             log_id, 
                             user_id, 
                             action, 
                             description, 
                             ip_address, 
                             timestamp
                         FROM 
                             activity_logs 
                         WHERE 
                             user_id = ? AND 
                             action LIKE 'tailor_%' 
                         ORDER BY 
                             timestamp DESC
                         LIMIT ?");
    
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param("ii", $tailor_id, $limit);
    
    if (!$stmt->execute()) {
        error_log("Error executing statement: " . $stmt->error);
        $stmt->close();
        return [];
    }
    
    $result = $stmt->get_result();
    $logs = [];
    
    while ($row = $result->fetch_assoc()) {
        // Remove the 'tailor_' prefix from the action for display
        $row['action'] = str_replace('tailor_', '', $row['action']);
        $logs[] = $row;
    }
    
    $stmt->close();
    
    return $logs;
}

/**
 * Log an activity for a tailor
 * 
 * @param mysqli $conn Database connection
 * @param int $tailor_id The ID of the tailor
 * @param string $action The action performed (without 'tailor_' prefix)
 * @param string $description Description of the activity
 * @return bool True on success, false on failure
 */
function logTailorActivity($conn, $tailor_id, $action, $description) {
    // Validate input
    $tailor_id = intval($tailor_id);
    
    if ($tailor_id <= 0 || empty($action) || empty($description)) {
        return false;
    }
    
    // Add 'tailor_' prefix to action to distinguish tailor activities
    $action = 'tailor_' . $action;
    
    // Get IP address and user agent
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $conn->prepare("INSERT INTO activity_logs 
                            (user_id, action, description, ip_address, user_agent) 
                            VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("issss", $tailor_id, $action, $description, $ip_address, $user_agent);
    
    if (!$stmt->execute()) {
        error_log("Error executing statement: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $stmt->close();
    
    return true;
}

/**
 * Get the appropriate icon class for a tailor activity type
 * 
 * @param string $action The action type
 * @return string CSS class for the icon background
 */
function getTailorActivityIconClass($action) {
    switch ($action) {
        case 'login':
            return 'primary';
        case 'logout':
            return 'secondary';
        case 'profile_update':
            return 'info';
        case 'password_change':
            return 'warning';
        case 'order_update':
            return 'success';
        case 'message_sent':
            return 'info';
        default:
            return 'secondary';
    }
}

/**
 * Get the appropriate FontAwesome icon for a tailor activity type
 * 
 * @param string $action The action type
 * @return string FontAwesome icon name
 */
function getTailorActivityIcon($action) {
    switch ($action) {
        case 'login':
            return 'sign-in-alt';
        case 'logout':
            return 'sign-out-alt';
        case 'profile_update':
            return 'user-edit';
        case 'password_change':
            return 'key';
        case 'order_update':
            return 'tasks';
        case 'measurement_update':
            return 'ruler';
        case 'order_complete':
            return 'check-circle';
        case 'message_sent':
            return 'comment';
        default:
            return 'history';
    }
}
/**
 * Get detailed information for a specific tailor
 * 
 * @param mysqli $conn Database connection
 * @param int $tailor_id The ID of the tailor
 * @return array|false The tailor details as an associative array or false if not found
 */
function getTailorDetails($conn, $tailor_id) {
    // Validate input
    $tailor_id = intval($tailor_id);
    
    if ($tailor_id <= 0) {
        return false;
    }
    
    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT 
                                tailor_id, 
                                fullname, 
                                email, 
                                phone, 
                                password, 
                                address, 
                                specialty, 
                                status, 
                                last_login, 
                                created_at 
                            FROM tailors 
                            WHERE tailor_id = ?");
                            
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("i", $tailor_id);
    
    if (!$stmt->execute()) {
        error_log("Error executing statement: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return false;
    }
    
    $tailor = $result->fetch_assoc();
    $stmt->close();
    
    return $tailor;
}

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