<?php
session_start();
// التحقق من صلاحيات المستخدم
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// استدعاء اتصال قاعدة البيانات
require_once '../php/config.php';

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // تنظيف المدخلات
    $question_text = sanitizeInput($_POST['question_text']);
    $option1 = sanitizeInput($_POST['option1']);
    $option2 = sanitizeInput($_POST['option2']);
    $option3 = sanitizeInput($_POST['option3']);
    $option4 = sanitizeInput($_POST['option4']);
    $correct_answer = (int)$_POST['correct_answer'];
    $subject = sanitizeInput($_POST['subject']);
    $lesson_name = sanitizeInput($_POST['lesson_name']);
    $question_type = sanitizeInput($_POST['question_type']);
    $note1 = isset($_POST['note1']) ? sanitizeInput($_POST['note1']) : null;
    $note2 = isset($_POST['note2']) ? sanitizeInput($_POST['note2']) : null;
    $note3 = isset($_POST['note3']) ? sanitizeInput($_POST['note3']) : null;
    $note4 = isset($_POST['note4']) ? sanitizeInput($_POST['note4']) : null;
    $added_by = $_SESSION['user_id'];
    $question_image = null;

    // معالجة رفع الصورة
    if (isset($_FILES['question_image']) && $_FILES['question_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/questions/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($fileInfo, $_FILES['question_image']['tmp_name']);
        finfo_close($fileInfo);

        if (in_array($detectedType, $allowedTypes)) {
            $extension = pathinfo($_FILES['question_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('question_') . '.' . $extension;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['question_image']['tmp_name'], $destination)) {
                $question_image = $destination;
            } else {
                $error_message = "فشل في رفع الصورة.";
            }
        } else {
            $error_message = "نوع الملف غير مسموح به. يرجى رفع صورة (JPEG, PNG, GIF) فقط.";
        }
    }

    if (!isset($error_message)) {
        try {
            // إعداد استعلام الإدراج
            $stmt = $pdo->prepare("INSERT INTO questions 
                (question_text, question_image, option1, option2, option3, option4, 
                correct_answer, subject, lesson_name, question_type, note1, note2, note3, note4, added_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // تنفيذ الاستعلام مع إضافة lesson_name
            $stmt->execute([
                $question_text, $question_image, $option1, $option2, $option3, $option4, 
                $correct_answer, $subject, $lesson_name, $question_type, 
                $note1, $note2, $note3, $note4, $added_by
            ]);

            $success_message = "تمت إضافة السؤال بنجاح!";
        } catch (PDOException $e) {
            $error_message = "حدث خطأ أثناء إضافة السؤال: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة سؤال جديد</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        textarea, input[type="text"], select, input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
        }
        .options-container {
            border: 1px solid #eee;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
        .option-item {
            margin-bottom: 10px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>إضافة سؤال جديد</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="question_text">نص السؤال:</label>
                <textarea id="question_text" name="question_text" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="question_image">صورة السؤال (اختياري):</label>
                <input type="file" id="question_image" name="question_image" accept="image/*">
                <img id="imagePreview" class="image-preview" src="#" alt="معاينة الصورة">
            </div>
            
            <div class="form-group">
                <label>الاختيارات:</label>
                <div class="options-container">
                    <div class="option-item">
                        <label for="option1">الاختيار الأول:</label>
                        <input type="text" id="option1" name="option1" required>
                        <label for="note1">ملاحظة (اختيارية):</label>
                        <input type="text" id="note1" name="note1">
                    </div>
                    
                    <div class="option-item">
                        <label for="option2">الاختيار الثاني:</label>
                        <input type="text" id="option2" name="option2" required>
                        <label for="note2">ملاحظة (اختيارية):</label>
                        <input type="text" id="note2" name="note2">
                    </div>
                    
                    <div class="option-item">
                        <label for="option3">الاختيار الثالث:</label>
                        <input type="text" id="option3" name="option3" required>
                        <label for="note3">ملاحظة (اختيارية):</label>
                        <input type="text" id="note3" name="note3">
                    </div>
                    
                    <div class="option-item">
                        <label for="option4">الاختيار الرابع:</label>
                        <input type="text" id="option4" name="option4" required>
                        <label for="note4">ملاحظة (اختيارية):</label>
                        <input type="text" id="note4" name="note4">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="correct_answer">الإجابة الصحيحة:</label>
                <select id="correct_answer" name="correct_answer" required>
                    <option value="1">الاختيار الأول</option>
                    <option value="2">الاختيار الثاني</option>
                    <option value="3">الاختيار الثالث</option>
                    <option value="4">الاختيار الرابع</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="subject">المادة:</label>
                <input type="text" id="subject" name="subject" required>
            </div>
            
            <div class="form-group">
                <label for="lesson_name">اسم الدرس:</label>
                <input type="text" id="lesson_name" name="lesson_name" required>
            </div>
            
            <div class="form-group">
                <label for="question_type">نوع السؤال:</label>
                <select id="question_type" name="question_type" required>
                    <option value="بنك اسئلة">بنك اسئلة</option>
                    <option value="اختبارات شاملة">اختبارات شاملة</option>
                    <option value="تحدى نفسك">تحدى نفسك</option>
                    <option value="إمتحان الوزارة">إمتحان الوزارة</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">إضافة السؤال</button>
            </div>
        </form>
    </div>

    <script>
        // عرض معاينة الصورة قبل الرفع
        document.getElementById('question_image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>