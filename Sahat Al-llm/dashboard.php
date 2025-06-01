<?php
session_start();

// إذا لم يكن المستخدم مسجل دخول، توجيهه لصفحة تسجيل الدخول
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once './php/config.php';

// جلب بيانات المستخدم من قاعدة البيانات
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0; }
        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .user-info { margin-bottom: 20px; }
        .logout { text-align: center; margin-top: 20px; }
        .logout a { color: #4CAF50; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>مرحباً <?php echo htmlspecialchars($user['username']); ?></h1>
    </div>
    
    <div class="container">
        <div class="user-info">
            <h2>معلومات الحساب</h2>
            <p><strong>اسم المستخدم:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <?php if (!empty($user['email'])): ?>
                <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <?php endif; ?>
            <?php if (!empty($user['phone'])): ?>
                <p><strong>رقم الهاتف:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
            <?php endif; ?>
            <p><strong>الصف الدراسي:</strong> <?php echo htmlspecialchars($user['student_class']); ?></p>
            <p><strong>آخر تسجيل دخول:</strong> <?php echo $user['last_login'] ? date('Y-m-d H:i:s', strtotime($user['last_login'])) : 'أول دخول'; ?></p>
            <p><strong>تاريخ التسجيل:</strong> <?php echo date('Y-m-d H:i:s', strtotime($user['registration_date'])); ?></p>
            <p><strong>معرف المستخدم (ID):</strong> <?php echo $user['id']; ?></p>
        </div>
        
        <div class="logout">
            <a href="logout.php">تسجيل الخروج</a>
        </div>
    </div>
</body>
</html>