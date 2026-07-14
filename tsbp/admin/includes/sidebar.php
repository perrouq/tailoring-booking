<div class="sidebar" id="sidebar">
	<div class="sidebar-header">
		<h2>Admin Panel</h2>
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
					<i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span>
				</a>
			</li>
			<?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] == 'super_admin'): ?>
			<li>
				<a href="admins.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admins.php') ? 'active' : ''; ?>">
					<i class="fa-solid fa-users-gear"></i> <span>Users</span>
				</a>
			</li>
			<?php endif; ?>
			<li>
				<a href="tailors.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'tailors.php') ? 'active' : ''; ?>">
					<i class="fa-solid fa-scissors"></i> <span>Tailors</span>
				</a>
			</li>
			<li>
				<a href="product.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'product.php') ? 'active' : ''; ?>">
					<i class="fa-solid fa-shirt"></i> <span>Products</span>
				</a>
			</li>
			<li>
				<a href="orders.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>">
					<i class="fa-solid fa-cart-shopping"></i> <span>Orders</span>
				</a>
			</li>
			<li>
				<a href="profile.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
					<i class="fa-solid fa-user-circle"></i> <span>Profile</span>
				</a>
			</li>
			<li> 
				<a href="includes/logout.php">
					<i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span>
				</a>
			</li>
		</ul>
	</nav>
</div>