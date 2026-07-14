<?php
/**
 * Authentication functions for the Indigene Certificate Application System
 */

/**
 * Log an activity in the system
 * 
 * @param int|null $userId The user ID (if applicable)
 * @param int|null $adminId The admin ID (if applicable)
 * @param string $action The action performed
 * @param string $description Description of the activity
 * @return bool True if logged successfully, false otherwise
 *
function logActivity($userId, $adminId, $action, $description) {
    global $conn;
    
    // Get IP address and user agent
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, admin_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $userId, $adminId, $action, $description, $ip, $userAgent);
    
    return $stmt->execute();
}

/**
 * Check if admin is logged in and redirect if not
 * 
 * @param string $redirectTo The page to redirect to if not logged in
 * @return void
 */
function checkAdminLogin($redirectTo = 'index.php') {
    if(!isset($_SESSION['admin_id'])) {
        $_SESSION['error'] = "Please log in to continue";
        header("Location: $redirectTo");
        exit();
    }
}

/**
 * Check if admin has super_admin role
 * 
 * @return bool True if super_admin, false otherwise
 */
function isSuperAdmin() {
    return (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'super_admin');
}

/**
 * Display session messages (success, error, warning, info)
 * 
 * @return void
 */
function displayMessages() {
    $messageTypes = ['success', 'error', 'warning', 'info'];
    
    foreach($messageTypes as $type) {
        if(isset($_SESSION[$type])) {
            $alertClass = $type;
            if($type === 'error') {
                $alertClass = 'danger';
            }
            
            echo '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show" role="alert">';
            echo $_SESSION[$type];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
            
            // Clear the message
            unset($_SESSION[$type]);
        }
    }
}

/**
 * Get time elapsed string (e.g., "2 days ago")
 * 
 * @param string $datetime Date/time string
 * @return string Formatted time elapsed string
 */
/**
 * Get time elapsed string (e.g., "2 days ago")
 * 
 * @param string $datetime Date/time string
 * @return string Formatted time elapsed string
 */
function timeElapsed($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Instead of creating a dynamic property, calculate weeks properly
    $weeks = floor($diff->d / 7);
    $days = $diff->d % 7;

    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];
    
    // Use our calculated weeks value instead of a dynamic property
    foreach ($string as $k => &$v) {
        if ($k === 'w') {
            if ($weeks) {
                $v = $weeks . ' ' . $v . ($weeks > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        } else if ($k === 'd') {
            // Use our recalculated days value
            if ($days) {
                $v = $days . ' ' . $v . ($days > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        } else if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$string) {
        return 'just now';
    }
    
    $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
/**
 * Generate a random token
 * 
 * @param int $length The length of the token
 * @return string The generated token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Sanitize and validate input data
 * 
 * @param string $data The input data to sanitize
 * @return string The sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 * 
 * @param string $email The email to validate
 * @return bool True if valid, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if password meets requirements
 * 
 * @param string $password The password to check
 * @return bool True if valid, false otherwise
 */
function isValidPassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

/**
 * Get status badge CSS class
 * 
 * @param string $status The status
 * @return string CSS class for badge
 */
function getStatusBadgeClass($status) {
    switch($status) {
        case 'pending':
            return 'warning';
        case 'under_review':
            return 'info';
        case 'approved':
            return 'success';
        case 'rejected':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Get user status badge CSS class
 * 
 * @param string $status The user status
 * @return string CSS class for badge
 */
function getUserStatusBadgeClass($status) {
    switch($status) {
        case 'active':
            return 'success';
        case 'inactive':
            return 'warning';
        case 'suspended':
            return 'danger';
        default:
            return 'secondary';
    }
}

/**
 * Get activity icon class
 * 
 * @param string $action The action
 * @return string CSS class for icon
 *
function getActivityIconClass($action) {
    switch($action) {
        case 'login':
        case 'register':
            return 'primary';
        case 'approve':
            return 'success';
        case 'reject':
            return 'danger';
        case 'update':
            return 'info';
        case 'upload':
            return 'warning';
        default:
            return 'secondary';
    }
}

/**
 * Get activity icon
 * 
 * @param string $action The action
 * @return string Font Awesome icon name
 */
function getActivityIcon($action) {
    switch($action) {
        case 'login':
            return 'sign-in-alt';
        case 'logout':
            return 'sign-out-alt';
        case 'register':
            return 'user-plus';
        case 'approve':
            return 'check-circle';
        case 'reject':
            return 'times-circle';
        case 'update':
            return 'edit';
        case 'upload':
            return 'file-upload';
        case 'delete':
            return 'trash-alt';
        case 'view':
            return 'eye';
        default:
            return 'circle';
    }
}