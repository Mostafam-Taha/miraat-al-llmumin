<?php
// تضمين ملف الاتصال
include_once 'config.php';

// التحقق من أن الطلب من نوع POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // الحصول على البيانات من النموذج
    $itemName = $_POST['item_name'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $totalPrice = $_POST['totalPrice'] ?? 0.00;
    $totalAmount = $_POST['totalAmount'] ?? 0.00;

    // التحقق من البيانات المدخلة
    if (!empty($itemName) && is_numeric($quantity) && is_numeric($totalPrice) && is_numeric($totalAmount)) {
        // استعلام إدخال البيانات
        $stmt = $con->prepare("INSERT INTO cart (item_name, quantity, total_price, total_amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sidd", $itemName, $quantity, $totalPrice, $totalAmount);

        if ($stmt->execute()) {
            // إعادة التوجيه عند النجاح
            header("Location: success.php");
            exit;
        } else {
            echo "خطأ في الحفظ: " . $stmt->error;
        }

        // إغلاق الاستعلام
        $stmt->close();
    } else {
        echo "يرجى التأكد من صحة البيانات المدخلة.";
    }
}

// إغلاق الاتصال بقاعدة البيانات
mysqli_close($con);
?>
