<?php
session_start();
require_once('includes/config.php');
//require_once('../functions/functions.php');
require_once('includes/tailor_functions.php');

// Check if tailor is logged in
if(!isset($_SESSION['tailor_id'])) {
    header("Location: ../admin/index.php");
    exit();
}

// Get statistics and recent data
$stats = getTailorStats($conn, $_SESSION['tailor_id']);
$assignedOrders = getTailorAssignedOrders($conn, $_SESSION['tailor_id'], 5);
$recentMessages = getTailorRecentMessages($conn, $_SESSION['tailor_id'], 5);
?>
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Tailor Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['tailor_name']); ?></p>
            </div>
            
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_assigned']; ?></h3>
                        <p>Assigned Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['in_progress']; ?></h3>
                        <p>In Progress</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['completed']; ?></h3>
                        <p>Completed</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['due_today']; ?></h3>
                        <p>Due Today</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3>My Assigned Orders</h3>
                            <a href="orders.php" class="btn btn-sm btn-outline">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Products</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($assignedOrders)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">No orders assigned yet</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($assignedOrders as $order): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($order['product_count']); ?> items</td>
                                                    <td>
                                                        <?php if($order['due_date']): ?>
                                                            <span class="<?php echo (strtotime($order['due_date']) < time()) ? 'text-danger' : ''; ?>">
                                                                <?php echo date('M d, Y', strtotime($order['due_date'])); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            Not set
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo getAssignmentStatusClass($order['status']); ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="chat.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info" title="Chat with Customer">
                                                            <i class="fas fa-comments"></i>
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
                            <h3>Recent Messages</h3>
                            <a href="messages.php" class="btn btn-sm btn-outline">View All</a>
                        </div>
                        <div class="card-body">
                            <ul class="message-feed">
                                <?php if (empty($recentMessages)): ?>
                                    <li class="message-item empty">No messages yet</li>
                                <?php else: ?>
                                    <?php foreach ($recentMessages as $message): ?>
                                        <li class="message-item <?php echo ($message['read_status'] == 0) ? 'unread' : ''; ?>">
                                            <div class="message-icon">
                                                <?php if ($message['sender_type'] == 'customer'): ?>
                                                    <i class="fas fa-user"></i>
                                                <?php elseif ($message['sender_type'] == 'admin'): ?>
                                                    <i class="fas fa-user-shield"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-cut"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div class="message-content">
                                                <div class="message-header">
                                                    <strong>Order #<?php echo $message['order_id']; ?></strong>
                                                    <span class="message-time"><?php echo timeElapsed($message['created_at']); ?></span>
                                                </div>
                                                <p><?php echo htmlspecialchars(substr($message['message'], 0, 50)) . (strlen($message['message']) > 50 ? '...' : ''); ?></p>
                                                <a href="chat.php?order_id=<?php echo $message['order_id']; ?>" class="btn btn-sm btn-outline-primary">Reply</a>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3>Upcoming Deadlines</h3>
                        </div>
                        <div class="card-body">
                            <?php 
                            $upcomingDeadlines = getUpcomingDeadlines($conn, $_SESSION['tailor_id']);
                            if (empty($upcomingDeadlines)): 
                            ?>
                                <p class="text-center">No upcoming deadlines</p>
                            <?php else: ?>
                                <ul class="deadline-list">
                                    <?php foreach ($upcomingDeadlines as $deadline): ?>
                                        <li class="deadline-item <?php echo (strtotime($deadline['due_date']) <= strtotime('+2 days')) ? 'urgent' : ''; ?>">
                                            <div class="deadline-info">
                                                <span class="deadline-date">
                                                    <i class="fas fa-calendar"></i> 
                                                    <?php echo date('M d, Y', strtotime($deadline['due_date'])); ?>
                                                </span>
                                                <span class="deadline-order">Order #<?php echo $deadline['order_id']; ?></span>
                                            </div>
                                            <div class="deadline-customer">
                                                <?php echo htmlspecialchars($deadline['customer_name']); ?>
                                            </div>
                                            <a href="view_order.php?id=<?php echo $deadline['order_id']; ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include('includes/footer.php'); ?>

