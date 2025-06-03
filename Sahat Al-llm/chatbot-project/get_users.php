<?php
require_once '../php/config.php';

try {
    $currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    $stmt = $pdo->prepare("
        SELECT id, username, student_class, 
               (last_login > NOW() - INTERVAL 5 MINUTE) as is_online
        FROM users 
        WHERE id != :currentUserId
        ORDER BY is_online DESC, username ASC
    ");
    
    $stmt->bindParam(':currentUserId', $currentUserId, PDO::PARAM_INT);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'General error: ' . $e->getMessage()
    ]);
}

// تأكد من عدم وجود أي إخراج إضافي بعد هذا النقطة
exit();
?>