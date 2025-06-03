<?php

$con = mysqli_connect('localhost', 'root', '', 'xlsx');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // استلام بيانات المدخلات
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // استعلام للتحقق من وجود المستخدم في قاعدة البيانات
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        // الحصول على بيانات المستخدم من قاعدة البيانات
        $user = mysqli_fetch_assoc($result);

        // التحقق من كلمة المرور
        if (password_verify($password, $user['password'])) {
            // تسجيل الدخول بنجاح (هنا يمكنك تخزين معلومات الجلسة مثل ID المستخدم في الجلسة)
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            echo "تم تسجيل الدخول بنجاح!";
            header("Location: ../index.php"); // التوجيه إلى الصفحة الرئيسية بعد تسجيل الدخول
        } else {
            echo "كلمة المرور غير صحيحة!";
        }
    } else {
        echo "البريد الإلكتروني غير مسجل!";
    }
}
?>