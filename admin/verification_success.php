<?php
require_once '../php/config.php';
session_start();

if (!isset($_SESSION['admin_verification_code'])) {
    header("Location: questions_manager.php");
    exit();
}

$code = $_SESSION['admin_verification_code'];
$phone = $_SESSION['admin_verification_phone'];
$admin_id = $_SESSION['admin_verification_id'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انتظار التحقق</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .verification-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .code-display {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            padding: 15px;
            background-color: #333;
            color: #fff;
            border-radius: 5px;
            margin: 20px 0;
        }
        .instructions {
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>انتظار التحقق</h1>
        
        <div class="verification-info">
            <p>تم إنشاء حساب الأدمن بنجاح وجاري انتظار التحقق.</p>
            <p>يرجى التواصل مع المسؤول عبر الواتساب على الرقم التالي لإكمال عملية التحقق:</p>
            <p style="text-align: center; font-size: 20px; margin: 15px 0;"><?php echo htmlspecialchars($phone); ?></p>
        </div>
        
        <div class="instructions">
            <h3>تعليمات للمسؤول:</h3>
            <ol>
                <li>سيتواصل معك المستخدم عبر الواتساب على الرقم أعلاه</li>
                <li>كود التحقق الخاص بهذا المستخدم هو:</li>
            </ol>
            
            <div class="code-display">
                <?php echo htmlspecialchars($code); ?>
            </div>
            
            <ol start="3">
                <li>قم بإعطاء المستخدم هذا الكود عبر الواتساب</li>
                <li>بعد إعطاء الكود، يمكنك الضغط على الزر أدناه لمتابعة المستخدم</li>
            </ol>
        </div>
        
        <div style="text-align: center;">
            <a href="verify_admin.php?admin_id=<?php echo $admin_id; ?>" class="btn">تم التحقق - متابعة المستخدم</a>
        </div>
    </div>
</body>
</html>