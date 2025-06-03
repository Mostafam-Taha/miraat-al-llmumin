<?php
require_once '../php/config.php';
require_once 'functions.php';

header('Content-Type: application/json');

$currentUserId = 1; // يجب استبداله ب ID المستخدم الحالي
$otherUserId = $_GET['user_id'] ?? 0;

try {
    // البحث عن محادثة موجودة أو إنشاء جديدة
    $stmt = $conn->prepare("
        SELECT id FROM conversations 
        WHERE (user1_id = ? AND user2_id = ?) 
           OR (user1_id = ? AND user2_id = ?)
    ");
    $stmt->bind_param("iiii", $currentUserId, $otherUserId, $otherUserId, $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $conversation = $result->fetch_assoc();
        $conversationId = $conversation['id'];
    } else {
        // إنشاء محادثة جديدة
        $stmt = $conn->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $currentUserId, $otherUserId);
        $stmt->execute();
        $conversationId = $conn->insert_id;
    }
    
    // جلب الرسائل
    $stmt = $conn->prepare("
        SELECT sender_id, message, sent_at 
        FROM messages 
        WHERE conversation_id = ? 
        ORDER BY sent_at ASC
    ");
    $stmt->bind_param("i", $conversationId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode([
        'conversation_id' => $conversationId,
        'messages' => $messages
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>