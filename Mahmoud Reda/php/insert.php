<?php
// تضمين الاتصال بقاعدة البيانات
include('config.php');

// التحقق من البيانات المدخلة
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // الحصول على القيم المدخلة من النموذج
    $first_name = mysqli_real_escape_string($con, $_POST['first']);
    $last_name = mysqli_real_escape_string($con, $_POST['last']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // تشفير كلمة المرور
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // إدخال البيانات في قاعدة البيانات
    $query = "INSERT INTO users (first_name, last_name, email, password) VALUES ('$first_name', '$last_name', '$email', '$hashed_password')";

    if (mysqli_query($con, $query)) {
        echo "تم التسجيل بنجاح!";
    } else {
        echo "خطأ في التسجيل: " . mysqli_error($con);
    }
}
header('location: ../signin.html');
// إغلاق الاتصال بقاعدة البيانات
mysqli_close($con);
?>