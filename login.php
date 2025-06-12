<?php
session_start();

// إذا كان المستخدم مسجل دخول بالفعل، توجيهه للصفحة الرئيسية
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: cour-3years.php');
    exit;
}

require_once './php/config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    if (empty($username)) {
        $errors['username'] = 'اسم المستخدم مطلوب';
    }

    if (empty($password)) {
        $errors['password'] = 'كلمة المرور مطلوبة';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // تحديث وقت الدخول الأخير
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // تخزين بيانات المستخدم في الجلسة
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['student_class'] = $user['student_class'];
            $_SESSION['logged_in'] = true;
            
            // توجيه المستخدم إلى الصفحة الرئيسية
            header('Location: cour-3years.php');
            exit;
        } else {
            $errors['login'] = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - ساحة العلم</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4bb543;
            --error-color: #ff3333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            display: flex;
            max-width: 1000px;
            width: 100%;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: fadeInUp 0.8s ease;
        }
        
        .login-image {
            flex: 1;
            background: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center;
            background-size: cover;
            position: relative;
            display: none;
        }
        
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(67, 97, 238, 0.7), rgba(63, 55, 201, 0.7));
        }
        
        .login-image-content {
            position: relative;
            z-index: 1;
            color: white;
            padding: 40px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-image-content h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            animation: fadeInRight 1s ease;
        }
        
        .login-image-content p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
            animation: fadeInRight 1s ease 0.2s forwards;
            opacity: 0;
        }
        
        .login-form {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
            animation: fadeIn 0.8s ease;
        }
        
        .logo img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
        
        .logo h1 {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: 700;
        }
        
        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--dark-color);
            animation: fadeIn 0.8s ease 0.2s forwards;
            opacity: 0;
        }
        
        .form-title h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .form-title p {
            color: #666;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
            animation: fadeIn 0.8s ease 0.4s forwards;
            opacity: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
        }
        
        .form-group .input-icon {
            position: absolute;
            left: 15px;
            top: 42px;
            color: #999;
        }
        
        .error {
            color: var(--error-color);
            font-size: 0.9rem;
            margin-top: 5px;
            display: block;
            animation: shake 0.5s ease;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            animation: fadeIn 0.8s ease 0.6s forwards;
            opacity: 0;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            animation: fadeIn 0.8s ease 0.8s forwards;
            opacity: 0;
        }
        
        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .register-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        /* Responsive Design */
        @media (min-width: 768px) {
            .login-image {
                display: block;
            }
        }
        
        @media (max-width: 767px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-form {
                padding: 30px;
            }
            
            .logo img {
                width: 60px;
                height: 60px;
            }
            
            .logo h1 {
                font-size: 1.5rem;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            20%, 60% {
                transform: translateX(-5px);
            }
            40%, 80% {
                transform: translateX(5px);
            }
        }
        
        /* Floating animation for logo */
        .logo img {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <div class="login-image-content">
                <h2 class="animate__animated animate__fadeInRight">مرحباً بك في ساحة العلم</h2>
                <p class="animate__animated animate__fadeInRight animate__delay-1s">منصة تعليمية متكاملة تساعدك على تحقيق التفوق الأكاديمي</p>
            </div>
        </div>
        
        <div class="login-form">
            <div class="logo">
                <img src="./image/logo/book-open-reader-solid.svg" alt="ساحة العلم">
                <h1>ساحة العلم</h1>
            </div>
            
            <div class="form-title">
                <h2>تسجيل الدخول</h2>
                <p>أدخل بياناتك للوصول إلى حسابك</p>
            </div>
            
            <?php if (!empty($errors['login'])): ?>
                <div class="error animate__animated animate__shakeX"><?php echo $errors['login']; ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">اسم المستخدم</label>
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="username" name="username" required placeholder="أدخل اسم المستخدم">
                    <?php if (!empty($errors['username'])): ?>
                        <span class="error"><?php echo $errors['username']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" required placeholder="أدخل كلمة المرور">
                    <?php if (!empty($errors['password'])): ?>
                        <span class="error"><?php echo $errors['password']; ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn">تسجيل الدخول</button>
            </form>
            
            <div class="register-link">
                ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a>
            </div>
        </div>
    </div>
    
    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>