<?php
session_start();
require_once './php/config.php';
require_once './php/auth.php';

checkAuth();

$user_id = $_SESSION['user_id'];

// استرجاع إعدادات الخصوصية الحالية
$stmt = $pdo->prepare("SELECT show_rank, show_scores, show_tests, show_class, show_avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$privacySettings = $stmt->fetch();

if (!$privacySettings) {
    die("User not found");
}

// معالجة تحديث الإعدادات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $show_rank = isset($_POST['show_rank']) ? 1 : 0;
    $show_scores = isset($_POST['show_scores']) ? 1 : 0;
    $show_tests = isset($_POST['show_tests']) ? 1 : 0;
    $show_class = isset($_POST['show_class']) ? 1 : 0;
    $show_avatar = isset($_POST['show_avatar']) ? 1 : 0;
    
    $updateStmt = $pdo->prepare("UPDATE users SET 
                show_rank = ?, 
                show_scores = ?, 
                show_tests = ?, 
                show_class = ?, 
                show_avatar = ? 
                WHERE id = ?");
    
    $updateStmt->execute([$show_rank, $show_scores, $show_tests, $show_class, $show_avatar, $user_id]);
    
    $_SESSION['success_message'] = "تم تحديث إعدادات الخصوصية بنجاح";
    header("Location: settings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعدادات الخصوصية</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --border-radius: 12px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: var(--dark-color);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .back-button {
            position: absolute;
            right: 20px;
            top: 20px;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .back-button:hover {
            transform: translateX(-5px);
        }

        .content {
            padding: 30px;
        }

        .success-message {
            background-color: var(--success-color);
            color: white;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            text-align: center;
            display: <?php echo isset($_SESSION['success_message']) ? 'block' : 'none'; ?>;
        }

        .privacy-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .privacy-section {
            background-color: var(--light-color);
            padding: 20px;
            border-radius: var(--border-radius);
        }

        .section-title {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--dark-color);
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .privacy-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .privacy-option:last-child {
            border-bottom: none;
        }

        .option-label {
            font-size: 1.1rem;
            flex: 1;
        }

        .option-description {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary-color);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .submit-btn {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 15px;
            border-radius: var(--border-radius);
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
        }

        .submit-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .container {
                width: 100%;
                border-radius: 0;
            }
            
            .header {
                padding: 20px 15px;
            }
            
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="profile.php" class="back-button">
                <i class="fas fa-arrow-right"></i> العودة للصفحة الشخصية
            </a>
            <h1>إعدادات الخصوصية</h1>
        </div>
        
        <div class="content">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message">
                    <?php echo $_SESSION['success_message']; ?>
                    <?php unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            
            <form class="privacy-form" method="POST">
                <div class="privacy-section">
                    <h3 class="section-title">إعدادات الملف الشخصي</h3>
                    
                    <div class="privacy-option">
                        <div>
                            <div class="option-label">إظهار الصورة الشخصية</div>
                            <div class="option-description">سيتمكن الآخرون من رؤية صورتك الشخصية</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_avatar" <?php echo $privacySettings['show_avatar'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="privacy-option">
                        <div>
                            <div class="option-label">إظهار الصف الدراسي</div>
                            <div class="option-description">سيتمكن الآخرون من رؤية صفك الدراسي</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_class" <?php echo $privacySettings['show_class'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="privacy-section">
                    <h3 class="section-title">إعدادات النتائج</h3>
                    
                    <div class="privacy-option">
                        <div>
                            <div class="option-label">إظهار الترتيب</div>
                            <div class="option-description">سيتمكن الآخرون من رؤية ترتيبك بين الطلاب</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_rank" <?php echo $privacySettings['show_rank'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="privacy-option">
                        <div>
                            <div class="option-label">إظهار النقاط الكلية</div>
                            <div class="option-description">سيتمكن الآخرون من رؤية مجموع نقاطك</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_scores" <?php echo $privacySettings['show_scores'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="privacy-option">
                        <div>
                            <div class="option-label">إظهار نتائج الاختبارات</div>
                            <div class="option-description">سيتمكن الآخرون من رؤية تفاصيل اختباراتك</div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_tests" <?php echo $privacySettings['show_tests'] ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">حفظ التغييرات</button>
            </form>
        </div>
    </div>
</body>
</html>