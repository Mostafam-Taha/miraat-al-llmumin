<?php
require_once '../php/config.php';

if (isset($_GET['session_expired'])) {
    $errors[] = "انتهت مدة الجلسة بسبب عدم النشاط. يرجى تسجيل الدخول مرة أخرى.";
}

session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // مرحلة تسجيل الدخول الأساسي
        $username = sanitizeInput($_POST['username']);
        $password = sanitizeInput($_POST['password']);
        
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            if ($admin['is_active']) {
                // إنشاء كود التحقق
                $verification_code = generateVerificationCode();
                
                $stmt = $pdo->prepare("INSERT INTO verification_codes (
                    admin_id, code, phone, expires_at
                ) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
                
                $stmt->execute([$admin['id'], $verification_code, $admin['phone']]);
                
                // إرسال الكود عبر الواتساب
                sendWhatsAppVerificationCode($admin['phone'], $verification_code);
                
                // حفظ بيانات الأدمن في الجلسة للمرحلة القادمة
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['verification_step'] = true;
                
                // تسجيل محاولة الدخول
                logLoginAttempt($admin['id'], $_SERVER['REMOTE_ADDR'], true);
            } else {
                $errors[] = "الحساب معطل. يرجى التواصل مع المسؤول.";
                logLoginAttempt($admin['id'], $_SERVER['REMOTE_ADDR'], false);
            }
        } else {
            $errors[] = "اسم المستخدم أو كلمة المرور غير صحيحة.";
            if ($admin) {
                logLoginAttempt($admin['id'], $_SERVER['REMOTE_ADDR'], false);
            }
        }
    } elseif (isset($_POST['verify'])) {
        // مرحلة التحقق من الكود
        if (!isset($_SESSION['admin_id']) || !isset($_SESSION['verification_step'])) {
            header("Location: login.php");
            exit();
        }
        
        $admin_id = $_SESSION['admin_id'];
        $code = sanitizeInput($_POST['code']);
        
        $stmt = $pdo->prepare("SELECT * FROM verification_codes 
                              WHERE admin_id = ? AND code = ? AND is_used = 0 AND expires_at > NOW()");
        $stmt->execute([$admin_id, $code]);
        $verification = $stmt->fetch();
        
        if ($verification) {
            // التحقق من أن المسؤول قد قام بتفعيل الحساب
            $stmt = $pdo->prepare("SELECT is_verified FROM admins WHERE id = ?");
            $stmt->execute([$admin_id]);
            $admin_status = $stmt->fetch();
            
            if (!$admin_status || !$admin_status['is_verified']) {
                $errors[] = "الحساب لم يتم التحقق منه بعد. يرجى التواصل مع المسؤول.";
            } else {
                // تم التحقق بنجاح
                $stmt = $pdo->prepare("UPDATE verification_codes SET is_used = 1 WHERE id = ?");
                $stmt->execute([$verification['id']]);
                
                $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$admin_id]);
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_data'] = $pdo->prepare("SELECT * FROM admins WHERE id = ?")->execute([$admin_id]);                
                unset($_SESSION['verification_step']);
                unset($_SESSION['admin_id']);
                
                if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
                    header("Location: questions_manager.php");
                    exit();
                }
            }
        } else {
            $errors[] = "كود التحقق غير صحيح أو منتهي الصلاحية.";
        }
    }
}

function logLoginAttempt($admin_id, $ip_address, $is_successful) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO login_attempts (admin_id, ip_address, is_successful) VALUES (?, ?, ?)");
    $stmt->execute([$admin_id, $ip_address, $is_successful ? 1 : 0]);
}

function generateVerificationCode() {
    return bin2hex(random_bytes(8)); // كود مكون من 16 حرف عشوائي
}

function sendWhatsAppVerificationCode($phone, $code) {
    // هذه وظيفة افتراضية، يجب استبدالها بوظيفة حقيقية لإرسال الواتساب
    file_put_contents('whatsapp.log', "إرسال كود التحقق {$code} إلى الرقم {$phone}\n", FILE_APPEND);
    return true;
}

// بعد التحقق من كود الواتساب بنجاح
$_SESSION['login_time'] = time(); // إضافة وقت تسجيل الدخول
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="منصة تعليمية متكاملة لمساعدة الطلاب على تنظيم الدراسة، وضع خطط دراسية ذكية، تحسين الأداء الأكاديمي، وحل اختبارات تفاعلية. ابدأ رحلتك نحو التفوق مع أدواتنا الذكية وتقارير الأداء المُفصّلة!">
    <meta name="keywords" content="تنظيم الدراسة, خطط دراسية, تحسين الأداء الدراسي, اختبارات تفاعلية, نصائح دراسية, جدول مذاكرة, مهارات التعلم, مراجعة الدروس, حلول امتحانات, منصة تعليمية, موارد دراسية, إدارة الوقت للطلاب, تعلم فعال, تقنيات الحفظ, التحضير للامتحانات, دروس مجانية, تمارين تدريبية, تقييم ذاتي, تعليم عن بعد, أدوات الدراسة الذكية">
    <meta name="author" content="ساحة العلم - رفيقك نحو التفوق الأكاديمي">
    <meta property="og:url" content="https://starlit-axolotl-737204.netlify.app">
    <meta property="og:image" content="https://starlit-axolotl-737204.netlify.app/image/logo/logo.png">
    <meta property="og:type" content="website">
    <meta name="twitter:image" content="https://starlit-axolotl-737204.netlify.app/image/logo/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="image/logo/book-open-reader-solid.svg" type="image/svg">
    <link rel="stylesheet" href="../css/login-sighin admin.css">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-B2Z6G6EY81"></script>
    <script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-B2Z6G6EY81');</script>
    <title>نظام الإدارة - تسجيل الدخول</title>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-K73Z87CS');</script>
    <!-- End Google Tag Manager -->
</head>
<body>
    <div class="login-container animate__animated animate__fadeInUp">
        <div class="login-header">
            <h1>مرحباً بعودتك!</h1>
            <p>من فضلك قم بتسجيل الدخول للوصول إلى لوحة التحكم</p>
        </div>
        
        <div class="login-body">
            <?php if (!empty($errors)): ?>
                <div class="error-message animate__animated animate__shakeX">
                    <?php foreach ($errors as $error): ?>
                        <p><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Step -->
            <div id="login-step" class="form-step <?php echo !isset($_SESSION['verification_step']) ? 'active' : ''; ?>">
                <form action="login.php" method="post">
                    <div class="form-group">
                        <label for="username">اسم المستخدم</label>
                        <div style="position: relative;">
                            <input type="text" id="username" name="username" class="form-control" required>
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">كلمة المرور</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-control" required>
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                    </div>
                    
                    <button type="submit" name="login" class="btn">
                        <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                    </button>
                </form>
                
                <div class="login-footer">
                    <a href="#"><i class="fas fa-question-circle"></i> نسيت كلمة المرور؟</a>
                </div>
            </div>
            
            <!-- Verification Step -->
            <div id="verification-step" class="form-step <?php echo isset($_SESSION['verification_step']) ? 'active' : ''; ?>">
                <div class="verification-info">
                    <p><i class="fas fa-mobile-alt"></i> تم إرسال كود التحقق المكون من 16 رقم إلى رقم الواتساب المسجل لدينا.</p>
                </div>
                
                <form action="login.php" method="post">
                    <div class="form-group">
                        <label for="code">كود التحقق</label>
                        <div style="position: relative;">
                            <input type="text" id="code" name="code" class="form-control" required maxlength="16" placeholder="أدخل الكود المرسل إليك">
                            <i class="fas fa-key input-icon"></i>
                        </div>
                    </div>
                    
                    <button type="submit" name="verify" class="btn btn-verify">
                        <i class="fas fa-check-circle"></i> تأكيد الكود
                    </button>
                </form>
                
                <div class="login-footer">
                    <p>لم تستلم الكود؟ <a href="#"><i class="fas fa-sync-alt"></i> إعادة إرسال</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Animation for form switching
            $('.form-step').hide();
            $('.form-step.active').show();
            
            // Add animation to form elements
            $('.form-group').each(function(index) {
                $(this).css('animation-delay', (index * 0.1) + 's');
                $(this).addClass('animate__animated animate__fadeInUp');
            });
            
            // Button hover effects
            $('.btn').hover(
                function() {
                    $(this).css('transform', 'translateY(-2px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
            
            // Focus effects for inputs
            $('.form-control').focus(function() {
                $(this).parent().find('.input-icon').css('color', '#4895ef');
            }).blur(function() {
                $(this).parent().find('.input-icon').css('color', '#adb5bd');
            });
            
            <?php if (isset($_SESSION['verification_step'])): ?>
                $('#login-step').removeClass('active');
                $('#verification-step').addClass('active').hide().fadeIn(500);
            <?php endif; ?>
        });
    </script>
</body>
</html>