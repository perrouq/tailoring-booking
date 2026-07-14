<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0">DANALI-SHOP</h4>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="home.php">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
        </li>
        <li>
            <a href="products.php">
                <i class="fas fa-box"></i>
                Products
            </a>
        </li>
        <li>
            <a href="profile.php">
                <i class="fas fa-user"></i>
                Profile
            </a>
        </li>
        <li>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="dashboard-header">
        <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
    </div>

    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
        <?php
        // Get total products count
        $products_query = "SELECT COUNT(*) as total FROM products";
        $products_result = $conn->query($products_query);
        $total_products = $products_result->fetch_assoc()['total'];
        ?>
        <div class="dashboard-card">
            <div class="card-icon purple">
                <i class="fas fa-box fa-lg"></i>
            </div>
            <div class="card-title">Total Products</div>
            <div class="card-value"><?php echo $total_products; ?></div>
        </div>

        <div class="dashboard-card">
            <div class="card-icon blue">
                <i class="fas fa-users fa-lg"></i>
            </div>
            <div class="card-title">Active Users</div>
            <div class="card-value">Active</div>
        </div>
    </div>