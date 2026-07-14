<?php
session_start();
require_once 'includes/config.php';
include 'includes/header.php';
 
// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    //header('Location: login.php');
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}
 
// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get user measurements if they exist
$stmt = $conn->prepare("SELECT * FROM customer_measurements WHERE customer_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$measurements = $result->fetch_assoc();

// Profile update message
$profile_updated = isset($_SESSION['profile_updated']) ? $_SESSION['profile_updated'] : false;
$update_message = isset($_SESSION['update_message']) ? $_SESSION['update_message'] : "Profile updated successfully!";
unset($_SESSION['profile_updated']);
unset($_SESSION['update_message']);

// Measurements update message
$measurements_updated = isset($_SESSION['measurements_updated']) ? $_SESSION['measurements_updated'] : false;
$measurements_error = isset($_SESSION['measurements_error']) ? $_SESSION['measurements_error'] : null;
unset($_SESSION['measurements_updated']);
unset($_SESSION['measurements_error']);

// Default active tab (personal or measurements)
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'personal';
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
        --gray-800: #2d3748;
        --red-500: #ef4444;
        --success: #10b981;
        --container-width: 1200px;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }
    
    .profile-container {
        max-width: var(--container-width);
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    
    .profile-header {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .profile-header h1 {
        color: var(--primary-dark);
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    
    .profile-subtitle {
        color: var(--gray-800);
        font-size: 1.1rem;
    }
    
    .profile-card {
        background-color: var(--text-light);
        border-radius: 12px;
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }
    
    /* Alerts */
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .alert-success {
        background-color: rgba(16, 185, 129, 0.1);
        border-left: 4px solid var(--success);
        color: var(--success);
    }
    
    .alert-danger {
        background-color: rgba(239, 68, 68, 0.1);
        border-left: 4px solid var(--red-500);
        color: var(--red-500);
    }
    
    /* Tabs */
    .profile-tabs {
        display: flex;
        background-color: var(--gray-50);
        border-bottom: 1px solid var(--gray-100);
    }
    
    .tab-btn {
        flex: 1;
        padding: 1.25rem 1.5rem;
        text-align: center;
        font-weight: 600;
        color: var(--gray-800);
        text-decoration: none;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .tab-btn svg {
        transition: var(--transition);
    }
    
    .tab-btn.active {
        color: var(--primary);
        background-color: var(--text-light);
        position: relative;
    }
    
    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: var(--primary);
    }
    
    .tab-btn:hover:not(.active) {
        background-color: rgba(94, 53, 177, 0.05);
        color: var(--primary-light);
    }
    
    /* Profile Content */
    .profile-content {
        padding: 2rem;
    }
    
    .section-title {
        color: var(--primary-dark);
        font-size: 1.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--gray-100);
    }
    
    .subsection-title {
        color: var(--primary);
        font-size: 1.3rem;
        margin: 2rem 0 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    /* Form Styling */
    .profile-form {
        margin-top: 1.5rem;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group.full {
        grid-column: 1 / -1;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--gray-800);
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--gray-100);
        border-radius: 6px;
        font-size: 1rem;
        transition: var(--transition);
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary-light);
        box-shadow: 0 0 0 3px rgba(94, 53, 177, 0.1);
    }
    
    .form-group input[readonly] {
        background-color: var(--gray-50);
        cursor: not-allowed;
    }
    
    .form-group small {
        display: block;
        margin-top: 0.25rem;
        color: var(--gray-800);
        font-size: 0.85rem;
    }
    
    /* Measurements Styling */
    .measurements-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
    
    .measurement-column h3 {
        margin-bottom: 1.5rem;
    }
    
    .measurement-input {
        display: flex;
        align-items: center;
    }
    
    .measurement-input input {
        flex-grow: 1;
    }
    
    .measurement-input .unit {
        margin-left: 0.5rem;
        color: var(--gray-800);
        font-weight: 500;
    }
    
    .measurements-info {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background-color: rgba(94, 53, 177, 0.05);
        border-radius: 6px;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .measurements-info svg {
        color: var(--primary);
        flex-shrink: 0;
        margin-top: 0.2rem;
    }
    
    .measurements-info p {
        margin: 0;
        color: var(--gray-800);
    }
    
    .measurement-help {
        margin: 1.5rem 0;
        padding: 1rem;
        background-color: rgba(255, 152, 0, 0.1);
        border-radius: 6px;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .measurement-help svg {
        color: var(--secondary);
        flex-shrink: 0;
        margin-top: 0.2rem;
    }
    
    .measurement-help p {
        margin: 0;
    }
    
    .measurement-help a {
        color: var(--primary);
        font-weight: 500;
        text-decoration: none;
        transition: var(--transition);
    }
    
    .measurement-help a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }
    
    /* Button Styling */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: var(--transition);
    }
    
    .btn-primary {
        background-color: var(--primary);
        color: var(--text-light);
    }
    
    .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(94, 53, 177, 0.25);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .form-grid,
        .measurements-grid {
            grid-template-columns: 1fr;
        }
        
        .profile-tabs {
            flex-direction: column;
        }
        
        .tab-btn {
            padding: 1rem;
        }
        
        .profile-content {
            padding: 1.5rem 1rem;
        }
        
        .profile-header h1 {
            font-size: 2rem;
        }
    }
</style>

<div class="profile-container">
    <div class="profile-header">
        <h1>My Profile</h1>
        <p class="profile-subtitle">Manage your personal information and measurements</p>
    </div>
    
    <?php if ($profile_updated): ?>
        <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <?php echo $update_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($measurements_error): ?>
        <div class="alert alert-danger">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <?php echo $measurements_error; ?>
        </div>
    <?php endif; ?>
    
    <div class="profile-card">
        <div class="profile-tabs">
            <a href="?tab=personal" class="tab-btn <?php echo ($active_tab == 'personal') ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Personal Information
            </a>
            <a href="?tab=measurements" class="tab-btn <?php echo ($active_tab == 'measurements') ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12h20M2 12c0-2.76 2.24-5 5-5h10c2.76 0 5 2.24 5 5M2 12c0 2.76 2.24 5 5 5h3"></path>
                    <polyline points="20 12 18 10 16 12 14 10 12 12 10 10"></polyline>
                </svg>
                Body Measurements
            </a>
        </div>
        
        <div class="profile-content">
            <?php if ($active_tab == 'personal'): ?>
                <!-- Personal Information Form -->
                <h2 class="section-title">Personal Information</h2>
                
                <form action="includes/update_profile.php" method="post" class="profile-form">
                    <input type="hidden" name="update_type" value="personal">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required readonly>
                            <small>Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                    </div>
                    
                    <h3 class="subsection-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        Shipping Address
                    </h3>
                    
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" 
                            value="<?php echo ($user && isset($user['address'])) ? htmlspecialchars($user['address']) : ''; ?>"
                            placeholder="Enter your full address">
                        </div>
                        
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" 
                            value="<?php echo ($user && isset($user['city'])) ? htmlspecialchars($user['city']) : ''; ?>"
                            placeholder="Enter your city">
                        </div>
                        
                        <div class="form-group">
                            <label for="state">State</label>
                            <select id="state" name="state">
                                <option value="">Select State</option>
                                <option value="Abia" <?php if(isset($user['state']) && $user['state'] == 'Abia') echo 'selected'; ?>>Abia</option>
                                <option value="Adamawa" <?php if(isset($user['state']) && $user['state'] == 'Adamawa') echo 'selected'; ?>>Adamawa</option>
                                <!-- Add more states as needed -->
                                <option value="Yobe" <?php if(isset($user['state']) && $user['state'] == 'Yobe') echo 'selected'; ?>>Yobe</option>
                                <option value="Zamfara" <?php if(isset($user['state']) && $user['state'] == 'Zamfara') echo 'selected'; ?>>Zamfara</option>
                                <option value="FCT" <?php if(isset($user['state']) && $user['state'] == 'FCT') echo 'selected'; ?>>Federal Capital Territory (Abuja)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="zip_code">ZIP Code</label>
                            <input type="text" id="zip_code" name="zip_code" 
                            value="<?php echo ($user && isset($user['zip_code'])) ? htmlspecialchars($user['zip_code']) : ''; ?>"  
                            placeholder="Enter ZIP/postal code">
                        </div>
                    </div>
                    
                    <h3 class="subsection-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        Password
                    </h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_new_password">Confirm New Password</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Update Personal Info
                    </button>
                </form>
            
            <?php else: ?>
                <!-- Measurements Form -->
                <h2 class="section-title">Body Measurements</h2>
                <div class="measurements-info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <p>Please provide your body measurements in inches. These measurements help our tailors create perfectly fitted garments for you.</p>
                </div>
                 
                <form action="includes/update_measurements.php" method="post" class="profile-form">
                    <input type="hidden" name="update_type" value="measurements">
                    <div class="measurements-grid">
                        <div class="measurement-column">
                            <h3 class="subsection-title">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.38 3.46L16 2a4 4 0 01-8 0L3.62 3.46a2 2 0 00-1.34 2.23l.58 3.47a1 1 0 00.99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 002-2V10h2.15a1 1 0 00.99-.84l.58-3.47a2 2 0 00-1.34-2.23z"></path>
                                </svg>
                                Upper Body
                            </h3>
                            <div class="form-group">
                                <label for="neck">Neck</label>
                                <div class="measurement-input">
                                    <input type="number" id="neck" name="neck" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['neck'])) ? htmlspecialchars($measurements['neck']) : ''; ?>"
                                    placeholder="Neck measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="chest">Chest</label>
                                <div class="measurement-input">
                                    <input type="number" id="chest" name="chest" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['chest'])) ? htmlspecialchars($measurements['chest']) : ''; ?>"
                                    placeholder="Chest measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="shoulder">Shoulder</label>
                                <div class="measurement-input">
                                    <input type="number" id="shoulder" name="shoulder" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['shoulder'])) ? htmlspecialchars($measurements['shoulder']) : ''; ?>"
                                    placeholder="Shoulder measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="sleeve">Sleeve Length</label>
                                <div class="measurement-input">
                                    <input type="number" id="sleeve" name="sleeve" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['sleeve'])) ? htmlspecialchars($measurements['sleeve']) : ''; ?>"
                                    placeholder="Sleeve measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="bicep">Bicep</label>
                                <div class="measurement-input">
                                    <input type="number" id="bicep" name="bicep" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['bicep'])) ? htmlspecialchars($measurements['bicep']) : ''; ?>"
                                    placeholder="Bicep measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="wrist">Wrist</label>
                                <div class="measurement-input">
                                    <input type="number" id="wrist" name="wrist" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['wrist'])) ? htmlspecialchars($measurements['wrist']) : ''; ?>"
                                    placeholder="Wrist measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="measurement-column">
                            <h3 class="subsection-title">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M7 22l5-1 5 1V2l-5 1-5-1v20z"></path>
                                </svg>
                                Lower Body
                            </h3>
                            <div class="form-group">
                                <label for="waist">Waist</label>
                                <div class="measurement-input">
                                    <input type="number" id="waist" name="waist" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['waist'])) ? htmlspecialchars($measurements['waist']) : ''; ?>"
                                    placeholder="Waist measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="hip">Hip</label>
                                <div class="measurement-input">
                                    <input type="number" id="hip" name="hip" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['hip'])) ? htmlspecialchars($measurements['hip']) : ''; ?>"
                                    placeholder="Hip measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="inseam">Inseam</label>
                                <div class="measurement-input">
                                    <input type="number" id="inseam" name="inseam" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['inseam'])) ? htmlspecialchars($measurements['inseam']) : ''; ?>"
                                    placeholder="Inseam measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="thigh">Thigh</label>
                                <div class="measurement-input">
                                    <input type="number" id="thigh" name="thigh" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['thigh'])) ? htmlspecialchars($measurements['thigh']) : ''; ?>"
                                    placeholder="Thigh measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="knee">Knee</label>
                                <div class="measurement-input">
                                    <input type="number" id="knee" name="knee" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['knee'])) ? htmlspecialchars($measurements['knee']) : ''; ?>"
                                    placeholder="Knee measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="ankle">Ankle</label>
                                <div class="measurement-input">
                                    <input type="number" id="ankle" name="ankle" step="0.01" min="0" 
                                    value="<?php echo ($measurements && isset($measurements['ankle'])) ? htmlspecialchars($measurements['ankle']) : ''; ?>"
                                    placeholder="Ankle measurement">
                                    <span class="unit">in</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group full">
                        <label for="notes">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="4" placeholder="Add any additional information about your measurements or fit preferences"><?php echo ($measurements && isset($measurements['notes'])) ? htmlspecialchars($measurements['notes']) : ''; ?></textarea>
                    </div>
                    
                    <div class="measurement-help">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <p><strong>Need help with measurements?</strong> Check our <a href="#measurement-guide.php">measurement guide</a> for detailed instructions on how to take accurate measurements.</p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Save Measurements
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php include 'includes/footer.php'; ?>
        