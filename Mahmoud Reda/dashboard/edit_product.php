<?php
include('../php/config.php');

// جلب بيانات المنتج
$id = $_GET['id'];
$query = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($con, $query);
$product = mysqli_fetch_assoc($result);

// معالجة طلب التعديل
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['product_name'];
    $category = $_POST['category'];
    $status = $_POST['status'];
    $price = $_POST['price'];

    // تحديث الصورة في حالة رفع صورة جديدة
    if (!empty($_FILES['product_image']['name'])) {
        // تعيين اسم الصورة الجديدة
        $image = $_FILES['product_image']['name'];

        // تحديد المسار الجديد حيث سيتم تخزين الصورة
        $target_dir = "../php/uploads/"; // المسار الجديد
        $target_file = $target_dir . basename($image);

        // نقل الصورة إلى المسار الجديد
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            echo "تم رفع الصورة بنجاح";
            // تحديث قيمة الصورة مع مسار 'uploads/'
            $image = "uploads/" . $image;
        } else {
            echo "حدث خطأ أثناء رفع الصورة";
        }
    } else {
        // الاحتفاظ بالصورة القديمة إذا لم يتم تغييرها
        $image = $product['product_image'];
    }

    // استعلام التحديث
    $query = "UPDATE products SET 
                product_name='$name', 
                category='$category', 
                status='$status', 
                price='$price', 
                product_image='$image'
              WHERE id = $id";

    if (mysqli_query($con, $query)) {
        echo "تم تحديث المنتج بنجاح";
    } else {
        echo "حدث خطأ أثناء تحديث المنتج: " . mysqli_error($con);
    }

    // إعادة التوجيه بعد التحديث
    header("Location: products.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/edit.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@100..900&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>edit_product | Cane</title>
    <link rel="icon" type="image/svg" sizes="32x32" href="../image/logo/basket.svg">
</head>
<body>
    <section class="section-edit">
        <h2 class="product-title">تعديل المنتج</h2>
        <form action="edit_product.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data" class="form-edit-product">
            <div class="griy-dis">
                <div class="fr-ro-in-125">
                    <label for="product_name" class="label-product-name">اسم المنتج</label>
                    <br>
                    <input type="text" name="product_name" value="<?php echo $product['product_name']; ?>" required class="input-product-name">
                </div>
                <div class="fr-ro-in-125">
                    <label for="category" class="label-category">الفئة</label>
                    <br>
                    <input type="text" name="category" value="<?php echo $product['category']; ?>" required class="input-category">
                </div>
                <div class="fr-ro-in-125">
                    <label for="status" class="label-status">الحالة</label>
                    <br>
                    <input type="text" name="status" value="<?php echo $product['status']; ?>" required class="input-status">
                </div>
                <div class="fr-ro-in-125">
                    <label for="price" class="label-price">السعر</label>
                    <br>
                    <input type="number" name="price" min="1" value="<?php echo $product['price']; ?>" required class="input-price">
                </div>
            </div>
            <div class="ro-in-la-123">
                <div class="fr-ro-in-125">
                    <label for="product_image" class="label-product-image">صورة المنتج
                        <input type="file" name="product_image" id="product_image" class="input-product-image">
                    </label>
                </div>
                <?php if (!empty($product['product_image'])): ?>
                    <img src="../php/<?php echo $product['product_image']; ?>" alt="صورة المنتج" class="image-current-product">
                <?php endif; ?>
            </div>
            <button type="submit" class="button-update-product">تحديث المنتج</button>
        </form>    
    </section>
</body>
</html>
