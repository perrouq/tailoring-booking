<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>Tailor Panel</h2>
    </div>
    <div class="sidebar-theme-switch">
		<span class="theme-icon"><i class="fa-solid fa-sun"></i></span>
		<label class="switch">
			<input type="checkbox" id="theme-toggle">
			<span class="slider"></span>
		</label>
		<span class="theme-icon"><i class="fa-solid fa-moon"></i></span>
	</div>
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="tailor_orders.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php' || basename($_SERVER['PHP_SELF']) == 'view_order.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i> My Orders
                </a>
            </li>
            <li>
                <a href="messages.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'messages.php' || basename($_SERVER['PHP_SELF']) == 'chat.php') ? 'active' : ''; ?>">
                    <i class="fas fa-comments"></i> Messages
                    <?php 
                    // Count unread messages
                    $unreadCount = getUnreadMessageCount($conn, $_SESSION['tailor_id']);
                    if ($unreadCount > 0): 
                    ?>
                    <span class="badge bg-danger"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
          <!--  <li>
                <a href="measurements.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'measurements.php') ? 'active' : ''; ?>">
                    <i class="fas fa-ruler"></i> Measurements
                </a>
            </li> -->
            <li>
                <a href="profile.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i> My Profile
                </a>
            </li>
            <li>
                <a href="includes/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
</div>