<?php
session_start();
require_once '../php/config.php'; // ملف الاتصال بقاعدة البيانات

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مسموحة']);
    exit;
}

if (!isset($_POST['user_id']) || !isset($_FILES['avatar'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit;
}

$userId = (int)$_POST['user_id'];
$file = $_FILES['avatar'];

// التحقق من أن الملف هو صورة
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'نوع الملف غير مسموح به']);
    exit;
}

// التحقق من حجم الملف (2MB كحد أقصى)
if ($file['size'] > 2097152) {
    echo json_encode(['success' => false, 'message' => 'حجم الملف كبير جداً (الحد الأقصى 2MB)']);
    exit;
}

// إنشاء مجلد التخزين إذا لم يكن موجوداً
$uploadDir = 'uploads/avatars/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// إنشاء اسم فريد للملف
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'user_' . $userId . '_' . time() . '.' . $extension;
$destination = $uploadDir . $filename;

// نقل الملف إلى مجلد التخزين
if (move_uploaded_file($file['tmp_name'], $destination)) {
    // تحديث قاعدة البيانات
    try {
        $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
        $stmt->execute([':avatar' => $destination, ':id' => $userId]);
        
        echo json_encode(['success' => true, 'path' => $destination]);
    } catch (PDOException $e) {
        unlink($destination); // حذف الملف إذا فشل تحديث قاعدة البيانات
        echo json_encode(['success' => false, 'message' => 'خطأ في تحديث قاعدة البيانات']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'فشل في رفع الملف']);
}