<?php
session_start();
require_once '../php/config.php';

// التحقق من صلاحيات المستخدم
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// استعلام للحصول على الأسئلة
try {
    $stmt = $pdo->query("SELECT * FROM questions");
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تعيين رأس JSON
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="questions_export_'.date('Y-m-d').'.json"');
    
    // تصدير البيانات كملف JSON
    echo json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
    
} catch (PDOException $e) {
    die("Error exporting questions: " . $e->getMessage());
}
?>