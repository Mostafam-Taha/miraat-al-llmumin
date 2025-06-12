<?php
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['admin_id'])) {
    die("ليس لديك صلاحية الوصول");
}

$adminId = $_SESSION['admin_id'];
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    die("المستخدم غير موجود");
}

// تحديد المسار الافتراضي للصورة إذا لم تكن موجودة
$profileImage = $admin['profile_image'] ?: 'default-profile.jpg';
$imagePath = './uploads/profiles/' . $profileImage;

// التأكد من وجود الصورة، وإلا استخدام الصورة الافتراضية
if (!file_exists($imagePath)) {
    $imagePath = '../uploads/profiles/default-profile.jpg';
}

header('Content-Type: application/json');
echo json_encode([
    'profile_image' => $imagePath,
    'full_name' => $admin['full_name'],
    'email' => $admin['email']
]);
?>