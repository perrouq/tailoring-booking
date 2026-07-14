<?php
session_start();
require_once('includes/config.php');
require_once('functions/auth_functions.php');
require_once('functions/admin_functions.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Set default filters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total tailors count based on filters
$countQuery = "SELECT COUNT(*) as total FROM tailors";
$whereClause = [];

if ($status) {
    $whereClause[] = "status = '$status'";
}

if ($search) {
    $whereClause[] = "(fullname LIKE '%$search%' OR email LIKE '%$search%')";
}

if (!empty($whereClause)) {
    $countQuery .= " WHERE " . implode(" AND ", $whereClause);
}

$countResult = $conn->query($countQuery);
$totalTailors = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalTailors / $limit);

// Get tailors with pagination and filters
$query = "SELECT * FROM tailors";

if (!empty($whereClause)) {
    $query .= " WHERE " . implode(" AND ", $whereClause);
}

$query .= " ORDER BY created_at DESC LIMIT $offset, $limit";
$result = $conn->query($query);

// Handle tailor delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $tailor_id = $_GET['id'];
    $query = "DELETE FROM tailors WHERE tailor_id = $tailor_id";
    if ($conn->query($query) === TRUE) {
        $_SESSION['success'] = "Tailor deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting tailor: " . $conn->error;
    }
    
    // Redirect to remove the action parameters from URL
    header("Location: tailors.php?status=$status&search=" . urlencode($search) . "&page=$page");
    exit();
}

// Handle tailor status toggle
if(isset($_GET['toggle_status']) && isset($_GET['tailor_id'])) {
    $tailorId = (int)$_GET['tailor_id'];
    $newStatus = $_GET['toggle_status'];
    
    if(in_array($newStatus, ['active', 'inactive'])) {
        $updateQuery = "UPDATE tailors SET status = '$newStatus' WHERE tailor_id = $tailorId";
        $updateResult = $conn->query($updateQuery);
        
        if($updateResult) {
            $_SESSION['success'] = "Tailor status updated successfully";
        } else {
            $_SESSION['error'] = "Failed to update tailor status";
        }
    }
    
    // Redirect to remove the toggle parameters from URL
    header("Location: tailors.php?status=$status&search=" . urlencode($search) . "&page=$page");
    exit();
}



?>
<?php include 'includes/header.php';?>
<?php include 'includes/sidebar.php';?>
<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>Tailors Management</h1>
            <div class="header-actions">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTailorModal">
                    <i class="fas fa-plus"></i> Add New Tailor
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
                                <th>Specialty</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows == 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No tailors found</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['tailor_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['specialty']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] == 'active' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        <td class="actions">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="statusDropdown<?php echo $row['tailor_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="statusDropdown<?php echo $row['tailor_id']; ?>">
                                                    <li>
                                                        <a class="dropdown-item edit-tailor-btn" href="#" data-bs-toggle="modal" data-bs-target="#editTailorModal" 
                                                           data-id="<?php echo $row['tailor_id']; ?>"
                                                           data-name="<?php echo htmlspecialchars($row['fullname']); ?>"
                                                           data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                           data-phone="<?php echo htmlspecialchars($row['phone']); ?>"
                                                           data-address="<?php echo htmlspecialchars($row['address']); ?>"
                                                           data-specialty="<?php echo htmlspecialchars($row['specialty']); ?>"
                                                           data-status="<?php echo $row['status']; ?>">
                                                            <i class="fas fa-edit text-primary"></i> Edit Tailor
                                                        </a>
                                                    </li>
                                                    
                                                    <?php if($row['status'] != 'active'): ?>
                                                        <li>
                                                            <a class="dropdown-item" href="?toggle_status=active&tailor_id=<?php echo $row['tailor_id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>">
                                                                <i class="fas fa-check-circle text-success"></i> Activate
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($row['status'] != 'inactive'): ?>
                                                        <li>
                                                            <a class="dropdown-item" href="?toggle_status=inactive&tailor_id=<?php echo $row['tailor_id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>">
                                                                <i class="fas fa-ban text-warning"></i> Deactivate
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    
                                                    <li>
                                                        <a class="dropdown-item" href="?action=delete&id=<?php echo $row['tailor_id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>" 
                                                           onclick="return confirm('Are you sure you want to delete this tailor?');">
                                                            <i class="fas fa-trash text-danger"></i> Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
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

<!-- Add Tailor Modal -->
<div class="modal fade" id="addTailorModal" tabindex="-1" aria-labelledby="addTailorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTailorModalLabel">Add New Tailor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="includes/add_tailor_process.php" method="post">
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
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="specialty">Specialty</label>
                        <input type="text" class="form-control" id="specialty" name="specialty">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Tailor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Tailor Modal -->
<div class="modal fade" id="editTailorModal" tabindex="-1" aria-labelledby="editTailorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTailorModalLabel">Edit Tailor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="includes/edit_tailor_process.php" method="post">
                    <input type="hidden" id="edit_tailor_id" name="tailor_id">
                    
                    <div class="form-group mb-3">
                        <label for="edit_fullname">Full Name</label>
                        <input type="text" class="form-control" id="edit_fullname" name="fullname" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="edit_email">Email Address</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="edit_phone">Phone Number</label>
                        <input type="text" class="form-control" id="edit_phone" name="phone" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="edit_address">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="edit_specialty">Specialty</label>
                        <input type="text" class="form-control" id="edit_specialty" name="specialty">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="edit_password">Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="edit_status">Status</label>
                        <select class="form-control" id="edit_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit tailor modal functionality
    var editButtons = document.querySelectorAll('.edit-tailor-btn');
    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var name = this.getAttribute('data-name');
            var email = this.getAttribute('data-email');
            var phone = this.getAttribute('data-phone');
            var address = this.getAttribute('data-address');
            var specialty = this.getAttribute('data-specialty');
            var status = this.getAttribute('data-status');

            document.getElementById('edit_tailor_id').value = id;
            document.getElementById('edit_fullname').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_address').value = address;
            document.getElementById('edit_specialty').value = specialty;
            document.getElementById('edit_status').value = status;

            document.getElementById('editTailorModalLabel').textContent = 'Edit Tailor: ' + name;
        });
    });
    
    // Display success/error messages
    <?php if(isset($_SESSION['success'])): ?>
        alert('<?php echo $_SESSION['success']; ?>');
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        alert('<?php echo $_SESSION['error']; ?>');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
});
</script>

<?php include 'includes/footer.php'; ?>