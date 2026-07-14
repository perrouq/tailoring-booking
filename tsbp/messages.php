<?php
session_start();
require_once 'includes/config.php';
include 'includes/header.php';

// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Get user ID
$userId = $_SESSION['user_id'];

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
          END AS sender_name,
          t.fullname AS tailor_name
          FROM chat_messages cm
          INNER JOIN orders o ON cm.order_id = o.id
          LEFT JOIN customers c ON (cm.sender_type = 'customer' AND cm.sender_id = c.id)
          LEFT JOIN admins a ON (cm.sender_type = 'admin' AND cm.sender_id = a.admin_id)
          LEFT JOIN tailors t ON (cm.sender_type = 'tailor' AND cm.sender_id = t.tailor_id)
          LEFT JOIN order_tailor_assignments ota ON o.id = ota.order_id
          WHERE o.user_id = ?";

// Create params array for prepared statement
$params = [$userId];

// Add filters
if ($status === 'unread') {
    $query .= " AND cm.read_status = 0 AND cm.sender_type != 'customer'";
} elseif ($status === 'read') {
    $query .= " AND cm.read_status = 1";
}

if (!empty($search)) {
    $searchTerm = "%$search%";  // Create the pattern with % wildcards
    $query .= " AND (cm.message LIKE ? OR 
                  CONCAT(c.first_name, ' ', c.last_name) LIKE ? OR 
                  a.fullname LIKE ? OR 
                  t.fullname LIKE ? OR 
                  cm.order_id LIKE ?)";
    
    // Add search params 5 times for each LIKE condition
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Count query for pagination
$countQuery = "SELECT COUNT(*) FROM ($query) as subquery";
$stmt = $pdo->prepare($countQuery);
$stmt->execute($params);
$totalMessages = $stmt->fetchColumn();
$totalPages = ceil($totalMessages / $limit);

// Add ordering and pagination
$query .= " ORDER BY cm.created_at DESC LIMIT $offset, $limit";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark message as read if coming from notification
if(isset($_GET['mark_read']) && isset($_GET['message_id'])) {
    $messageId = (int)$_GET['message_id'];
    $updateQuery = "UPDATE chat_messages SET read_status = 1 
                    WHERE message_id = ? 
                    AND sender_type != 'customer'";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([$messageId]);
    
    // Redirect using JavaScript instead of header()
    echo "<script>window.location.href = 'messages.php?status=" . urlencode($status) . "&search=" . urlencode($search) . "&page=$page';</script>";
    exit();
}

// Helper function to get message status class
function getMessageStatusClass($senderType, $readStatus) {
    if($senderType == 'customer') {
        return 'message-sent';
    } else {
        return ($readStatus == 0) ? 'unread' : 'message-received';
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
<style>
/* Page Container */
.page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Back Button */
.back-button {
    display: inline-flex;
    align-items: center;
    padding: 8px 12px;
    background-color: #f8f9fa;
    color: #495057;
    border-radius: 4px;
    font-size: 14px;
    margin-bottom: 20px;
    text-decoration: none;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
}

.back-button:hover {
    background-color: #e9ecef;
    text-decoration: none;
}

/* Message Center Container */
.message-center-container {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

/* Message Center Header */
.message-center-header {
    padding: 20px;
    border-bottom: 1px solid #e3e8ee;
    background-color: #f8f9fa;
}

.message-center-header h1 {
    margin: 0;
    font-size: 24px;
    color: #333;
}

/* Filters */
.message-center-filters {
    padding: 15px 20px;
    border-bottom: 1px solid #e3e8ee;
    background-color: #fff;
}

.filter-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.filter-col {
    flex: 1;
    min-width: 150px;
}

.search-col {
    flex: 2;
}

.form-select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
    color: #495057;
}

.search-input-group {
    display: flex;
    width: 100%;
}

.form-control {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px 0 0 4px;
    font-size: 14px;
    color: #495057;
}

.search-btn {
    padding: 8px 15px;
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    transition: background-color 0.2s;
}

.search-btn:hover {
    background-color: #2563eb;
}

/* Message List */
.message-list {
    padding: 15px 20px;
}

/* Message Item */
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

.message-item.message-sent {
    border-left: 4px solid #10b981;
}

/* Message Header */
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

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    color: white;
}

.badge-primary {
    background-color: #3b82f6;
}

.badge-admin {
    background-color: #dc2626;
}

.badge-tailor {
    background-color: #10b981;
}

.badge-success {
    background-color: #10b981;
}

.badge-warning {
    background-color: #f59e0b;
}

.badge-danger {
    background-color: #ef4444;
}

.order-number {
    color: #6b7280;
    font-size: 0.9rem;
}

.message-time {
    color: #9ca3af;
    font-size: 0.85rem;
}

/* Message Actions */
.message-actions {
    display: flex;
    gap: 5px;
}

.action-btn {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 13px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}

.mark-read-btn {
    background-color: #f3f4f6;
    color: #4b5563;
}

.mark-read-btn:hover {
    background-color: #e5e7eb;
}

.reply-btn {
    background-color: #3b82f6;
    color: white;
}

.reply-btn:hover {
    background-color: #2563eb;
}

.details-btn {
    background-color: #0ea5e9;
    color: white;
}

.details-btn:hover {
    background-color: #0284c7;
}

/* Message Content */
.message-content {
    padding: 15px;
    line-height: 1.5;
}

.message-content p {
    margin: 0;
    font-size: 14px;
}

.message-attachment {
    margin-top: 10px;
    padding: 8px;
    background-color: #f3f4f6;
    border-radius: 4px;
    display: inline-block;
    font-size: 13px;
}

.message-attachment a {
    color: #3b82f6;
    text-decoration: none;
    margin-left: 5px;
}

.message-attachment a:hover {
    text-decoration: underline;
}

/* Message Footer */
.message-footer {
    padding: 10px 15px;
    border-top: 1px solid #e3e8ee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f9fafb;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
    font-size: 13px;
}

.delivery-info {
    display: flex;
    gap: 15px;
}

.delivery-info small {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6b7280;
}

.tailor-info {
    color: #4b5563;
}

/* No Messages */
.no-messages {
    text-align: center;
    padding: 40px 0;
    color: #6b7280;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    padding: 20px 0;
}

.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 5px;
}

.page-item {
    display: inline-block;
}

.page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 4px;
    border: 1px solid #d1d5db;
    background-color: #fff;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s;
}

.page-item.active .page-link {
    background-color: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.page-link:hover {
    background-color: #f3f4f6;
    border-color: #d1d5db;
}

.page-item.active .page-link:hover {
    background-color: #2563eb;
    border-color: #2563eb;
}

/* Responsive */
@media (max-width: 768px) {
    .page-container {
        padding: 10px;
    }
    
    .message-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .message-actions {
        width: 100%;
        justify-content: flex-end;
        margin-top: 5px;
    }
    
    .message-info {
        flex-wrap: wrap;
    }
    
    .filter-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .filter-col {
        width: 100%;
    }
    
    .message-footer {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    .delivery-info {
        width: 100%;
        flex-direction: column;
        gap: 5px;
    }
    
    .order-status {
        width: 100%;
        display: flex;
        justify-content: flex-start;
    }
}
</style>
<div class="page-container">
    <a href="index.php" class="back-button">
        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
        Back to Home 
    </a>
    
    <div class="message-center-container">
        <div class="message-center-header">
            <h1>Message Center</h1>
        </div>
        
        <div class="message-center-filters">
            <form action="" method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-col">
                        <select name="status" class="form-select">
                            <option value="">All Messages</option>
                            <option value="unread" <?php if ($status == 'unread') echo 'selected'; ?>>Unread</option>
                            <option value="read" <?php if ($status == 'read') echo 'selected'; ?>>Read</option>
                        </select>
                    </div>

                    <div class="filter-col search-col">
                        <div class="search-input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search messages..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="message-list">
            <?php
            if (!empty($messages)) {
                foreach ($messages as $row):
            ?>
            <div class="message-item <?php echo getMessageStatusClass($row['sender_type'], $row['read_status']); ?>">
                <div class="message-header">
                    <div class="message-info">
                        <span class="badge <?php 
                            echo $row['sender_type'] == 'customer' ? 'badge-primary' : 
                                 ($row['sender_type'] == 'admin' ? 'badge-admin' : 'badge-tailor'); 
                        ?>">
                            <?php echo ucfirst($row['sender_type']); ?>
                        </span>
                        <strong><?php echo htmlspecialchars($row['sender_name']); ?></strong>
                        <span class="order-number">Order #<?php echo $row['order_id']; ?></span>
                        <span class="message-time"><?php echo formatMessageTime($row['created_at']); ?></span>
                    </div>
                    <div class="message-actions">
                        <?php if($row['read_status'] == 0 && $row['sender_type'] != 'customer'): ?>
                        <a href="?mark_read=1&message_id=<?php echo $row['message_id']; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $page; ?>" class="action-btn mark-read-btn" title="Mark as Read">
                            <i class="fas fa-check"></i>
                        </a>
                        <?php endif; ?>
                        <a href="order_chat.php?order_id=<?php echo $row['order_id']; ?>" class="action-btn reply-btn" title="View Conversation">
                            <i class="fas fa-reply"></i> Reply
                        </a>
                        <a href="orders.php?id=<?php echo $row['order_id']; ?>" class="action-btn details-btn" title="View Order Details">
                            <i class="fas fa-eye"></i> Details
                        </a>
                    </div>
                </div>
                <div class="message-content">
                    <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                    
                    <?php if(!empty($row['attachment'])): ?>
                    <div class="message-attachment">
                        <i class="fas fa-paperclip"></i> 
                        <a href="uploads/chat/<?php echo htmlspecialchars($row['attachment']); ?>" target="_blank">
                            View Attachment
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="message-footer">
                    <div class="delivery-info">
                        <?php if(!empty($row['delivery_city'])): ?>
                        <small class="text-muted">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($row['delivery_city'] . ', ' . $row['delivery_state']); ?>
                        </small>
                        <?php endif; ?>
                        
                        <?php if(!empty($row['tailor_name']) && $row['sender_type'] != 'customer'): ?>
                        <small class="tailor-info">
                            <i class="fas fa-user-tie"></i>
                            <?php echo htmlspecialchars($row['tailor_name']); ?>
                        </small>
                        <?php endif; ?>
                    </div>
                    <div class="order-status">
                        <span class="badge <?php
                            echo $row['payment_status'] == 'completed' ? 'badge-success' :
                            ($row['payment_status'] == 'pending' ? 'badge-warning' : 'badge-danger');
                        ?>">
                            <?php echo ucfirst($row['payment_status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php
                endforeach;
            } else {
                echo '<div class="no-messages"><p>No messages found</p></div>';
            }
            ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
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

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add animations for message items
    const messageItems = document.querySelectorAll('.message-item');
    messageItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.05}s`;
        item.classList.add('animate-fade-in');
    });
    
    // Add hover effect for action buttons
    const actionButtons = document.querySelectorAll('.action-btn');
    actionButtons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Add click effect for "Mark as Read" buttons
    const markReadButtons = document.querySelectorAll('.mark-read-btn');
    markReadButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const messageItem = this.closest('.message-item');
            messageItem.classList.remove('unread');
            messageItem.classList.add('message-received');
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>