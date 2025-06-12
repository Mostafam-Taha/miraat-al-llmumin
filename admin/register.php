<?php
require_once '../php/config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من الحقول المطلوبة
    $required_fields = [
        'username', 'password', 'confirm_password', 'full_name', 'email', 
        'phone', 'address', 'birth_date', 'gender', 'national_id'
    ];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "حقل {$field} مطلوب.";
        }
    }
    
    // التحقق من صحة البيانات
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صالح.";
    }
    
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $errors[] = "كلمة المرور غير متطابقة.";
    }
    
    if (strlen($_POST['password']) < 8) {
        $errors[] = "كلمة المرور يجب أن تكون على الأقل 8 أحرف.";
    }
    
    // التحقق من عدم وجود مستخدم بنفس البيانات
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? OR email = ? OR phone = ?");
    $stmt->execute([$_POST['username'], $_POST['email'], $_POST['phone']]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "اسم المستخدم أو البريد الإلكتروني أو رقم الهاتف موجود مسبقاً.";
    }
    
    // إذا لا يوجد أخطاء، قم بتسجيل الأدمن
    if (empty($errors)) {
    $hashed_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO admins (
            username, password, full_name, email, phone, address, 
            birth_date, gender, national_id, profile_image
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $profile_image = !empty($_FILES['profile_image']['name']) 
            ? uploadProfileImage($_FILES['profile_image']) 
            : null;
        
        $stmt->execute([
            sanitizeInput($_POST['username']),
            $hashed_password,
            sanitizeInput($_POST['full_name']),
            sanitizeInput($_POST['email']),
            sanitizeInput($_POST['phone']),
            sanitizeInput($_POST['address']),
            sanitizeInput($_POST['birth_date']),
            sanitizeInput($_POST['gender']),
            sanitizeInput($_POST['national_id']),
            $profile_image
        ]);
        
        $admin_id = $pdo->lastInsertId();
        
        // إنشاء كود التحقق
        $verification_code = generateVerificationCode();
        $phone = sanitizeInput($_POST['phone']);
        
        $stmt = $pdo->prepare("INSERT INTO verification_codes (
            admin_id, code, phone, expires_at
        ) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
        
        $stmt->execute([$admin_id, $verification_code, $phone]);
        
        $pdo->commit();
        
        // بدلاً من إرسال الكود، نعرضه للمسؤول
        $_SESSION['admin_verification_code'] = $verification_code;
        $_SESSION['admin_verification_phone'] = $phone;
        $_SESSION['admin_verification_id'] = $admin_id;
        
        header("Location: verification_success.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = "حدث خطأ أثناء التسجيل: " . $e->getMessage();
    }
}
}

function uploadProfileImage($file) {
    $target_dir = "uploads/profiles/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // التحقق من نوع الملف
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($file_extension), $allowed_types)) {
        throw new Exception("نوع الملف غير مسموح به.");
    }
    
    // التحقق من حجم الملف (2MB كحد أقصى)
    if ($file['size'] > 2097152) {
        throw new Exception("حجم الملف كبير جداً. الحد الأقصى 2MB.");
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $target_file;
    } else {
        throw new Exception("حدث خطأ أثناء رفع الملف.");
    }
}

function generateVerificationCode() {
    return bin2hex(random_bytes(8)); // كود مكون من 16 حرف عشوائي
}

function sendWhatsAppVerificationCode($phone, $code) {
    // هذه وظيفة افتراضية، يجب استبدالها بوظيفة حقيقية لإرسال الواتساب
    // مثال باستخدام Twilio:
    /*
    $twilio = new Twilio\Rest\Client($account_sid, $auth_token);
    $message = $twilio->messages
        ->create("whatsapp:+".$phone,
            array(
                "from" => "whatsapp:+14155238886",
                "body" => "كود التحقق الخاص بك هو: " . $code
            )
        );
    */
    
    // لأغراض التطوير، سنقوم فقط بتسجيل الكود في ملف log
    file_put_contents('whatsapp.log', "إرسال كود التحقق {$code} إلى الرقم {$phone}\n", FILE_APPEND);
    return true;
}
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
    <link rel="stylesheet" href="../css/register admin.css">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-B2Z6G6EY81"></script>
    <script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-B2Z6G6EY81');</script>
    <title>Sahat al-llm - Register</title>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-K73Z87CS');</script>
    <!-- End Google Tag Manager -->
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="logo">
                <i class="fas fa-user-shield"></i>
                <h1>نظام إدارة الأدمن</h1>
            </div>
            <h2>إنشاء حساب جديد</h2>
            <p>املأ النموذج أدناه لتسجيل حساب أدمن جديد</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <div class="alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="alert-content">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <div class="alert-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="alert-content">
                    <p>تم تسجيل الحساب بنجاح! سيتم إرسال كود التحقق إلى رقم الواتساب الخاص بك.</p>
                </div>
            </div>
        <?php else: ?>
            <form class="register-form" action="register.php" method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> اسم المستخدم
                        </label>
                        <input type="text" id="username" name="username" placeholder="أدخل اسم المستخدم" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> كلمة المرور
                        </label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" placeholder="كلمة المرور (8 أحرف على الأقل)" required>
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> تأكيد كلمة المرور
                        </label>
                        <div class="password-input">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="أعد إدخال كلمة المرور" required>
                            <button type="button" class="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">
                            <i class="fas fa-id-card"></i> الاسم الكامل
                        </label>
                        <input type="text" id="full_name" name="full_name" placeholder="الاسم الكامل كما في الهوية" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> البريد الإلكتروني
                        </label>
                        <input type="email" id="email" name="email" placeholder="example@domain.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone-alt"></i> رقم الهاتف
                        </label>
                        <div class="phone-input">
                            <span class="country-code">+20</span>
                            <input type="tel" id="phone" name="phone" placeholder="1XXXXXXXX" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">
                            <i class="fas fa-map-marker-alt"></i> العنوان
                        </label>
                        <textarea id="address" name="address" placeholder="العنوان بالتفصيل" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="birth_date">
                            <i class="fas fa-birthday-cake"></i> تاريخ الميلاد
                        </label>
                        <input type="date" id="birth_date" name="birth_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">
                            <i class="fas fa-venus-mars"></i> الجنس
                        </label>
                        <select id="gender" name="gender" required>
                            <option value="">اختر الجنس...</option>
                            <option value="male">ذكر</option>
                            <option value="female">أنثى</option>
                            <option value="other">أخرى</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="national_id">
                            <i class="fas fa-id-badge"></i> رقم الهوية الوطنية
                        </label>
                        <input type="text" id="national_id" name="national_id" placeholder="10 أو 12 رقم" required>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="profile_image">
                            <i class="fas fa-camera"></i> صورة الملف الشخصي (اختياري)
                        </label>
                        <div class="file-upload">
                            <label for="profile_image" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>اختر صورة</span>
                                <span class="file-name" id="file-name">لم يتم اختيار ملف</span>
                            </label>
                            <input type="file" id="profile_image" name="profile_image" accept="image/*">
                        </div>
                    </div>
                </div>
                
                <div class="form-footer">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus"></i> تسجيل الحساب
                    </button>
                    <div class="login-link">
                        لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="../js/register admin.js"></script>
</body>
</html>