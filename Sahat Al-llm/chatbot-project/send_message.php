<?php
require_once '../php/config.php';
require_once 'functions.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$currentUserId = 1; // يجب استبداله ب ID المستخدم الحالي

try {
    // إدخال الرسالة في قاعدة البيانات
    $stmt = $conn->prepare("
        INSERT INTO messages (conversation_id, sender_id, message) 
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iis", $data['conversation_id'], $currentUserId, $data['message']);
    $stmt->execute();
    
    // تحديث وقت آخر رسالة في المحادثة
    $stmt = $conn->prepare("
        UPDATE conversations 
        SET last_message_at = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $data['conversation_id']);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>