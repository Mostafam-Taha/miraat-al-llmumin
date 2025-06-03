<?php
// تضمين ملف الاتصال بقاعدة البيانات
include('config.php');

// التحقق من أن النموذج قد تم إرساله
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // الحصول على القيم المدخلة في النموذج
    $category = $_POST['Category'];  // الفئة
    $productName = $_POST['name_products']; // اسم المنتج
    $weight = $_POST['wiegth'];  // الوزن
    $description = $_POST['description'];  // الوصف
    $price = $_POST['price']; // السعر
    $status = isset($_POST['radio']) ? 'نشط' : 'غير نشط'; // حالة المنتج
    $productCode = $_POST['product_code'];  // رمز المنتج

    // التعامل مع الصورة
    if (isset($_FILES['file'])) {
        $fileName = $_FILES['file']['name'];
        $fileTmpName = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileError = $_FILES['file']['error'];
        $fileType = $_FILES['file']['type'];

        // التحقق من وجود خطأ في رفع الملف
        if ($fileError === 0) {
            // تحديد مكان حفظ الصورة
            $fileDestination = 'uploads/' . basename($fileName);
            move_uploaded_file($fileTmpName, $fileDestination);
        } else {
            echo "حدث خطأ أثناء رفع الملف.";
        }
    } else {
        $fileDestination = null;
    }

    // إدخال البيانات في قاعدة البيانات
    $sql = "INSERT INTO products (product_name, category, weight, description, price, status, product_code, product_image, created_at) 
            VALUES ('$productName', '$category', '$weight', '$description', '$price', '$status', '$productCode', '$fileDestination', NOW())";

    if (mysqli_query($con, $sql)) {
        echo "تم إضافة المنتج بنجاح!";
        header('location:../dashboard/addproduct.html');
    } else {
        echo "خطأ في إضافة المنتج: " . mysqli_error($con);
    }
}

// غلق الاتصال بقاعدة البيانات
mysqli_close($con);
?>
