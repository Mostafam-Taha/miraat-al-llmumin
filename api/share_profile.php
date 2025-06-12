<?php
require_once '../php/config.php';
require_once '../php/auth_check.php';
// session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// إنشاء أو تجديد رمز المشاركة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_share_link'])) {
    $token = bin2hex(random_bytes(16));
    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $pdo->prepare("UPDATE users SET share_token = ?, share_token_expiry = ? WHERE id = ?");
    $stmt->execute([$token, $expiry, $user_id]);
    
    $_SESSION['success'] = "تم إنشاء رابط المشاركة بنجاح";
    header("Location: share_profile.php");
    exit();
}

// إلغاء رابط المشاركة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['revoke_share_link'])) {
    $stmt = $pdo->prepare("UPDATE users SET share_token = NULL, share_token_expiry = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
    
    $_SESSION['success'] = "تم إلغاء رابط المشاركة بنجاح";
    header("Location: share_profile.php");
    exit();
}

// جلب معلومات رابط المشاركة الحالي
$stmt = $pdo->prepare("SELECT share_token, share_token_expiry FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$share_info = $stmt->fetch(PDO::FETCH_ASSOC);

$share_link = '';
if ($share_info && $share_info['share_token']) {
    $share_link = "https://sha-exam.ct.ws/api/view_profile.php?token=" . $share_info['share_token'];
}

    // جلب بيانات المستخدم الحالي (تأكد من وجود نظام تسجيل دخول)
    $userId = $_SESSION['user_id'] ?? 0; // افترض أنك تخزن id المستخدم في الجلسة

    // استعلام لجلب بيانات المستخدم بما فيها الصورة
    $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // صورة افتراضية إذا لم يكن هناك صورة
    $avatarPath = $user['avatar'] ?? '../image/logo/user-solid.svg';
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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="../image/logo/book-open-reader-solid.svg" type="image/svg">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-B2Z6G6EY81"></script>
    <script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-B2Z6G6EY81');</script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/Dark Mode.css">
    <title>مشاركة الملف الشخصي</title>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-K73Z87CS');</script>
    <!-- End Google Tag Manager -->
    <style>
        .share-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .share-box {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .share-link {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            word-break: break-all;
            margin: 15px 0;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary {
            background: #4361ee;
            color: white;
        }
        .btn-danger {
            background: #f72585;
            color: white;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #4cc9f0;
            color: white;
        }

        .header {
            direction: ltr;
        }

        .image-user img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <a href="../index.html">
                <div class="logo">
                        <svg class="logo-sahat-alllm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#183153" d="M160 96a96 96 0 1 1 192 0A96 96 0 1 1 160 96zm80 152l0 264-48.4-24.2c-20.9-10.4-43.5-17-66.8-19.3l-96-9.6C12.5 457.2 0 443.5 0 427L0 224c0-17.7 14.3-32 32-32l30.3 0c63.6 0 125.6 19.6 177.7 56zm32 264l0-264c52.1-36.4 114.1-56 177.7-56l30.3 0c17.7 0 32 14.3 32 32l0 203c0 16.4-12.5 30.2-28.8 31.8l-96 9.6c-23.2 2.3-45.9 8.9-66.8 19.3L272 512z"/></svg>
                    <h1><?php echo htmlspecialchars($current_user['username']); ?></h1>
                </div>
            </a>
                <ul>
                <li><a href="../cour-3years.php">الرئسية</a></li>
                <li><a href="../exam/top_students.php">الأوائل</a></li>
                <li><a href="PDF.html">الكتب</a></li>
                <li><a href="../exam/test.php">الإختبارات</a></li>
                <li><a href="share_profile.php">مشاركة حساب</a></li>
            </ul>
            <div class="root-linge">
                <!-- <div class="search-bar">
                    <label for="search">
                        <input type="search" name="search" id="search" placeholder="بحث">
                    </label>
                </div> -->
                <div class="image-user">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16" id="toggle-user"><path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/></svg>
                </div>
            </div>
        </nav>
    </header>
    <!-- View User -->
    <div class="view-user" id="user-box">
        <div class="container">
            <div class="username-profile">
                <div class="image-user">
                    <img src="<?php echo htmlspecialchars($avatarPath); ?>" 
                        alt="صورة المستخدم" 
                        class="user-avatar"
                        onerror="this.src='../image/logo/user-solid.svg'">
                </div>                
                <div class="br-username">
                    <h3 id="user-name"><?php echo htmlspecialchars($current_user['username']); ?></h3>
                    <p style="font-size: 12px;" id="user-email"><?php echo htmlspecialchars($current_user['email']); ?></p>
                </div>
            </div>
            <hr class="hr-username">
            <div class="option-profile">
                <ul>
                    <li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pen" viewBox="0 0 16 16"><path d="m13.498.795.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001m-.644.766a.5.5 0 0 0-.707 0L1.95 11.756l-.764 3.057 3.057-.764L14.44 3.854a.5.5 0 0 0 0-.708z"/></svg><a href="../profile.php">الملف الشخصي</a></li>
                    <li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-easel" viewBox="0 0 16 16"><path d="M8 0a.5.5 0 0 1 .473.337L9.046 2H14a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1.85l1.323 3.837a.5.5 0 1 1-.946.326L11.092 11H8.5v3a.5.5 0 0 1-1 0v-3H4.908l-1.435 4.163a.5.5 0 1 1-.946-.326L3.85 11H2a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1h4.954L7.527.337A.5.5 0 0 1 8 0M2 3v7h12V3z"/></svg><a href="../exam/test.php">الاختبارات</a></li>
                    <li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book-half" viewBox="0 0 16 16"><path d="M8.5 2.687c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/></svg><a href="#">الكتب</a></li>
                    <li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16"><path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/></svg><a href="../settings.php">الإعدادات</a></li>
                    <li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-question-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286m1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94"/></svg><a href="#">المساعدة</a></li>
                </ul>
            </div>
            <hr class="hr-username">
            <div class="fast-option">
                <ul>
                    <li>
                        <div class="dark-light">
                            <div class="line-setting-mode"></div>
                            <div class="line-setting-mode-2"></div>
                            <div style="cursor: pointer; display: inline;">Mode<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-highlights" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 0 0 8a8 8 0 0 0 16 0m-8 5v1H4.5a.5.5 0 0 0-.093.009A7 7 0 0 1 3.1 13zm0-1H2.255a7 7 0 0 1-.581-1H8zm-6.71-2a7 7 0 0 1-.22-1H8v1zM1 8q0-.51.07-1H8v1zm.29-2q.155-.519.384-1H8v1zm.965-2q.377-.54.846-1H8v1zm2.137-2A6.97 6.97 0 0 1 8 1v1z"/></svg></div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="overlay" id="overlay"></div>
    <div class="share-container" style="margin-top: 90px;">
        <h1>مشاركة الملف الشخصي</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="share-box">
            <h3>رابط المشاركة</h3>
            
            <?php if ($share_link): ?>
                <p>يمكنك مشاركة هذا الرابط مع الآخرين:</p>
                <div class="share-link"><?php echo $share_link; ?></div>
                <p>ينتهي صلاحية الرابط في: <?php echo $share_info['share_token_expiry']; ?></p>
                
                <form method="POST">
                    <button type="submit" name="revoke_share_link" class="btn btn-danger">إلغاء الرابط</button>
                </form>
            <?php else: ?>
                <p>ليس لديك رابط مشاركة نشط حالياً</p>
                <form method="POST">
                    <button type="submit" name="generate_share_link" class="btn btn-primary">إنشاء رابط مشاركة</button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="share-box">
            <h3>إعدادات الخصوصية</h3>
            <p>يمكنك التحكم في المعلومات التي تظهر عند مشاركة ملفك الشخصي:</p>
            <a href="privacy_settings.php" class="btn">تعديل إعدادات الخصوصية</a>
        </div>
    </div>
    <script src="../js/light-dark-mode.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // العناصر
        const toggleUser = document.getElementById('toggle-user');
        const userBox = document.getElementById('user-box');
        const overlay = document.getElementById('overlay');

        // وظيفة فتح وإغلاق صندوق المستخدم
        function toggleUserBox() {
            userBox.classList.toggle('show');
            overlay.classList.toggle('active');
            
            // إيقاف التمرير عند فتح الصندوق
            document.body.style.overflow = userBox.classList.contains('show') ? 'hidden' : '';
        }

        // حدث النقر على أيقونة المستخدم
        toggleUser.addEventListener('click', function(e) {
            e.stopPropagation(); // منع الانتشار لتجنب إغلاق الصندوق فور فتحه
            toggleUserBox();
        });

        // إغلاق الصندوق عند النقر خارجًا
        overlay.addEventListener('click', toggleUserBox);
        
        // إغلاق الصندوق عند النقر على أي مكان في الصفحة (اختياري)
        document.addEventListener('click', function(e) {
            if (!userBox.contains(e.target)) {
                userBox.classList.remove('show');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // منع إغلاق الصندوق عند النقر داخله
        userBox.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
</script>
</body>
</html>