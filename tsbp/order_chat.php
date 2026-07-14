<?php
session_start();
require_once 'includes/config.php';
include 'includes/header.php';

// Redirect if user is not logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

// Get user ID and order ID
$userId = $_SESSION['user_id'];
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Validate that this order belongs to the current user
$stmt = $pdo->prepare("
    SELECT o.*, 
           t.fullname as tailor_name,
           t.tailor_id,
           ota.status as assignment_status
    FROM orders o
    LEFT JOIN order_tailor_assignments ota ON o.id = ota.order_id
    LEFT JOIN tailors t ON ota.tailor_id = t.tailor_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<script>window.location.href = 'orders.php';</script>";
    exit;
}

// Mark customer's messages as read
$stmt = $pdo->prepare("
    UPDATE chat_messages 
    SET read_status = 1 
    WHERE order_id = ? 
    AND sender_type != 'customer'
");
$stmt->execute([$orderId]);

// Get chat history
$stmt = $pdo->prepare("
    SELECT cm.*, 
           CASE 
               WHEN cm.sender_type = 'customer' THEN c.first_name
               WHEN cm.sender_type = 'tailor' THEN t.fullname
               WHEN cm.sender_type = 'admin' THEN a.fullname
           END as sender_name
    FROM chat_messages cm
    LEFT JOIN customers c ON cm.sender_type = 'customer' AND cm.sender_id = c.id
    LEFT JOIN tailors t ON cm.sender_type = 'tailor' AND cm.sender_id = t.tailor_id
    LEFT JOIN admins a ON cm.sender_type = 'admin' AND cm.sender_id = a.admin_id
    WHERE cm.order_id = ?
    ORDER BY cm.created_at ASC
");
$stmt->execute([$orderId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to format date
function formatMessageDate($dateString) {
    $messageDate = new DateTime($dateString);
    $now = new DateTime();
    $diff = $now->diff($messageDate);
    
    if ($diff->days == 0) {
        // Today - show time only
        return $messageDate->format('h:i A');
    } elseif ($diff->days == 1) {
        // Yesterday
        return 'Yesterday at ' . $messageDate->format('h:i A');
    } else {
        // Other days
        return $messageDate->format('M d, Y, h:i A');
    }
}

// Send new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $hasAttachment = false;
    $attachmentPath = null;
    
    // Check if message is not empty
    if (!empty($message) || isset($_FILES['attachment']['name']) && !empty($_FILES['attachment']['name'])) {
        
        // Handle file upload if present
        if (isset($_FILES['attachment']['name']) && !empty($_FILES['attachment']['name'])) {
            $uploadDir = 'uploads/chat/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['attachment']['name']);
            $targetFilePath = $uploadDir . $fileName;
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            
            // Allow certain file formats
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'pdf', 'doc', 'docx');
            if (in_array(strtolower($fileType), $allowTypes)) {
                // Upload file to server
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFilePath)) {
                    $attachmentPath = $fileName;
                    $hasAttachment = true;
                }
            }
        }
        
        // Insert message into database
        $stmt = $pdo->prepare("
            INSERT INTO chat_messages (order_id, sender_type, sender_id, message, attachment, created_at)
            VALUES (?, 'customer', ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $orderId,
            $userId,
            $message,
            $attachmentPath
        ]);
        
        // Redirect to prevent form resubmission
       // header("Location: order_chat.php?order_id=$orderId");
        echo "<script>window.location.href = 'order_chat.php?order_id=$orderId';</script>";

        exit;
    }
}
?>

<style>  
/* Order Chat Styles - based on system style variables */

/* Page Container */
.page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.5rem;
    background-color: var(--gray-50);
}

/* Back Button */
.back-button {
    display: inline-flex;
    align-items: center;
    color: var(--text-dark);
    margin-bottom: 1.5rem;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    padding: 0.6rem 1rem;
    border-radius: 50px;
    background-color: white;
    box-shadow: var(--shadow-sm);
}

.back-button:hover {
    color: var(--primary);
    transform: translateX(-5px);
    box-shadow: var(--shadow-md);
}

/* Chat Container */
.chat-container {
    display: flex;
    flex-direction: column;
    height: 75vh;
    border-radius: 15px;
    overflow: hidden;
    background-color: white;
    box-shadow: var(--shadow-lg);
    border: 3px solid var(--gray-100);
}

/* Chat Header */
.chat-header {
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, var(--primary-light), var(--primary));
    color: var(--text-light);
    border-bottom: 1px solid var(--primary-dark);
    position: relative;
}

.chat-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
}

.chat-with {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.9);
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
}

.chat-with span {
    font-weight: 600;
    margin-left: 0.3rem;
}

/* Badge Styles */
.badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-left: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: var(--shadow-sm);
}

.badge.assigned {
    background-color: var(--primary-light);
    color: white;
}

.badge.in_progress {
    background-color: var(--secondary);
    color: var(--text-dark);
}

.badge.completed {
    background-color: var(--success);
    color: white;
}

.badge.cancelled {
    background-color: var(--red-500);
    color: white;
}

/* Chat Messages Container */
.chat-messages-container {
    flex: 1;
    overflow-y: auto;
    padding: .5rem;
    background-color: var(--gray-50);
    scroll-behavior: smooth;
}

.chat-messages {
    display: flex;
    flex-direction: column;
}

/* Message Styles */
.message {
    max-width: 75%;
    margin-bottom: 1.25rem;
    position: relative;
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message-sent {
    align-self: flex-end;
}

.message-received {
    align-self: flex-start;
}

.message-content {
    padding: 0.8rem 0.2rem;
    border-radius: 18px;
    position: relative;
    word-wrap: break-word;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
}

.message-content:hover {
    box-shadow: var(--shadow-md);
}

.message-sent .message-content {
    background-color: var(--primary);
    color: white;
    border-bottom-right-radius: 5px;
}

.message-received .message-content {
    background-color: white;
    color: var(--text-dark);
    border-bottom-left-radius: 5px;
}

.message-text {
    margin-bottom: 0;
    line-height: 1.5;
    font-size: 0.95rem;
}

.message-meta {
    display: flex;
    font-size: 0.75rem;
    margin-top: 0.4rem;
    opacity: 0.8;
}

.message-sent .message-meta {
    justify-content: flex-end;
}

.message-time {
    color: var(--gray-800);
}

.message-sent .message-time {
    color: var(--gray-300);
}

.message-sender {
    color: var(--primary-dark);
    margin-left: 0.5rem;
    font-weight: 600;
}

/* Date Separator */
.date-separator {
    text-align: center;
    margin: 2rem 0 1.5rem;
    position: relative;
}

.date-separator:before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    height: 1px;
    background-color: var(--gray-200);
    z-index: 1;
}

.date-separator span {
    background-color: var(--gray-50);
    padding: 0.4rem 1rem;
    font-size: 0.8rem;
    color: var(--gray-800);
    position: relative;
    z-index: 2;
    border-radius: 15px;
    font-weight: 600;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-100);
}

/* Chat Input Container */
.chat-input-container {
    padding: 1.25rem;
    border-top: 1px solid var(--gray-100);
    background-color: white;
}

.chat-input-wrapper {
    display: flex;
    align-items: flex-end;
    position: relative;
}

/* Attachment Styling */
.chat-attachment {
    margin-right: 0.8rem;
    position: relative;
}

.attachment-label {
    cursor: pointer;
    color: var(--gray-300);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    transition: var(--transition);
    background-color: var(--gray-50);
}

.attachment-label:hover {
    background-color: var(--gray-100);
    color: var(--primary);
    transform: scale(1.05);
}

.attachment-input {
    display: none;
}

/* Message Input */
.chat-input {
    flex: 1;
    padding: 0.9rem 1.2rem;
    border: 2px solid var(--gray-100);
    border-radius: 24px;
    resize: none;
    max-height: 120px;
    min-height: 45px;
    outline: none;
    font-family: inherit;
    font-size: 0.95rem;
    line-height: 1.5;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
}

.chat-input:focus {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(94, 53, 177, 0.1);
}

/* Send Button */
.chat-send-btn {
    background-color: var(--primary);
    color: white;
    border: none;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    margin-left: 0.8rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
}

.chat-send-btn:hover {
    background-color: var(--primary-dark);
    transform: scale(1.05);
    box-shadow: var(--shadow-md);
}

/* Welcome Message */
.chat-welcome {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--gray-800);
    background-color: white;
    border-radius: 15px;
    box-shadow: var(--shadow-sm);
    margin: 2rem auto;
    max-width: 500px;
}

.welcome-icon {
    font-size: 3.5rem;
    color: var(--primary-light);
    margin-bottom: 1.5rem;
    background-color: var(--gray-50);
    width: 100px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    margin: 0 auto 1.5rem;
}

.chat-welcome h3 {
    margin-bottom: 1rem;
    font-size: 1.5rem;
    color: var(--primary-dark);
    font-weight: 700;
}

.chat-welcome p {
    color: var(--gray-800);
    font-size: 1rem;
}

/* Attachment Preview */
.attachment-preview {
    display: none;
    position: relative;
    padding: 0.5rem;
    margin-top: 0.8rem;
    background-color: var(--gray-50);
    border-radius: 10px;
}

.attachment-preview img {
    max-width: 100px;
    max-height: 100px;
    border-radius: 8px;
    box-shadow: var(--shadow-sm);
}

.remove-attachment {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: var(--red-500);
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
}

.remove-attachment:hover {
    transform: scale(1.1);
}

.message-attachment {
    margin-top: 0.8rem;
}

.attachment-preview.message-image {
    max-width: 200px;
    max-height: 150px;
    border-radius: 10px;
    box-shadow: var(--shadow-md);
    transition: transform 0.3s ease;
}

.attachment-preview.message-image:hover {
    transform: scale(1.03);
}

.file-attachment {
    display: flex;
    align-items: center;
    padding: 0.8rem 1rem;
    background-color: var(--gray-100);
    border-radius: 8px;
    text-decoration: none;
    color: var(--primary-dark);
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    font-weight: 500;
}

.file-attachment:hover {
    background-color: var(--gray-200);
    box-shadow: var(--shadow-md);
}

.file-attachment i {
    margin-right: 0.8rem;
    color: var(--primary);
    font-size: 1.1rem;
}

/* File Preview Styling */
.file-preview {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background: var(--gray-100);
    border-radius: 8px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 200px;
}

.file-preview i {
    margin-right: 8px;
    color: var(--primary);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .page-container {
        padding: 1rem;
    }
    
    .chat-container {
        height: 90vh;
    }
    
    .message {
        max-width: 85%;
    }
    
    .chat-header {
        padding: 0.5rem;
    }
    
    .chat-input-container {
        padding: 1rem;
    }
    
    .welcome-icon {
        width: 80px;
        height: 80px;
        font-size: 2.5rem;
    }
}

@media (max-width: 576px) {
    .chat-welcome {
        padding: 2rem 1rem;
    }
    
    .welcome-icon {
        width: 60px;
        height: 60px;
        font-size: 2rem;
    }
    
    .chat-with {
        flex-wrap: wrap;
    }
    
    .badge {
        margin-left: 0;
        margin-top: 0.5rem;
    }
}

/* Animation for new messages */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message:last-child {
    animation: slideIn 0.3s ease-out;
}

/* Image lightbox effect */
.message-attachment img:active {
    cursor: zoom-in;
}

/* Additional styles to ensure image visibility */
.message-image,
img.attachment-preview {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    max-width: 200px !important;
    border-radius: 8px !important;
}

/* Scroll bar styling */
.chat-messages-container::-webkit-scrollbar {
    width: 8px;
}

.chat-messages-container::-webkit-scrollbar-track {
    background: var(--gray-50);
}

.chat-messages-container::-webkit-scrollbar-thumb {
    background-color: var(--gray-300);
    border-radius: 20px;
}

.chat-messages-container::-webkit-scrollbar-thumb:hover {
    background-color: var(--primary-light);
}  </style>
<style>
/* Add these styles to the bottom of your existing CSS */

/* For the attachment preview in the message input area */
.attachment-input-preview {
    display: none;
    position: relative;
    margin-top: 10px;
}

.attachment-input-preview img {
    max-width: 100px;
    max-height: 100px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

/* For images in chat messages */
.message-attachment {
    margin-top: 5px;
    max-width: 100%;
}

.message-image {
    display: block !important;
    max-width: 200px !important;
    max-height: 150px !important;
    border-radius: 8px !important;
    margin-top: 5px !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Fix for the conflicting attachment-preview classes */
.attachment-preview.message-image {
    display: block !important;
}

/* Make sure images are visible in all cases */
img.attachment-preview {
    display: block !important;
}
</style>


<div class="page-container">
    <a href="orders.php?id=<?php echo $orderId; ?>" class="back-button">
        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
        Back to Order Details
    </a>
    
    <div class="chat-container">
        <div class="chat-header">
            <h2>Order #<?php echo $orderId; ?> Chat</h2>
            <?php if ($order['tailor_name']): ?>
            <div class="chat-with">
                Chatting with: <span><?php echo htmlspecialchars($order['tailor_name']); ?></span>
                <span class="badge <?php echo $order['assignment_status']; ?>">
                    <?php echo ucwords(str_replace('_', ' ', $order['assignment_status'])); ?>
                </span>
            </div>
            <?php else: ?>
            <div class="chat-with">
                <span class="text-muted">No tailor assigned yet</span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="chat-messages-container">
            <div class="chat-messages" id="chatMessages">
                <?php if (empty($messages)): ?>
                <div class="chat-welcome">
                    <div class="welcome-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Start a conversation</h3>
                    <p>Send a message to communicate about your order.</p>
                </div>
                <?php else: ?>
                
                <?php 
                $currentDate = '';
                foreach ($messages as $message): 
                    $messageDate = date('Y-m-d', strtotime($message['created_at']));
                    
                    // Add date separator if this is a new day
                    if ($currentDate != $messageDate) {
                        $currentDate = $messageDate;
                        $dateDisplay = '';
                        
                        $today = date('Y-m-d');
                        $yesterday = date('Y-m-d', strtotime('-1 day'));
                        
                        if ($messageDate == $today) {
                            $dateDisplay = 'Today';
                        } elseif ($messageDate == $yesterday) {
                            $dateDisplay = 'Yesterday';
                        } else {
                            $dateDisplay = date('F j, Y', strtotime($messageDate));
                        }
                ?>
                    <div class="date-separator">
                        <span><?php echo $dateDisplay; ?></span>
                    </div>
                <?php } ?>
                
                <div class="message <?php echo ($message['sender_type'] == 'customer') ? 'message-sent' : 'message-received'; ?>">
                    <div class="message-content">
                        <?php if (!empty($message['message'])): ?>
                            <div class="message-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($message['attachment'])): ?>
                            <div class="message-attachment">
                                <?php
                                $ext = pathinfo($message['attachment'], PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']);
                                
                                if ($isImage):
                                ?>
                                    <a href="uploads/chat/<?php echo $message['attachment']; ?>" target="_blank">
                                        <img src="uploads/chat/<?php echo $message['attachment']; ?>" alt="Attachment" class="attachment-preview">
                                    </a>
                                <?php else: ?>
                                    <a href="uploads/chat/<?php echo $message['attachment']; ?>" target="_blank" class="file-attachment">
                                        <i class="fas fa-file"></i>
                                        <span>Download Attachment</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="message-meta">
                        <span class="message-time"><?php echo formatMessageDate($message['created_at']); ?></span>
                        <?php if ($message['sender_type'] != 'customer'): ?>
                            <span class="message-sender"><?php echo htmlspecialchars($message['sender_name']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="chat-input-container">
            <form action="order_chat.php?order_id=<?php echo $orderId; ?>" method="post" enctype="multipart/form-data" id="chatForm">
                <div class="chat-input-wrapper">
                    <div class="chat-attachment">
                        <label for="attachment" class="attachment-label">
                            <i class="fas fa-paperclip"></i>
                        </label>
                        <input type="file" id="attachment" name="attachment" class="attachment-input">
                        <div class="attachment-preview" id="attachmentPreview"></div>
                    </div>
                    <textarea name="message" id="messageInput" placeholder="Type your message..." class="chat-input"></textarea>
                    <button type="submit" class="chat-send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll to bottom of chat on load
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Handle attachment preview with enhanced UI
    const attachment = document.getElementById('attachment');
    const attachmentPreview = document.getElementById('attachmentPreview');
    
    attachment.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const reader = new FileReader();
            
            attachmentPreview.innerHTML = '';
            attachmentPreview.style.display = 'block';
            
            if (file.type.match('image.*')) {
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
                    const removeBtn = document.createElement('div');
                    removeBtn.className = 'remove-attachment';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        attachment.value = '';
                        attachmentPreview.style.display = 'none';
                    });
                    
                    attachmentPreview.appendChild(img);
                    attachmentPreview.appendChild(removeBtn);
                }
                reader.readAsDataURL(file);
            } else {
                const fileExt = file.name.split('.').pop().toLowerCase();
                let iconClass = 'fa-file';
                
                // Choose appropriate icon based on file type
                if (['pdf'].includes(fileExt)) iconClass = 'fa-file-pdf';
                else if (['doc', 'docx'].includes(fileExt)) iconClass = 'fa-file-word';
                else if (['xls', 'xlsx'].includes(fileExt)) iconClass = 'fa-file-excel';
                else if (['zip', 'rar'].includes(fileExt)) iconClass = 'fa-file-archive';
                
                const filePreview = document.createElement('div');
                filePreview.className = 'file-preview';
                filePreview.innerHTML = `<i class="fas ${iconClass}"></i> ${file.name}`;
                
                const removeBtn = document.createElement('div');
                removeBtn.className = 'remove-attachment';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    attachment.value = '';
                    attachmentPreview.style.display = 'none';
                });
                
                attachmentPreview.appendChild(filePreview);
                attachmentPreview.appendChild(removeBtn);
            }
        }
    });
    
    // Auto-resize textarea as user types
    const messageInput = document.getElementById('messageInput');
    
    // Set initial height
    setTimeout(() => {
        messageInput.style.height = 'auto';
        messageInput.style.height = (messageInput.scrollHeight) + 'px';
    }, 0);
    
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    // Form validation with visual feedback
    const chatForm = document.getElementById('chatForm');
    chatForm.addEventListener('submit', function(e) {
        const message = messageInput.value.trim();
        const hasAttachment = attachment.files.length > 0;
        
        if (!message && !hasAttachment) {
            e.preventDefault();
            messageInput.classList.add('invalid');
            messageInput.focus();
            
            // Remove invalid class after animation
            setTimeout(() => {
                messageInput.classList.remove('invalid');
            }, 500);
        }
    });
    
    // Add support for Ctrl+Enter to submit
    messageInput.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            const message = messageInput.value.trim();
            const hasAttachment = attachment.files.length > 0;
            
            if (message || hasAttachment) {
                chatForm.submit();
            }
        }
    });
    
    // Image gallery lightbox effect for attachment previews
    document.querySelectorAll('.attachment-preview.message-image').forEach(img => {
        img.addEventListener('click', function() {
            // You could implement a lightbox here if needed
            this.classList.toggle('expanded');
        });
    });
    
    // Initialize cart count if cart functionality exists
    if (typeof cart !== 'undefined' && typeof cart.updateCartCount === 'function') {
        cart.updateCartCount();
    }
    
    // Add text badge animation
    const badges = document.querySelectorAll('.badge');
    badges.forEach(badge => {
        badge.classList.add('animate');
        setTimeout(() => {
            badge.classList.remove('animate');
        }, 500);
    });
});</script>

<?php include 'includes/footer.php'; ?>