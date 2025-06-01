<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الملف الشخصي</title>
</head>
<body>
    <h1>مرحباً <?php echo htmlspecialchars($current_user['username']); ?></h1>
    
    <div>
        <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($current_user['email']); ?></p>
        <p><strong>الصف الدراسي:</strong> <?php echo htmlspecialchars($current_user['student_class']); ?></p>
    </div>
    
    <a href="dashboard.php">الرئيسية</a> |
    <a href="logout.php">تسجيل الخروج</a>
</body>
</html>