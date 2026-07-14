<?php
session_start();
require_once 'includes/config.php';
include 'includes/header.php';

// Check if we have an order ID
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_GET['order_id'];

// Get order details
$stmt = $conn->prepare("SELECT o.*, c.first_name, c.last_name, c.email, c.phone 
                        FROM orders o 
                        LEFT JOIN customers c ON o.user_id = c.id 
                        WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Get order items
$items_stmt = $conn->prepare("SELECT oi.*, p.name, p.image 
                             FROM order_items oi 
                             JOIN products p ON oi.product_id = p.id 
                             WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = [];
while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
}

// Define status colors
$status_colors = [
    'pending' => '#f59e0b',  // Amber
    'paid' => '#10b981',     // Green
    'processing' => '#3b82f6', // Blue
    'completed' => '#10b981', // Green
    'cancelled' => '#ef4444', // Red
    'refunded' => '#8b5cf6'   // Purple
];

// Get status color
$status_color = isset($status_colors[strtolower($order['payment_status'])]) 
    ? $status_colors[strtolower($order['payment_status'])] 
    : '#718096'; // Default gray
?>
    <style>
        :root {
            --primary: #5e35b1;
            --primary-light: #7c51d1;
            --primary-dark: #4527a0;
            --secondary: #ff9800;
            --secondary-light: #ffb74d;
            --secondary-dark: #f57c00;
            --text-light: #ffffff;
            --text-dark: #212121;
            --gray-50: #f5f7fa;
            --gray-100: #e4e7eb;
            --gray-200: #cbd5e0;
            --gray-300: #a0aec0;
            --gray-400: #718096;
            --gray-800: #2d3748;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --container-width: 1200px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-50);
            color: var(--text-dark);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        /* Container styles */
        .container {
            max-width: var(--container-width);
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* Order confirmation styles */
        .order-confirmation {
            background-color: var(--text-light);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .confirmation-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--text-light);
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
        }

        .confirmation-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 10px;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255, 255, 255, 0.1) 10px,
                rgba(255, 255, 255, 0.1) 20px
            );
        }

        .confirmation-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .confirmation-header p {
            margin: 1rem 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .order-number {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.6rem 1.2rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .confirmation-body {
            padding: 2rem;
        }

        .confirmation-details {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            padding: 2rem;
            background-color: var(--text-light);
        }

        .confirmation-section {
            flex: 1;
            min-width: 250px;
        }

        .confirmation-section h2 {
            color: var(--primary);
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--gray-200);
            position: relative;
        }

        .confirmation-section h2::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background-color: var(--secondary);
        }

        .order-details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-details-table th,
        .order-details-table td {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .order-details-table th {
            text-align: left;
            color: var(--gray-400);
            font-weight: 500;
            width: 40%;
        }

        .order-details-table td {
            text-align: right;
            font-weight: 500;
        }

        .status-paid,
        .status-completed {
            color: var(--success);
            display: inline-flex;
            align-items: center;
            font-weight: 600;
        }

        .status-paid::before,
        .status-completed::before {
            content: '•';
            font-size: 1.5rem;
            margin-right: 5px;
        }

        .status-pending,
        .status-processing {
            color: var(--warning);
            display: inline-flex;
            align-items: center;
            font-weight: 600;
        }

        .status-pending::before,
        .status-processing::before {
            content: '•';
            font-size: 1.5rem;
            margin-right: 5px;
        }

        .status-cancelled {
            color: var(--danger);
            display: inline-flex;
            align-items: center;
            font-weight: 600;
        }

        .status-cancelled::before {
            content: '•';
            font-size: 1.5rem;
            margin-right: 5px;
        }

        .address-info {
            padding: 1rem;
            background-color: var(--gray-50);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-light);
        }

        .address-info p {
            margin: 0.5rem 0;
        }

        .delivery-instructions {
            margin-top: 1rem;
            padding: 1rem;
            background-color: rgba(255, 152, 0, 0.1);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--secondary);
        }

        /* Order items section */
        .order-items {
            margin-top: 2rem;
        }

        .order-items-header {
            padding: 0 2rem;
            margin-bottom: 1.5rem;
        }

        .order-items-header h2 {
            color: var(--primary);
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--gray-200);
            position: relative;
        }

        .order-items-header h2::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background-color: var(--secondary);
        }

        .order-items-list {
            padding: 0 2rem 2rem;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: var(--border-radius);
            background-color: var(--gray-50);
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
        }

        .order-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-right: 1rem;
            box-shadow: var(--shadow-sm);
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .order-item-options {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .order-item-option {
            font-size: 0.85rem;
            background-color: var(--gray-100);
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            color: var(--gray-800);
        }

        .order-item-price {
            font-weight: 600;
            color: var(--primary);
        }

        .order-item-quantity {
            font-size: 0.9rem;
            color: var(--gray-400);
        }

        /* Confirmation actions */
        .confirmation-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            padding: 2rem;
            background-color: var(--gray-50);
            border-top: 1px solid var(--gray-100);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--text-light);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: var(--text-dark);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: var(--text-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Success animation */
        .success-checkmark {
            display: flex;
            justify-content: center;
            margin: 1rem 0;
        }

        .success-checkmark .check-icon {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid var(--success);
        }

        .success-checkmark .check-icon::before {
            top: 3px;
            left: -2px;
            width: 30px;
            transform-origin: 100% 50%;
            border-radius: 100px 0 0 100px;
        }

        .success-checkmark .check-icon::after {
            top: 0;
            left: 30px;
            width: 60px;
            transform-origin: 0 50%;
            border-radius: 0 100px 100px 0;
            animation: rotate-circle 4.25s ease-in;
        }

        .success-checkmark .check-icon::before, 
        .success-checkmark .check-icon::after {
            content: '';
            height: 100px;
            position: absolute;
            background: var(--text-light);
            transform: rotate(-45deg);
        }

        .success-checkmark .check-icon .icon-line {
            height: 5px;
            background-color: var(--success);
            display: block;
            border-radius: 2px;
            position: absolute;
            z-index: 10;
        }

        .success-checkmark .check-icon .icon-line.line-tip {
            top: 46px;
            left: 14px;
            width: 25px;
            transform: rotate(45deg);
            animation: icon-line-tip 0.75s;
        }

        .success-checkmark .check-icon .icon-line.line-long {
            top: 38px;
            right: 8px;
            width: 47px;
            transform: rotate(-45deg);
            animation: icon-line-long 0.75s;
        }

        .success-checkmark .check-icon .icon-circle {
            top: -4px;
            left: -4px;
            z-index: 10;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            position: absolute;
            box-sizing: content-box;
            border: 4px solid rgba(16, 185, 129, 0.3);
        }

        .success-checkmark .check-icon .icon-fix {
            top: 8px;
            width: 5px;
            left: 26px;
            z-index: 1;
            height: 85px;
            position: absolute;
            transform: rotate(-45deg);
            background-color: var(--text-light);
        }

        @keyframes rotate-circle {
            0% {
                transform: rotate(-45deg);
            }
            5% {
                transform: rotate(-45deg);
            }
            12% {
                transform: rotate(-405deg);
            }
            100% {
                transform: rotate(-405deg);
            }
        }

        @keyframes icon-line-tip {
            0% {
                width: 0;
                left: 1px;
                top: 19px;
            }
            54% {
                width: 0;
                left: 1px;
                top: 19px;
            }
            70% {
                width: 50px;
                left: -8px;
                top: 37px;
            }
            84% {
                width: 17px;
                left: 21px;
                top: 48px;
            }
            100% {
                width: 25px;
                left: 14px;
                top: 46px;
            }
        }

        @keyframes icon-line-long {
            0% {
                width: 0;
                right: 46px;
                top: 54px;
            }
            65% {
                width: 0;
                right: 46px;
                top: 54px;
            }
            84% {
                width: 55px;
                right: 0px;
                top: 35px;
            }
            100% {
                width: 47px;
                right: 8px;
                top: 38px;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 0 0.5rem;
                margin: 1rem auto;
            }
            
            .confirmation-header {
                padding: 1.5rem 1rem;
            }
            
            .confirmation-header h1 {
                font-size: 1.5rem;
            }
            
            .confirmation-details {
                padding: 1rem;
                gap: 1rem;
            }
            
            .confirmation-section {
                flex: 100%;
            }
            
            .order-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-item-image {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .confirmation-actions {
                flex-direction: column;
                padding: 1rem;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>

    <div class="container">
        <div class="order-confirmation">
            <div class="confirmation-header">
                <div class="success-checkmark">
                    <div class="check-icon">
                        <span class="icon-line line-tip"></span>
                        <span class="icon-line line-long"></span>
                        <div class="icon-circle"></div>
                        <div class="icon-fix"></div>
                    </div>
                </div>
                <h1>Thank You for Your Order!</h1>
                <p>Your order has been received and is now being processed.</p>
                <div class="order-number">Order #<?php echo $order_id; ?></div>
            </div>
            
            <div class="confirmation-details">
                <div class="confirmation-section">
                    <h2>Order Summary</h2>
                    <table class="order-details-table">
                        <tr>
                            <th>Order Date</th>
                            <td><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <th>Payment Status</th>
                            <td><span class="status-<?php echo strtolower($order['payment_status']); ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                        </tr>
                        <tr>
                            <th>Order Status</th>
                            <td><span class="status-<?php echo strtolower($order['payment_status']); ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                        </tr>
                        <tr>
                            <th>Payment Method</th>
                            <td><?php echo !empty($order['payment_method']) ? ucfirst($order['payment_method']) : 'Not specified'; ?></td>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <td><strong>₦<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                        </tr>
                    </table>
                </div>
                
                <div class="confirmation-section">
                    <h2>Shipping Address</h2>
                    <div class="address-info">
                        <p><strong><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></strong></p>
                        <p><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                        <p><?php echo htmlspecialchars($order['delivery_city']); ?>, <?php echo htmlspecialchars($order['delivery_state']); ?> <?php echo htmlspecialchars($order['delivery_zip']); ?></p>
                        <p>Email: <?php echo htmlspecialchars($order['email']); ?></p>
                        <p>Phone: <?php echo htmlspecialchars($order['phone']); ?></p>
                    </div>
                    
                    <?php if (!empty($order['delivery_instructions'])): ?>
                    <div class="delivery-instructions">
                        <p><strong>Delivery Instructions:</strong><br><?php echo htmlspecialchars($order['delivery_instructions']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="order-items">
                <div class="order-items-header">
                    <h2>Order Items</h2>
                </div>
                
                <div class="order-items-list">
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <?php if ($item['image']): ?>
                                <img src="images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-image">
                            <?php else: ?>
                                <div class="order-item-image" style="background-color: var(--gray-200); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-tshirt" style="font-size: 2rem; color: var(--gray-400);"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="order-item-details">
                                <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                
                                <?php 
                                // Check if custom options exist (this would depend on your data structure)
                                $options = [];
                                if (!empty($item['custom_options'])) {
                                    $options = json_decode($item['custom_options'], true) ?? [];
                                }
                                
                                if (!empty($options)): 
                                ?>
                                <div class="order-item-options">
                                    <?php foreach ($options as $key => $value): ?>
                                        <span class="order-item-option"><?php echo htmlspecialchars($key); ?>: <?php echo htmlspecialchars($value); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="order-item-price">
                                    ₦<?php echo number_format($item['price'], 2); ?>
                                    <span class="order-item-quantity">× <?php echo $item['quantity']; ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="confirmation-actions">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                
                <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                <a href="orders.php?section=orders" class="btn btn-outline">
                    <i class="fas fa-clipboard-list"></i> View All Orders
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>


    