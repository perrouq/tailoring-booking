<?php
// Check if tailor is logged in
if(!isset($_SESSION['tailor_id']) && basename($_SERVER['PHP_SELF']) != 'index.php') {
    header("Location: ../index.php");
    exit();
}

// Initialize unread message count function if not defined yet
if (!function_exists('getUnreadMessageCount')) {
    function getUnreadMessageCount($conn, $tailor_id) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count FROM chat_messages cm
            JOIN order_tailor_assignments ota ON cm.order_id = ota.order_id
            WHERE ota.tailor_id = ? AND cm.sender_type = 'customer' AND cm.read_status = 0
        ");
        $stmt->bind_param("i", $tailor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailor Panel | Tailoring Services Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/tailor.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="logo">
                <h1>Tailor Portal</h1>
            </div>
        </div>
        <div class="header-right">
            <?php if(isset($_SESSION['tailor_id'])): ?>
                <div class="dropdown">
                    <button class="dropdown-toggle user-menu" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['tailor_name'], 0, 1)); ?>
                        </span>
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['tailor_name']); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- Notification area for messages -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show notification" role="alert">
            <?php echo $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show notification" role="alert">
            <?php echo $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>