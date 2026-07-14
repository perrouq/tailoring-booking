<?php
/**
 * Function to get unread message count for a user
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @return int Number of unread messages
 */
function getUnreadMessageCount($pdo, $userId) {
    try {
        // Query to count unread messages where the user is not the sender
        $query = "SELECT COUNT(*) as count 
                  FROM chat_messages cm
                  INNER JOIN orders o ON cm.order_id = o.id
                  WHERE o.user_id = ? 
                  AND cm.read_status = 0 
                  AND cm.sender_type != 'customer'";
                  
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] ?? 0;
    } catch (PDOException $e) {
        // Log error instead of displaying to user
        error_log("Error counting unread messages: " . $e->getMessage());
        return 0;
    }
}