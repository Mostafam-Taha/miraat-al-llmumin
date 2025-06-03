<?php
include('../php/config.php');

// الحصول على معرف المنتج المراد حذفه
$id = $_GET['id'];

// تنفيذ استعلام الحذف
$query = "DELETE FROM products WHERE id = $id";
if (mysqli_query($con, $query)) {
    echo "تم حذف المنتج بنجاح";
} else {
    echo "حدث خطأ أثناء حذف المنتج: " . mysqli_error($con);
}

// إعادة التوجيه للعودة إلى صفحة المنتجات
header("Location: products.php");
exit;
?>
