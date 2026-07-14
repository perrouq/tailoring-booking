<?php
session_start();
require_once 'includes/config.php';
include 'includes/header.php';

// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Function to get order status in a user-friendly format
function getStatusBadge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="status-badge pending">Pending</span>';
        case 'processing':
            return '<span class="status-badge processing">Processing</span>';
        case 'shipped':
            return '<span class="status-badge shipped">Shipped</span>';
        case 'delivered':
            return '<span class="status-badge delivered">Delivered</span>';
        case 'completed':
            return '<span class="status-badge completed">Completed</span>';
        case 'failed':
            return '<span class="status-badge failed">Failed</span>';
        default:
            return '<span class="status-badge">' . ucfirst($status) . '</span>';
    }
}

// Get specific order details if order ID is provided
$orderDetails = null;
$orderItems = [];
$tailorAssignment = null;

if (isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    
    // Get order details
    $stmt = $pdo->prepare("
        SELECT o.*, 
               c.first_name, c.last_name, c.email, c.phone
        FROM orders o
        JOIN customers c ON o.user_id = c.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$orderId, $userId]);
    $orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($orderDetails) {
        // Get order items with color information
        $stmt = $pdo->prepare("
            SELECT oi.*, 
                   p.name, 
                   p.image,
                   pc.color_name,
                   pc.color_code
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN product_colors pc ON oi.color_id = pc.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get tailor assignment details
        $stmt = $pdo->prepare("
            SELECT ota.*, t.fullname, t.email, t.phone, t.specialty
            FROM order_tailor_assignments ota
            JOIN tailors t ON ota.tailor_id = t.tailor_id
            WHERE ota.order_id = ?
        ");
        $stmt->execute([$orderId]);
        $tailorAssignment = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Get all orders for the user with tailor assignment information
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(DISTINCT oi.id) as item_count,
           MAX(oi.product_id) as last_product_id,
           ota.tailor_id,
           ota.status as assignment_status,
           t.fullname as tailor_name
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN order_tailor_assignments ota ON o.id = ota.order_id
    LEFT JOIN tailors t ON ota.tailor_id = t.tailor_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get product image for order preview
function getOrderPreviewImage($orderId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
        LIMIT 1
    ");
    $stmt->execute([$orderId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $imagePath = $result ? 'images/'.$result['image'] : 'images/placeholder.jpg';
    
    return $imagePath;
}

// Function to format date
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('M d, Y, h:i A');
}

// Function to check if there are unread messages
function hasUnreadMessages($orderId, $userId, $pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM chat_messages
        WHERE order_id = ? 
        AND sender_type != 'customer' 
        AND read_status = 0
    ");
    $stmt->execute([$orderId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['count'] > 0;
}
?>
<style>
    /* Main Container Styles */
    .page-container {
        max-width: var(--container-width);
        margin: 0 auto;
        padding: 2rem 1rem;
        min-height: calc(100vh - 12rem);
    }

    .page-header {
        margin-bottom: 2rem;
        text-align: center;
        position: relative;
    }
    
    .page-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 0.5rem;
        position: relative;
        display: inline-block;
    }
    
    .page-title::after {
        content: "";
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(to right, var(--primary), var(--secondary));
        border-radius: 2px;
    }
    
    .page-subtitle {
        font-size: 1.2rem;
        color: var(--gray-400);
        margin-bottom: 1.5rem;
    }

    /* Empty Orders State */
    .empty-orders {
        text-align: center;
        padding: 4rem 2rem;
        background-color: white;
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
    }

    .empty-orders h2 {
        color: var(--gray-800);
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .empty-orders p {
        color: var(--gray-600);
        margin-bottom: 2rem;
    }

    .empty-orders .shop-now-btn {
        display: inline-block;
        padding: 0.75rem 2rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition);
        border: none;
        cursor: pointer;
    }

    .empty-orders .shop-now-btn:hover {
        background: linear-gradient(135deg, var(--primary-dark), var(--primary-dark));
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Order List Grid */
    .order-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    /* Order Card Styles */
    .order-card {
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .order-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .order-preview {
        height: 160px;
        background-size: cover;
        background-position: center;
        position: relative;
    }

    .order-badge {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .order-info {
        padding: 1rem;
    }

    .order-id {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: var(--primary-dark);
    }

    .order-date {
        color: var(--gray-600);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .order-total {
        font-weight: 600;
        font-size: 1.2rem;
        color: var(--secondary-dark);
        margin-bottom: 0.5rem;
    }

    .order-items {
        font-size: 0.9rem;
        color: var(--gray-600);
        display: flex;
        align-items: center;
    }

    .order-items:before {
        content: "\f07a";
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        margin-right: 0.5rem;
        color: var(--gray-500);
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.pending {
        background-color: #fff8e6;
        color: #976400;
        border: 1px solid #ffcb66;
    }

    .status-badge.processing {
        background-color: #e6f7ff;
        color: #0057b8;
        border: 1px solid #91d5ff;
    }

    .status-badge.shipped {
        background-color: #e6f9ff;
        color: #006d9e;
        border: 1px solid #87e8ff;
    }

    .status-badge.delivered {
        background-color: #e6ffed;
        color: #00864e;
        border: 1px solid #8af5c0;
    }

    .status-badge.completed {
        background-color: #f0f9eb;
        color: #60a917;
        border: 1px solid #b3e19d;
    }

    .status-badge.failed {
        background-color: #fff2f0;
        color: #cf1322;
        border: 1px solid #ffccc7;
    }

    /* Order Details Page */
    .back-button {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        margin-bottom: 1.5rem;
        background-color: var(--gray-100);
        color: var(--gray-800);
        text-decoration: none;
        border-radius: 4px;
        transition: all 0.2s ease;
    }

    .back-button:hover {
        background-color: var(--gray-200);
    }

    .order-details {
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }

    .order-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        padding: 1.5rem;
        background: linear-gradient(to right, var(--primary-light), var(--primary));
        color: white;
    }

    .order-header-left h2 {
        margin: 0 0 0.5rem 0;
        font-size: 1.5rem;
    }

    .order-header-left .order-date {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 0.5rem;
    }

    .order-header-left .order-status {
        display: flex;
        align-items: center;
    }

    .order-header-right {
        text-align: right;
    }

    .order-reference {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    .order-sections {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        padding: 1.5rem;
        background-color: #f9fafb;
        border-bottom: 1px solid var(--gray-100);
    }

    .order-section {
        padding: 1.5rem;
        background-color: white;
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
    }

    .order-section h3 {
        margin-top: 0;
        margin-bottom: 1rem;
        color: var(--primary-dark);
        font-size: 1.2rem;
        font-weight: 600;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--secondary-light);
        display: inline-block;
    }

    .order-total-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .order-total-row:last-child {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-100);
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--secondary-dark);
    }

    .order-items-list {
        padding: 1.5rem;
    }

    .order-items-list h3 {
        margin-top: 0;
        margin-bottom: 1.5rem;
        color: var(--primary-dark);
        font-size: 1.2rem;
        font-weight: 600;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--secondary-light);
        display: inline-block;
    }

    .order-items-list table {
        width: 100%;
        border-collapse: collapse;
    }

    .order-items-list table th {
        text-align: left;
        padding: 1rem;
        background-color: #f9fafb;
        color: var(--gray-700);
        font-weight: 600;
        border-bottom: 1px solid var(--gray-200);
    }

    .order-items-list table td {
        padding: 1rem;
        border-bottom: 1px solid var(--gray-100);
        vertical-align: middle;
    }

    .product-cell {
        display: flex;
        align-items: center;
    }

    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 1rem;
        border: 1px solid var(--gray-100);
    }

    .product-name {
        font-weight: 500;
    }

    /* Color indicator in order items */
    .item-color-info {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.25rem;
        padding: 0.25rem 0.5rem;
        background-color: var(--gray-50);
        border-radius: 12px;
        font-size: 0.875rem;
    }

    .item-color-swatch {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 0 1px var(--gray-200);
    }

    .item-color-name {
        color: var(--text-dark);
        font-weight: 500;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .order-header {
            flex-direction: column;
        }

        .order-header-right {
            margin-top: 1rem;
            text-align: left;
        }

        .order-items-list table {
            display: block;
            overflow-x: auto;
        }

        .order-sections {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .order-list {
            grid-template-columns: 1fr;
        }
    }

    /* Tailor and Chat Styles */
    .tailor-section {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .tailor-info {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .tailor-name {
        font-weight: bold;
        font-size: 1.1em;
    }

    .tailor-specialty {
        color: #666;
        font-style: italic;
    }

    .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.8em;
        font-weight: 500;
    }

    .badge.assigned {
        background-color: #e3f2fd;
        color: #0d47a1;
    }

    .badge.in_progress {
        background-color: #fff8e1;
        color: #ff8f00;
    }

    .badge.completed {
        background-color: #e8f5e9;
        color: #2e7d32;
    }

    .badge.cancelled {
        background-color: #ffebee;
        color: #c62828;
    }

    .chat-button {
        display: inline-flex;
        align-items: center;
        background-color: #4caf50;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 16px;
        font-size: 0.9em;
        cursor: pointer;
        text-decoration: none;
        margin-top: 10px;
        position: relative;
    }

    .chat-button:hover {
        background-color: #43a047;
        color: white;
        text-decoration: none;
    }

    .chat-button-small {
        display: inline-flex;
        align-items: center;
        background-color: #4caf50;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 6px 12px;
        font-size: 0.85em;
        cursor: pointer;
        text-decoration: none;
        position: relative;
    }

    .chat-button-small:hover {
        background-color: #43a047;
        color: white;
        text-decoration: none;
    }

    .order-tailor {
        margin-top: 8px;
        font-size: 0.9em;
    }

    .tailor-label {
        font-weight: 500;
        margin-right: 5px;
    }

    .order-actions {
        padding: 10px;
        display: flex;
        justify-content: flex-end;
        border-top: 1px solid #eee;
    }

    .unread-indicator {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 10px;
        height: 10px;
        background-color: #f44336;
        border-radius: 50%;
    }

    .order-card {
        display: flex;
        flex-direction: column;
    }

    .order-info {
        flex: 1;
    }
</style>
<div class="page-container">
    <?php if (isset($orderDetails)): ?>
        <!-- Order details view -->
        <a href="orders.php" class="back-button">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
            Back to All Orders
        </a>
        
        <div class="order-details">
            <div class="order-header">
                <div class="order-header-left">
                    <h2>Order #<?php echo $orderDetails['id']; ?></h2>
                    <div class="order-date">Placed on <?php echo formatDate($orderDetails['created_at']); ?></div>
                    <div class="order-status">
                        Status: <?php echo getStatusBadge($orderDetails['payment_status']); ?>
                    </div>
                </div>
                <div class="order-header-right">
                    <div class="order-reference">
                        Payment Reference: <?php echo $orderDetails['payment_reference'] ?: 'N/A'; ?>
                    </div>
                    <?php if ($tailorAssignment): ?>
                    <a href="order_chat.php?order_id=<?php echo $orderDetails['id']; ?>" class="chat-button btn-primary">
                        <i class="fas fa-comments" style="margin-right: 8px;"></i>
                        Chat with Tailor
                        <?php if (hasUnreadMessages($orderDetails['id'], $userId, $pdo)): ?>
                            <span class="unread-indicator"></span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="order-sections">
                <div class="order-section">
                    <h3>Shipping Information</h3>
                    <p>
                        <strong><?php echo $orderDetails['first_name'] . ' ' . $orderDetails['last_name']; ?></strong><br>
                        <?php echo nl2br($orderDetails['delivery_address']); ?><br>
                        <?php echo $orderDetails['delivery_city']; ?>, 
                        <?php echo $orderDetails['delivery_state']; ?> 
                        <?php echo $orderDetails['delivery_zip']; ?><br>
                        <strong>Phone:</strong> <?php echo $orderDetails['phone']; ?>
                    </p>
                    
                    <?php if ($orderDetails['delivery_instructions']): ?>
                        <p><strong>Delivery Instructions:</strong><br>
                           <?php echo nl2br($orderDetails['delivery_instructions']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="order-section">
                    <h3>Order Summary</h3>
                    <div class="order-total-row">
                        <span>Items (<?php echo count($orderItems); ?>):</span>
                        <span>₦<?php echo number_format($orderDetails['total_amount'], 2); ?></span>
                    </div>
                    <div class="order-total-row">
                        <span>Shipping:</span>
                        <span>₦0.00</span>
                    </div>
                    <div class="order-total-row">
                        <span>Total:</span>
                        <span>₦<?php echo number_format($orderDetails['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($tailorAssignment): ?>
            <div class="order-section tailor-section">
                <h3>Assigned Tailor</h3>
                <div class="tailor-info">
                    <div class="tailor-name"><?php echo htmlspecialchars($tailorAssignment['fullname']); ?></div>
                    <?php if ($tailorAssignment['specialty']): ?>
                    <div class="tailor-specialty"><?php echo htmlspecialchars($tailorAssignment['specialty']); ?></div>
                    <?php endif; ?>
                    <div class="tailor-status">
                        Status: <span class="badge <?php echo $tailorAssignment['status']; ?>">
                            <?php echo ucwords(str_replace('_', ' ', $tailorAssignment['status'])); ?>
                        </span>
                    </div>
                    <?php if ($tailorAssignment['due_date']): ?>
                    <div class="due-date">
                        Expected completion: <?php echo date('M d, Y', strtotime($tailorAssignment['due_date'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="order-items-list">
                <h3>Order Items</h3>
                <table>
                    <thead> 
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orderItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="product-cell">
                                        <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="product-image">
                                        <div>
                                            <div class="product-name"><?php echo $item['name']; ?></div>
                                            <?php if (!empty($item['color_name'])): ?>
                                                <div class="item-color-info">
                                                    <div class="item-color-swatch" style="background-color: <?php echo htmlspecialchars($item['color_code']); ?>"></div>
                                                    <span class="item-color-name"><?php echo htmlspecialchars($item['color_name']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>₦<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>₦<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Orders list view -->
        <div class="page-header">
            <h1 class="page-title">My Orders</h1>
            <p class="page-subtitle">Manage your orders and communicate with assigned tailors</p>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <i class="fas fa-shopping-cart" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 1.5rem;"></i>
                <h2>You haven't placed any orders yet</h2>
                <p>Browse our collection and find something you'll love.</p>
                <a href="index.php" class="shop-now-btn">
                    <i class="fas fa-store" style="margin-right: 8px;"></i>
                    Shop Now
                </a>
            </div>
        <?php else: ?>
            <div class="order-list">
                <?php foreach($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-preview" onclick="location.href='orders.php?id=<?php echo $order['id']; ?>'" style="background-image: url('<?php echo getOrderPreviewImage($order['id'], $pdo); ?>')">
                            <div class="order-badge">
                                <?php echo getStatusBadge($order['payment_status']); ?>
                            </div>
                        </div>
                        <div class="order-info" onclick="location.href='orders.php?id=<?php echo $order['id']; ?>'">
                            <div class="order-id">Order #<?php echo $order['id']; ?></div>
                            <div class="order-date"><?php echo formatDate($order['created_at']); ?></div>
                            <div class="order-total">₦<?php echo number_format($order['total_amount'], 2); ?></div>
                            <div class="order-items"><?php echo $order['item_count']; ?> items</div>
                            
                            <?php if (!empty($order['tailor_id'])): ?>
                            <div class="order-tailor">
                                <span class="tailor-label">Tailor:</span>
                                <span class="tailor-name"><?php echo htmlspecialchars($order['tailor_name']); ?></span>
                                <span class="tailor-status badge <?php echo $order['assignment_status']; ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $order['assignment_status'])); ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($order['tailor_id'])): ?>
                        <div class="order-actions">
                            <a href="order_chat.php?order_id=<?php echo $order['id']; ?>" class="chat-button-small">
                                <i class="fas fa-comments"></i>
                                Chat
                                <?php if (hasUnreadMessages($order['id'], $userId, $pdo)): ?>
                                    <span class="unread-indicator"></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    // Initialize cart count
    window.addEventListener('DOMContentLoaded', () => {
        if (typeof cart !== 'undefined') {
            cart.updateCartCount();
        }
    });
</script>