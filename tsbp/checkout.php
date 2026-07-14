<?php
session_start();
require_once 'includes/config.php';
include 'includes/header.php';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$user = null;
$measurements = null;

if ($is_logged_in) {
    // Get user information
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Get user measurements
    $stmt = $conn->prepare("SELECT * FROM customer_measurements WHERE customer_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $measurements = $result->fetch_assoc();
}
 
// Calculate cart total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $product_id = $item['product_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if ($product) {
        $total += $product['price'] * $item['quantity'];
    }
}
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
            --gray-200: #cbd2d9;
            --gray-300: #9aa5b1;
            --gray-400: #7b8794;
            --gray-500: #616e7c;
            --gray-600: #52606d;
            --gray-700: #3e4c59;
            --gray-800: #323f4b;
            --gray-900: #1f2933;
            --red-500: #ef4444;
            --success: #10b981;
            --success-light: #d1fae5;
            --warning: #f59e0b;
            --danger: #ef4444;
            --container-width: 1200px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s ease;
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gray-50);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: var(--container-width);
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .page-title {
            font-size: 2.25rem;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .checkout-login-prompt {
            background-color: var(--primary-light);
            color: var(--text-light);
            padding: 1.25rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: var(--shadow-md);
        }

        .checkout-login-prompt p {
            margin: 0.5rem 0;
            font-size: 1rem;
        }

        .checkout-login-prompt a {
            color: var(--text-light);
            font-weight: 600;
            text-decoration: underline;
            transition: var(--transition);
        }

        .checkout-login-prompt a:hover {
            color: var(--secondary-light);
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 768px) {
            .checkout-grid {
                grid-template-columns: 2fr 1fr;
            }
        }

        .checkout-form-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .form-section {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-100);
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section h2 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section h2 i {
            font-size: 1.25rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        @media (min-width: 640px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .form-group.full {
                grid-column: span 2;
            }
        }

        .form-group {
            margin-bottom: 0.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--gray-700);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            color: var(--gray-800);
            background-color: var(--gray-50);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-light);
            outline: none;
            box-shadow: 0 0 0 3px rgba(94, 53, 177, 0.1);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--gray-400);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.025em;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--text-light);
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-dark));
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .order-summary {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            padding: 1.5rem;
            position: sticky;
            top: 2rem;
        }

        .order-summary h2 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--gray-100);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .summary-items {
            margin-bottom: 1.5rem;
            max-height: 300px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .summary-items::-webkit-scrollbar {
            width: 6px;
        }

        .summary-items::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 10px;
        }

        .summary-items::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 10px;
        }

        .summary-item {
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .item-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .item-name {
            font-weight: 500;
            color: var(--gray-800);
        }

        .item-price {
            font-weight: 600;
            color: var(--primary);
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 2px solid var(--gray-100);
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .spinner {
            display: inline-block;
            width: 1.25rem;
            height: 1.25rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .checkout-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--gray-200);
            z-index: 1;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }

        .step-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background-color: var(--gray-200);
            color: var(--gray-600);
            border-radius: 50%;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .step-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            text-align: center;
        }

        .step.active .step-number {
            background-color: var(--primary);
            color: white;
        }

        .step.active .step-label {
            color: var(--primary);
            font-weight: 600;
        }

        .step.completed .step-number {
            background-color: var(--success);
            color: white;
        }

        .step.completed .step-number::after {
            content: "✓";
        }

        .step.completed .step-label {
            color: var(--success);
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding: 0.75rem;
            background-color: var(--success-light);
            color: var(--success);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .form-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-100);
        }
        
        .form-section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-light);
            color: white;
            border-radius: 50%;
        }
        
        .edit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .edit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .measurements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .measurement-item {
            padding: 0.75rem;
            background: var(--gray-50);
            border-radius: 6px;
            border-left: 3px solid var(--primary);
        }
        
        .measurement-label {
            font-size: 0.75rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        
        .measurement-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-dark);
        }
        
        .no-measurements-warning {
            padding: 1rem;
            background: rgba(255, 152, 0, 0.1);
            border-left: 4px solid var(--warning);
            border-radius: 6px;
            color: var(--gray-800);
        }
        
        .no-measurements-warning strong {
            color: var(--warning);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal.show {
            display: block;
        }
        
        .modal-dialog {
            position: relative;
            margin: 2rem auto;
            max-width: 800px;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--shadow-xl);
        }
        
        .modal-header {
            background: var(--primary);
            color: white;
            padding: 1.5rem;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }
        
        .btn-close {
            background: transparent;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .modal-footer {
            padding: 1rem 2rem;
            border-top: 1px solid var(--gray-100);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        
        .btn-secondary {
            background: var(--gray-300);
            color: var(--gray-800);
        }
        
        .btn-secondary:hover {
            background: var(--gray-400);
        }
        
        .btn-save {
            background: var(--primary);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-save:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Responsive padding adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
                margin: 1rem auto;
            }
            
            .page-title {
                font-size: 1.75rem;
                margin-bottom: 1rem;
            }
            
            .form-section {
                padding: 1.25rem;
            }
            
            .measurements-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-dialog {
                margin: 1rem;
            }
            
            .modal-body {
                padding: 1.5rem 1rem;
            }
        }
    </style>
    <div class="container">
        <h1 class="page-title">Checkout</h1>
        
        <div class="checkout-steps">
            <div class="step completed">
                <div class="step-number">1</div>
                <div class="step-label">Shopping Cart</div>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-label">Shipping & Billing</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Payment</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>
        
        <?php if (isset($_SESSION['profile_updated']) && $_SESSION['profile_updated']): ?>
    <div class="alert alert-success" style="background-color: var(--success-light); border-left: 4px solid var(--success); padding: 1rem; border-radius: 6px; margin-bottom: 2rem;">
        <strong><i class="fas fa-check-circle"></i></strong>
        <p style="margin: 0.5rem 0 0 0;"><?php echo $_SESSION['update_message']; ?></p>
    </div>
    <?php 
    unset($_SESSION['profile_updated']); 
    unset($_SESSION['update_message']); 
    ?>
<?php endif; ?>

<?php if (isset($_SESSION['measurements_error'])): ?>
    <div class="alert alert-danger" style="background-color: rgba(239, 68, 68, 0.1); border-left: 4px solid var(--red-500); padding: 1rem; border-radius: 6px; margin-bottom: 2rem;">
        <strong><i class="fas fa-exclamation-circle"></i></strong>
        <p style="margin: 0.5rem 0 0 0;"><?php echo $_SESSION['measurements_error']; ?></p>
    </div>
    <?php unset($_SESSION['measurements_error']); ?>
<?php endif; ?>
        
        <?php if (!$is_logged_in): ?>
            <div class="checkout-login-prompt">
                <p><i class="fas fa-user-circle"></i> Already have an account? <a href="login.php?redirect=checkout.php">Login</a> to use your saved information.</p>
                <p><i class="fas fa-user-plus"></i> Don't have an account? <a href="register.php?redirect=checkout.php">Register</a> to save your details for future purchases.</p>
            </div>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <div class="checkout-form-container">
                <form id="checkout-form" method="post" action="process_order.php">
                    <div class="form-section">
                        <div class="form-section-header">
                            <div class="form-section-title">
                                <div class="form-section-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h2>Personal Information</h2>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo $user ? htmlspecialchars($user['first_name']) : ''; ?>" placeholder="Enter your first name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo $user ? htmlspecialchars($user['last_name']) : ''; ?>" placeholder="Enter your last name" required>
                            </div>
                            
                            <div class="form-group full">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>" placeholder="example@email.com" required>
                            </div>
                            
                            <div class="form-group full">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo $user ? htmlspecialchars($user['phone']) : ''; ?>" placeholder="Enter your phone number" required>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($is_logged_in): ?>
                    <!-- Body Measurements Section -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <div class="form-section-title">
                                <div class="form-section-icon">
                                    <i class="fas fa-ruler-combined"></i>
                                </div>
                                <h2>Body Measurements</h2>
                            </div>
                            <button type="button" class="edit-btn" onclick="openMeasurementsModal()">
                                <i class="fas fa-edit"></i>
                                Update
                            </button>
                        </div>
                        
                        <?php if ($measurements && (!empty($measurements['neck']) || !empty($measurements['chest']) || !empty($measurements['waist']))): ?>
                            <div class="measurements-grid">
                                <?php if (!empty($measurements['neck'])): ?>
                                    <div class="measurement-item">
                                        <div class="measurement-label">Neck</div>
                                        <div class="measurement-value"><?php echo htmlspecialchars($measurements['neck']); ?> in</div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($measurements['chest'])): ?>
                                    <div class="measurement-item">
                                        <div class="measurement-label">Chest</div>
                                        <div class="measurement-value"><?php echo htmlspecialchars($measurements['chest']); ?> in</div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($measurements['shoulder'])): ?>
                                    <div class="measurement-item">
                                        <div class="measurement-label">Shoulder</div>
                                        <div class="measurement-value"><?php echo htmlspecialchars($measurements['shoulder']); ?> in</div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($measurements['sleeve'])): ?>
                                    <div class="measurement-item">
                                        <div class="measurement-label">Sleeve</div>
                                        <div class="measurement-value"><?php echo htmlspecialchars($measurements['sleeve']); ?> in</div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($measurements['waist'])): ?>
                                    <div class="measurement-item">
                                        <div class="measurement-label">Waist</div>
                                        <div class="measurement-value"><?php echo htmlspecialchars($measurements['waist']); ?> in</div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($measurements['hip'])): ?>
                                    <div class="measurement-item">
                                        <div class="measurement-label">Hip</div>
                                        <div class="measurement-value"><?php echo htmlspecialchars($measurements['hip']); ?> in</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-measurements-warning">
                                <strong><i class="fas fa-exclamation-triangle"></i> No measurements provided yet!</strong>
                                <p style="margin: 0.5rem 0 0 0;">Please add your body measurements for accurate tailoring. Click "Update" above to add your measurements.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-section">
                        <div class="form-section-header">
                            <div class="form-section-title">
                                <div class="form-section-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <h2>Shipping Address</h2>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group full">
                                <label for="address">Street Address</label>
                                <input type="text" id="address" name="address" 
                                value="<?php echo ($user && isset($user['address'])) ? htmlspecialchars($user['address']) : ''; ?>"
                                placeholder="Enter your full address" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city"
                                value="<?php echo ($user && isset($user['city'])) ? htmlspecialchars($user['city']) : ''; ?>"
                                placeholder="Enter your city" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="state">State</label>
                                <select id="state" name="state" required>
                                    <option value="">Select State</option>
                                    <option value="Kaduna" <?php echo ($user && $user['state'] == 'Kaduna') ? 'selected' : ''; ?>>Kaduna</option>
                                    <option value="Kano" <?php echo ($user && $user['state'] == 'Kano') ? 'selected' : ''; ?>>Kano</option>
                                    <option value="Katsina" <?php echo ($user && $user['state'] == 'Katsina') ? 'selected' : ''; ?>>Katsina</option>
                                    <option value="Kebbi" <?php echo ($user && $user['state'] == 'Kebbi') ? 'selected' : ''; ?>>Kebbi</option>
                                    <option value="Sokoto" <?php echo ($user && $user['state'] == 'Sokoto') ? 'selected' : ''; ?>>Sokoto</option>
                                    <option value="Zamfara" <?php echo ($user && $user['state'] == 'Zamfara') ? 'selected' : ''; ?>>Zamfara</option>
                                    <option value="FCT" <?php echo ($user && $user['state'] == 'FCT') ? 'selected' : ''; ?>>Federal Capital Territory</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="zip_code">ZIP Code</label>
                                <input type="text" id="zip_code" name="zip_code" 
                                value="<?php echo ($user && isset($user['zip_code'])) ? htmlspecialchars($user['zip_code']) : ''; ?>"  
                                placeholder="Enter ZIP/postal code" required>
                            </div>
                            
                            <div class="form-group full">
                                <label for="delivery_instructions">Delivery Instructions (Optional)</label>
                                <textarea id="delivery_instructions" name="delivery_instructions" rows="3" placeholder="Special delivery instructions, landmarks, or preferences"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <div class="form-section-header">
                            <div class="form-section-title">
                                <div class="form-section-icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                                <h2>Payment Method</h2>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group full">
                                <select id="payment_method" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="card">Card Payment</option>
                                    <option value="banktransfer">Bank Transfer</option>
                                    <option value="ussd">USSD</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <input type="hidden" name="amount" value="<?php echo $total; ?>">
                        <input type="hidden" name="payment_status" id="payment_status" value="pending">
                        <input type="hidden" name="payment_reference" id="payment_reference" value="">
                        <button type="button" id="pay-btn" class="btn btn-primary">
                            <i class="fas fa-lock"></i>
                            <span id="btn-text">Proceed to Payment (₦<?php echo number_format($total, 2); ?>)</span>
                            <span id="loading-spinner" class="spinner" style="display: none;"></span>
                        </button>
                        
                        <div class="secure-badge">
                            <i class="fas fa-shield-alt"></i> Your payment information is secured with SSL encryption
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="order-summary">
                <h2><i class="fas fa-shopping-basket"></i> Order Summary</h2>
                <div class="summary-items">
                    <?php
                    foreach ($_SESSION['cart'] as $item) {
                        $product_id = $item['product_id'];
                        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $product = $result->fetch_assoc();
                        
                        if ($product) {
                            $item_total = $product['price'] * $item['quantity'];
                            ?>
                            <div class="summary-item">
                                <div class="item-info">
                                    <span class="item-name"><?php echo htmlspecialchars($product['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                                    <span class="item-price">₦<?php echo number_format($item_total, 2); ?></span>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span>₦<?php echo number_format($total, 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Measurements Modal -->
    <div id="measurementsModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Body Measurements</h5>
                    <button type="button" class="btn-close" onclick="closeMeasurementsModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="measurementsForm" action="includes/update_measurements.php" method="post">
                        <input type="hidden" name="redirect_to" value="checkout.php">
                        
                        <div class="form-grid">
                            <div>
                                <h6 style="color: var(--primary); margin-bottom: 1rem;">Upper Body</h6>
                                
                                <div class="form-group">
                                    <label for="neck">Neck (inches)</label>
                                    <input type="number" class="form-control" id="neck" name="neck" step="0.01" 
                                           value="<?php echo $measurements['neck'] ?? ''; ?>" placeholder="e.g., 15.5">
                                </div>
                                
                                <div class="form-group">
                                    <label for="chest">Chest (inches)</label>
                                    <input type="number" class="form-control" id="chest" name="chest" step="0.01" 
                                           value="<?php echo $measurements['chest'] ?? ''; ?>" placeholder="e.g., 40">
                                </div>
                                
                                <div class="form-group">
                                    <label for="shoulder">Shoulder (inches)</label>
                                    <input type="number" class="form-control" id="shoulder" name="shoulder" step="0.01" 
                                           value="<?php echo $measurements['shoulder'] ?? ''; ?>" placeholder="e.g., 18">
                                </div>
                                
                                <div class="form-group">
                                    <label for="sleeve">Sleeve Length (inches)</label>
                                    <input type="number" class="form-control" id="sleeve" name="sleeve" step="0.01" 
                                           value="<?php echo $measurements['sleeve'] ?? ''; ?>" placeholder="e.g., 24">
                                </div>
                            </div>
                            
                            <div>
                                <h6 style="color: var(--primary); margin-bottom: 1rem;">Lower Body</h6>
                                
                                <div class="form-group">
                                    <label for="waist">Waist (inches)</label>
                                    <input type="number" class="form-control" id="waist" name="waist" step="0.01" 
                                           value="<?php echo $measurements['waist'] ?? ''; ?>" placeholder="e.g., 32">
                                </div>
                                
                                <div class="form-group">
                                    <label for="hip">Hip (inches)</label>
                                    <input type="number" class="form-control" id="hip" name="hip" step="0.01" 
                                           value="<?php echo $measurements['hip'] ?? ''; ?>" placeholder="e.g., 38">
                                </div>
                                
                                <div class="form-group">
                                    <label for="inseam">Inseam (inches)</label>
                                    <input type="number" class="form-control" id="inseam" name="inseam" step="0.01" 
                                           value="<?php echo $measurements['inseam'] ?? ''; ?>" placeholder="e.g., 30">
                                </div>
                                
                                <div class="form-group">
                                    <label for="thigh">Thigh (inches)</label>
                                    <input type="number" class="form-control" id="thigh" name="thigh" step="0.01" 
                                           value="<?php echo $measurements['thigh'] ?? ''; ?>" placeholder="e.g., 22">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group full" style="margin-top: 1rem;">
                            <label for="notes">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Any special fitting preferences or instructions..."><?php echo $measurements['notes'] ?? ''; ?></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeMeasurementsModal()">Cancel</button>
                    <button type="submit" form="measurementsForm" class="btn-save">
                        <i class="fas fa-save"></i> Save Measurements
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://checkout.flutterwave.com/v3.js"></script>
    <script>
        // Measurements Modal Functions
        function openMeasurementsModal() {
            document.getElementById('measurementsModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMeasurementsModal() {
            document.getElementById('measurementsModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('measurementsModal');
            if (event.target === modal) {
                closeMeasurementsModal();
            }
        }
        
        // Payment Processing
        document.getElementById('pay-btn').addEventListener('click', function(e) {
            setLoadingState(true);
            
            let form = document.getElementById('checkout-form');
            if (!form.checkValidity()) {
                form.reportValidity();
                setLoadingState(false);
                return;
            }
            
            let paymentMethod = document.getElementById('payment_method').value;
            if (!paymentMethod) {
                alert('Please select a payment method');
                setLoadingState(false);
                return;
            }
            
            saveCustomerData();
        });
        
        function setLoadingState(isLoading) {
            const button = document.getElementById('pay-btn');
            const buttonText = document.getElementById('btn-text');
            const spinner = document.getElementById('loading-spinner');
            
            if (isLoading) {
                button.disabled = true;
                button.classList.add('loading');
                buttonText.textContent = 'Processing...';
                spinner.style.display = 'inline-block';
            } else {
                button.disabled = false;
                button.classList.remove('loading');
                buttonText.textContent = 'Proceed to Payment (₦<?php echo number_format($total, 2); ?>)';
                spinner.style.display = 'none';
            }
        }
        
        function saveCustomerData() {
            let formData = {
                first_name: document.getElementById('first_name').value,
                last_name: document.getElementById('last_name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value,
                city: document.getElementById('city').value,
                state: document.getElementById('state').value,
                zip_code: document.getElementById('zip_code').value,
                payment_method: document.getElementById('payment_method').value,
                tx_ref: "TAILOR-" + Math.floor(Math.random() * 1000000000 + 1)
            };
            
            fetch('save_customer_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    makePayment(formData);
                } else {
                    setLoadingState(false);
                    alert('Error saving customer data: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                setLoadingState(false);
                alert('An error occurred while saving your information. Please try again.');
            });
        }
        
        function makePayment(customerData) {
            try {
                let amount = <?php echo $total; ?>;
                
                FlutterwaveCheckout({
                    public_key: "FLWPUBK_TEST-773c81b305e6f29a3106e5db8423cdbb-X",
                    tx_ref: customerData.tx_ref,
                    amount: amount,
                    currency: "NGN",
                    payment_options: customerData.payment_method,
                    redirect_url: "confirm_payment.php",
                    customer: {
                        email: customerData.email,
                        phone_number: customerData.phone,
                        name: customerData.first_name + " " + customerData.last_name,
                    },
                    customizations: {
                        title: "Tailor Booking System",
                        description: "Payment for tailor services",
                        logo: "https://yourdomain.com/images/logo.png",
                    },
                    callback: function(response) {
                        setLoadingState(false);
                    },
                    onclose: function() {
                        setLoadingState(false);
                    }
                });
            } catch (error) {
                console.error('Payment error:', error);
                setLoadingState(false);
                alert('An error occurred during payment initialization. Please try again or check your internet connection!');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('checkout-form');
            const inputs = form.querySelectorAll('input[required], select[required]');
            
            inputs.forEach(input => {
                input.addEventListener('invalid', function() {
                    input.classList.add('input-error');
                });
                
                input.addEventListener('input', function() {
                    if (input.validity.valid) {
                        input.classList.remove('input-error');
                    }
                });
            });
        });
    </script> 

<?php include 'includes/footer.php'; ?>