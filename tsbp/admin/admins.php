<?php
session_start();
require_once('includes/config.php');
require_once('functions/auth_functions.php');
require_once('functions/admin_functions.php');

// Check if admin is logged in and is super_admin
if(!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != 'super_admin') {
    $_SESSION['error'] = "You don't have permission to access this page";
    header("Location: dashboard.php");
    exit();
}

// Set default filters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get admins with pagination
$admins = getAdmins($conn, $status, $search, $limit, $offset);
$totalAdmins = getTotalAdmins($conn, $status, $search);
$totalPages = ceil($totalAdmins / $limit);

// Handle form submission for adding a new admin
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $fullname = trim($_POST['fullname']);
    $email = htmlspecialchars($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Basic validation
    if(empty($fullname) || empty($email) || empty($phone) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
    } else {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT admin_id FROM admins WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if($checkResult->num_rows > 0) {
            $_SESSION['error'] = "Email already exists";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new admin
            $insertStmt = $conn->prepare("INSERT INTO admins (fullname, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->bind_param("sssss", $fullname, $email, $phone, $hashedPassword, $role);
            $result = $insertStmt->execute();
            
            if($result) {
                // Log the action
                $actionDescription = "New admin account created: $email";
                logActivity(null, $_SESSION['admin_id'], 'create_admin', $actionDescription);
                
                $_SESSION['success'] = "Admin account created successfully";
                
                // Redirect to refresh the page
                header("Location: admins.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to create admin account";
            }
        }
    }
}

// Handle admin status toggle
if(isset($_GET['toggle_status']) && isset($_GET['admin_id'])) {
    $adminId = (int)$_GET['admin_id'];
    $newStatus = $_GET['toggle_status'];
    
    // Prevent toggling own account
    if($adminId == $_SESSION['admin_id']) {
        $_SESSION['error'] = "You cannot change your own account status";
    } elseif(in_array($newStatus, ['active', 'inactive'])) {
        $updateResult = updateAdminStatus($conn, $adminId, $newStatus);
        
        if($updateResult) {
            $_SESSION['success'] = "Admin status updated successfully";
        } else {
            $_SESSION['error'] = "Failed to update admin status";
        }
    }
    
    // Redirect to remove the toggle parameters from URL
    header("Location: admins.php?status=$status&search=" . urlencode($search) . "&page=$page");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>
    
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1>Manage Administrators</h1>
                <div class="header-actions">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                        <i class="fas fa-plus"></i> Add New Admin
                    </button>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="filters">
                        <form action="" method="GET" class="filter-form">
                            <div class="form-group">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active" <?php if($status == 'active') echo 'selected'; ?>>Active</option>
                                    <option value="inactive" <?php if($status == 'inactive') echo 'selected'; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="form-group search-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($admins)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No administrators found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($admins as $admin): ?>
                                        <tr>
                                            <td><?php echo $admin['admin_id']; ?></td>
                                            <td><?php echo htmlspecialchars($admin['fullname']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['phone']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $admin['role'] == 'super_admin' ? 'danger' : 'primary'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $admin['status'] == 'active' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($admin['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'Never'; ?>
                                            </td>
                                            <td class="actions">
                                                <?php if($admin['admin_id'] != $_SESSION['admin_id']): ?>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="statusDropdown<?php echo $admin['admin_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="statusDropdown<?php echo $admin['admin_id']; ?>">
                                                            <li>
                                                                <a class="dropdown-item" href="edit_admin.php?id=<?php echo $admin['admin_id']; ?>">
                                                                    <i class="fas fa-edit text-primary"></i> Edit Admin
                                                                </a>
                                                            </li>
                                                            
                                                            <?php if($admin['status'] != 'active'): ?>
                                                                <li>
                                                                    <a class="dropdown-item" href="?toggle_status=active&admin_id=<?php echo $admin['admin_id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>">
                                                                        <i class="fas fa-check-circle text-success"></i> Activate
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                            
                                                            <?php if($admin['status'] != 'inactive'): ?>
                                                                <li>
                                                                    <a class="dropdown-item" href="?toggle_status=inactive&admin_id=<?php echo $admin['admin_id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>">
                                                                        <i class="fas fa-ban text-warning"></i> Deactivate
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Current Account</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div> 
                    
                    <?php if($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if($page > 1): ?>
                                <a href="?page=<?php echo ($page - 1); ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" class="page-link">Previous</a>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" class="page-link <?php if($i == $page) echo 'active'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if($page < $totalPages): ?>
                                <a href="?page=<?php echo ($page + 1); ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" class="page-link">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAdminModalLabel">Add New Administrator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <div class="form-group mb-3">
                            <label for="fullname">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="phone">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="role">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_admin" class="btn btn-primary">Add Administrator</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php'); ?>
   