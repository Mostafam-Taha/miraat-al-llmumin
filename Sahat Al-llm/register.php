<?php
require_once './php/config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من البيانات
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $student_class = sanitizeInput($_POST['student_class']);

    // التحقق من اسم المستخدم
    if (empty($username)) {
        $errors['username'] = 'اسم المستخدم مطلوب';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $errors['username'] = 'اسم المستخدم موجود بالفعل';
        }
    }

    // التحقق من البريد الإلكتروني أو رقم الهاتف
    if (empty($email) && empty($phone)) {
        $errors['contact'] = 'يجب إدخال البريد الإلكتروني أو رقم الهاتف';
    } else {
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'البريد الإلكتروني غير صالح';
        }
        
        if (!empty($phone)) {
            if (!preg_match('/^\+20\d{10}$/', $phone)) {
                $errors['phone'] = 'يجب أن يبدأ رقم الهاتف بـ +20 ويتبعه 10 أرقام';
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
                $stmt->execute([$phone]);
                if ($stmt->rowCount() > 0) {
                    $errors['phone'] = 'رقم الهاتف مسجل بالفعل';
                }
            }
        }
    }

    // التحقق من كلمة المرور
    if (strlen($password) < 5) {
        $errors['password'] = 'كلمة المرور يجب أن تكون أكثر من 4 أحرف';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'كلمة المرور غير متطابقة';
    }

    // إذا لم تكن هناك أخطاء، قم بتسجيل المستخدم
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password, student_class) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $phone, $hashed_password, $student_class])) {
            $success = true;
        } else {
            $errors['database'] = 'حدث خطأ أثناء التسجيل، يرجى المحاولة لاحقًا';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .container { max-width: 500px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .error { color: red; font-size: 0.8em; margin-top: 5px; }
        .success { color: green; text-align: center; margin-bottom: 15px; }
        button { width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .login-link { text-align: center; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>إنشاء حساب جديد</h2>
        
        <?php if ($success): ?>
            <div class="success">تم إنشاء الحساب بنجاح! يمكنك الآن <a href="login.php">تسجيل الدخول</a>.</div>
        <?php else: ?>
            <?php if (!empty($errors['database'])): ?>
                <div class="error"><?php echo $errors['database']; ?></div>
            <?php endif; ?>
            
            <form action="register.php" method="post">
                <div class="form-group">
                    <label for="username">اسم المستخدم</label>
                    <input type="text" id="username" name="username" required>
                    <?php if (!empty($errors['username'])): ?>
                        <div class="error"><?php echo $errors['username']; ?></div>
                    <?php endif; ?>
                </div>
                    
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>">
                    <?php if (!empty($errors['email'])): ?>
                        <div class="error"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="phone">رقم الهاتف (مصر +20)</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>" placeholder="+20xxxxxxxxxx">
                    <?php if (!empty($errors['phone'])): ?>
                        <div class="error"><?php echo $errors['phone']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">كلمة المرور</label>
                    <input type="password" id="password" name="password" required>
                    <?php if (!empty($errors['password'])): ?>
                        <div class="error"><?php echo $errors['password']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <div class="error"><?php echo $errors['confirm_password']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="student_class">الصف الدراسي</label>
                    <select id="student_class" name="student_class" required>
                        <option value="">اختر الصف</option>
                        <option value="الصف الثالث الثانوي">الصف الثالث الثانوي</option>
                    </select>
                </div>
                
                <?php if (!empty($errors['contact'])): ?>
                    <div class="error"><?php echo $errors['contact']; ?></div>
                <?php endif; ?>
                
                <button type="submit">إنشاء الحساب</button>
            </form>
            
            <div class="login-link">
                لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>