<?php
session_start();
require_once('includes/config.php');
require_once('functions/functions.php');

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}


// Get statistics and recent data
$stats = getTailoringStats($conn);
$recentOrders = getRecentOrders($conn, 5);
$recentLogs = getRecentActivityLogs($conn, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Tailoring Services Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body> 
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
            </div>
            
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_customers']; ?></h3>
                        <p>Total Customers</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_products']; ?></h3>
                        <p>Products</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-details">
                        <h3>₦<?php echo number_format($stats['total_income'], 2); ?></h3>
                        <p>Total Income</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['pending_orders']; ?></h3>
                        <p>Pending Orders</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent Orders</h3>
                            <a href="orders.php" class="btn btn-sm btn-outline">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentOrders)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No orders found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentOrders as $order): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                                    <td>₦<?php echo number_format($order['total_amount'], 2); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo getPaymentStatusClass($order['payment_status']); ?>">
                                                            <?php echo ucfirst($order['payment_status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Recent Activity</h3>
                        </div>
                        <div class="card-body">
                            <ul class="activity-feed">
                                <?php if (empty($recentLogs)): ?>
                                    <li class="activity-item">No recent activities</li>
                                <?php else: ?>
                                    <?php foreach ($recentLogs as $log): ?>
                                        <li class="activity-item">
                                            <div class="activity-icon bg-<?php echo getActivityIconClass($log['action']); ?>">
                                                <i class="fas fa-<?php echo getActivityIcon($log['action']); ?>"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p><?php echo htmlspecialchars($log['description']); ?></p>
                                                <small><?php echo timeElapsed($log['timestamp']); ?></small>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Income Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="income-summary">
                                <div class="income-item">
                                    <span class="income-label">Total Revenue:</span>
                                    <span class="income-value">₦<?php echo number_format($stats['total_income'], 2); ?></span>
                                </div>
                                <hr>
                                <div class="income-item">
                                    <span class="income-label">Average Order Value:</span>
                                    <span class="income-value">
                                        ₦<?php 
                                        $avg = $stats['total_orders'] > 0 ? $stats['total_income'] / $stats['total_orders'] : 0;
                                        echo number_format($avg, 2); 
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>