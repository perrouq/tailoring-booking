<?php
session_start();
require_once('includes/config.php');
require_once('includes/tailor_functions.php');

// Check if tailor is logged in
if(!isset($_SESSION['tailor_id'])) {
    header("Location: ../admin/index.php");
    exit();
}

// Get tailor ID
$tailor_id = $_SESSION['tailor_id'];

// Set default filters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build the query for messages
$query = "SELECT cm.message_id, cm.order_id, cm.message, cm.attachment, cm.read_status, cm.created_at,
          cm.sender_type, o.payment_status, o.delivery_address, o.delivery_city, o.delivery_state,
          CASE 
            WHEN cm.sender_type = 'customer' THEN CONCAT(c.first_name, ' ', c.last_name)
            WHEN cm.sender_type = 'admin' THEN a.fullname
            WHEN cm.sender_type = 'tailor' THEN t.fullname
          END AS sender_name
          FROM chat_messages cm
          INNER JOIN orders o ON cm.order_id = o.id
          INNER JOIN order_tailor_assignments ota ON o.id = ota.order_id
          LEFT JOIN customers c ON (cm.sender_type = 'customer' AND cm.sender_id = c.id)
          LEFT JOIN admins a ON (cm.sender_type = 'admin' AND cm.sender_id = a.admin_id)
          LEFT JOIN tailors t ON (cm.sender_type = 'tailor' AND cm.sender_id = t.tailor_id)
          WHERE ota.tailor_id = $tailor_id";

// Add filters
if ($status === 'unread') {
    $query .= " AND cm.read_status = 0 AND cm.sender_type != 'tailor'";
} elseif ($status === 'read') {
    $query .= " AND cm.read_status = 1";
}

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (cm.message LIKE '%$search%' OR 
                     CONCAT(c.first_name, ' ', c.last_name) LIKE '%$search%' OR 
                     a.fullname LIKE '%$search%' OR 
                     t.fullname LIKE '%$search%' OR 
                     cm.order_id LIKE '%$search%')";
}

// Get total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM ($query) as subquery";
$countResult = $conn->query($countQuery);
$totalMessages = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalMessages / $limit);

// Add ordering and pagination
$query .= " ORDER BY cm.created_at DESC LIMIT $offset, $limit";
$result = $conn->query($query);

// Mark message as read if coming from notification
if(isset($_GET['mark_read']) && isset($_GET['message_id'])) {
    $messageId = (int)$_GET['message_id'];
    $updateQuery = "UPDATE chat_messages SET read_status = 1 
                    WHERE message_id = $messageId 
                    AND sender_type != 'tailor'";
    $conn->query($updateQuery);
    
    // Redirect to remove the parameters from URL
    header("Location: messages.php?status=$status&search=" . urlencode($search) . "&page=$page");
    exit();
}

// Include header and sidebar
include('includes/header.php');
include('includes/sidebar.php');

// Helper function to get message status class
function getMessageStatusClass($senderType, $readStatus) {
    if($senderType == 'tailor') {
        return 'outgoing';
    } else {
        return ($readStatus == 0) ? 'unread' : 'read';
    }
}

// Helper function to format time
function formatMessageTime($timestamp) {
    $now = time();
    $messageTime = strtotime($timestamp);
    $diff = $now - $messageTime;
    
    if ($diff < 60) {
        return "Just now";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . " min" . ($mins > 1 ? "s" : "") . " ago";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    } else {
        return date("M d, Y", $messageTime);
    }
}
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>Message Center</h1>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="filters">
                    <form action="" method="GET" class="filter-form">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <select name="status" class="form-select">
                                    <option value="">All Messages</option>
                                    <option value="unread" <?php if ($status == 'unread') echo 'selected'; ?>>Unread</option>
                                    <option value="read" <?php if ($status == 'read') echo 'selected'; ?>>Read</option>
                                </select>
                            </div>

                            <div class="col-md-6 form-group search-group">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search messages..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="message-list">
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()):
                    ?>
                    <div class="message-item <?php echo getMessageStatusClass($row['sender_type'], $row['read_status']); ?>">
                        <div class="message-header">
                            <div class="message-info">
                                <span class="badge <?php 
                                    echo $row['sender_type'] == 'customer' ? 'bg-primary' : 
                                         ($row['sender_type'] == 'admin' ? 'bg-danger' : 'bg-success'); 
                                ?>">
                                    <?php echo ucfirst($row['sender_type']); ?>
                                </span>
                                <strong><?php echo htmlspecialchars($row['sender_name']); ?></strong>
                                <span class="order-number">Order #<?php echo $row['order_id']; ?></span>
                                <span class="message-time"><?php echo formatMessageTime($row['created_at']); ?></span>
                            </div>
                            <div class="message-actions">
                                <?php if($row['read_status'] == 0 && $row['sender_type'] != 'tailor'): ?>
                                <a href="?mark_read=1&message_id=<?php echo $row['message_id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-light" title="Mark as Read">
                                    <i class="fas fa-check"></i>
                                </a>
                                <?php endif; ?>
                                <a href="./chat.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-primary" title="View Conversation">
                                    <i class="fas fa-reply"></i> Reply
                                </a>
                                <a href="view_order.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-info" title="View Order Details">
                                    <i class="fas fa-eye"></i> Details
                                </a>
                            </div>
                        </div>
                        <div class="message-content">
                            <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                            
                            <?php if(!empty($row['attachment'])): ?>
                            <div class="message-attachment">
                                <i class="fas fa-paperclip"></i> 
                                <a href="../uploads/chat/<?php echo htmlspecialchars($row['attachment']); ?>" target="_blank">
                                    View Attachment
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="message-footer">
                            <div class="delivery-info">
                                <?php if(!empty($row['delivery_address'])): ?>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($row['delivery_city'] . ', ' . $row['delivery_state']); ?>
                                </small>
                                <?php endif; ?>
                            </div>
                            <div class="order-status">
                                <span class="badge <?php
                                    echo $row['payment_status'] == 'completed' ? 'bg-success' :
                                    ($row['payment_status'] == 'pending' ? 'bg-warning' : 'bg-danger');
                                ?>">
                                    <?php echo ucfirst($row['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php
                        endwhile;
                    } else {
                        echo '<div class="text-center p-5"><p>No messages found</p></div>';
                    }
                    ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="Messages page navigation">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php
                            // Show page numbers
                            for ($i = 1; $i <= $totalPages; $i++) {
                                echo "<li class='page-item " . ($i == $page ? 'active' : '') . "'>";
                                echo "<a class='page-link' href='?page=$i&status=$status&search=" . urlencode($search) . "'>$i</a>";
                                echo "</li>";
                            }
                            ?>

                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
.message-list {
    margin-top: 20px;
}
.message-item {
    background-color: #fff;
    border-radius: 8px;
    border: 1px solid #e3e8ee;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    transition: all 0.2s ease;
}
.message-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.08);
}
.message-item.unread {
    border-left: 4px solid #3b82f6;
    background-color: #f7faff;
}
.message-item.outgoing {
    border-left: 4px solid #10b981;
}
.message-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e3e8ee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}
.message-info {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.message-actions {
    display: flex;
    gap: 5px;
}
.order-number {
    color: #6b7280;
    font-size: 0.9rem;
}
.message-time {
    color: #9ca3af;
    font-size: 0.85rem;
}
.message-content {
    padding: 5px;
    line-height: 1.5;
}
.message-content p {
    margin: 0;
}
.message-attachment {
    margin-top: 10px;
    padding: 8px;
    background-color: #f3f4f6;
    border-radius: 4px;
    display: inline-block;
}
.message-attachment a {
    color: #3b82f6;
    text-decoration: none;
    margin-left: 5px;
}
.message-footer {
    padding: 10px 15px;
    border-top: 1px solid #e3e8ee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f9fafb;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}
.delivery-info small {
    display: flex;
    align-items: center;
    gap: 5px;
}
@media (max-width: 768px) {
    .message-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    .message-actions {
        width: 100%;
        justify-content: flex-end;
    }
    .message-list {
    margin: 0px;
}
    
}
</style>

<?php include('includes/footer.php'); ?>