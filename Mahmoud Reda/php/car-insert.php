<?php
// تضمين الاتصال بقاعدة البيانات
include('config.php');

// التحقق من تسجيل الدخول أولاً
session_start();
if (!isset($_SESSION['user_id'])) {
    die("يجب عليك تسجيل الدخول أولاً.");
}

// التحقق من البيانات المدخلة للطلب
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // الحصول على بيانات المنتج من النموذج
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $product_image = mysqli_real_escape_string($con, $_POST['product_image']);
    $quantity = intval($_POST['quantity']);
    $total_price = floatval($_POST['total_price']);

    // الحصول على معرف المستخدم من الجلسة
    $user_id = $_SESSION['user_id'];

    // إدخال بيانات الطلب في قاعدة البيانات
    $query = "INSERT INTO orders (user_id, product_name, product_image, quantity, total_price) 
              VALUES ('$user_id', '$product_name', '$product_image', '$quantity', '$total_price')";

    if (mysqli_query($con, $query)) {
        echo "تم إضافة الطلب بنجاح!";
    } else {
        echo "خطأ في إضافة الطلب: " . mysqli_error($con);
    }
}
mysqli_close($con);
?>
