<?php
require_once 'config.php';

// الحصول على قائمة الطلاب المتاحين للدردشة
function getAvailableStudents($currentUserId, $search = '') {
    global $pdo;
    
    $sql = "SELECT id, username, student_class, 
            (SELECT COUNT(*) FROM messages m 
             JOIN conversations c ON m.conversation_id = c.id 
             WHERE (c.user1_id = users.id OR c.user2_id = users.id) 
             AND (c.user1_id = ? OR c.user2_id = ?) > 0 AS has_chat_history
            FROM users 
            WHERE id != ?";
    
    $params = [$currentUserId, $currentUserId, $currentUserId];
    
    if(!empty($search)) {
        $sql .= " AND (username LIKE ? OR student_class LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY has_chat_history DESC, username ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// الحصول على المحادثة بين مستخدمين
function getConversation($user1Id, $user2Id) {
    global $pdo;
    
    // تحقق من وجود محادثة موجودة
    $stmt = $pdo->prepare("SELECT id FROM conversations 
                          WHERE (user1_id = ? AND user2_id = ?) 
                          OR (user1_id = ? AND user2_id = ?)");
    $stmt->execute([$user1Id, $user2Id, $user2Id, $user1Id]);
    $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($conversation) {
        return $conversation['id'];
    }
    
    // إنشاء محادثة جديدة إذا لم تكن موجودة
    $stmt = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
    $stmt->execute([$user1Id, $user2Id]);
    return $pdo->lastInsertId();
}

// الحصول على الرسائل في محادثة
function getMessages($conversationId, $limit = 100) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT m.*, u.username AS sender_name 
                          FROM messages m
                          JOIN users u ON m.sender_id = u.id
                          WHERE conversation_id = ?
                          ORDER BY sent_at DESC
                          LIMIT ?");
    $stmt->execute([$conversationId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// إرسال رسالة جديدة
function sendMessage($conversationId, $senderId, $message) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$conversationId, $senderId, $message]);
    
    // تحديث وقت تحديث المحادثة
    $stmt = $pdo->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
    $stmt->execute([$conversationId]);
    
    return $pdo->lastInsertId();
}

// تحديد الرسائل كمقروءة
function markMessagesAsRead($conversationId, $userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 
                          WHERE conversation_id = ? AND sender_id != ? AND is_read = 0");
    $stmt->execute([$conversationId, $userId]);
}
?>