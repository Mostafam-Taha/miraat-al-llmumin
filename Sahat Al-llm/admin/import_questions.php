<?php
session_start();
require_once '../php/config.php';

// التحقق من صلاحيات المستخدم
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['json_file'])) {
    // التحقق من وجود ملف
    if ($_FILES['json_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'حدث خطأ أثناء رفع الملف';
    } else {
        // قراءة محتوى الملف
        $jsonContent = file_get_contents($_FILES['json_file']['tmp_name']);
        $questions = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'ملف JSON غير صالح: ' . json_last_error_msg();
        } else {
            try {
                $pdo->beginTransaction();
                $importedCount = 0;
                
                foreach ($questions as $question) {
                    // تنظيف البيانات
                    $question_text = sanitizeInput($question['question_text']);
                    $question_image = isset($question['question_image']) ? sanitizeInput($question['question_image']) : null;
                    $option1 = sanitizeInput($question['option1']);
                    $option2 = sanitizeInput($question['option2']);
                    $option3 = sanitizeInput($question['option3']);
                    $option4 = sanitizeInput($question['option4']);
                    $correct_answer = (int)$question['correct_answer'];
                    $subject = sanitizeInput($question['subject']);
                    $question_type = sanitizeInput($question['question_type']);
                    $added_by = $_SESSION['user_id'];
                    
                    // إدراج السؤال
                    $stmt = $pdo->prepare("INSERT INTO questions 
                        (question_text, question_image, option1, option2, option3, option4, 
                        correct_answer, subject, question_type, added_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt->execute([
                        $question_text, $question_image, $option1, $option2, $option3, $option4,
                        $correct_answer, $subject, $question_type, $added_by
                    ]);
                    
                    $importedCount++;
                }
                
                $pdo->commit();
                $success = "تم استيراد $importedCount سؤال بنجاح";
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "حدث خطأ أثناء الاستيراد: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استيراد الأسئلة</title>
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
        input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
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
        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>استيراد الأسئلة</h1>
        
        <?php if ($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="json_file">ملف JSON للأسئلة:</label>
                <input type="file" id="json_file" name="json_file" accept=".json" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">استيراد الأسئلة</button>
            </div>
        </form>
        
        <div class="actions">
            <a href="export_questions.php" class="btn">تصدير جميع الأسئلة</a>
            <a href="add_question.php" class="btn">العودة إلى إضافة الأسئلة</a>
        </div>
    </div>
</body>
</html>