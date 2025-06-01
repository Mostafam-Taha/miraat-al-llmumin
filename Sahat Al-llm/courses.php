<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الدورات التعليمية</title>
</head>
<body>
    <h1>دورات الصف <?php echo htmlspecialchars($current_user['student_class']); ?></h1>
    
    <div>
        <h2>مرحباً <?php echo htmlspecialchars($current_user['username']); ?></h2>
        <p>هذه الدورات المتاحة لصفك:</p>
        <!-- محتوى الدورات هنا -->
    </div>
    
    <a href="dashboard.php">الرئيسية</a> |
    <a href="profile.php">الملف الشخصي</a> |
    <a href="logout.php">تسجيل الخروج</a>
</body>
</html>