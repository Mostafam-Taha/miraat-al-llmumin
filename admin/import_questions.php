<?php
require_once '../php/config.php';
require_once '../php/check_session.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['json_file'])) {
    if ($_FILES['json_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'حدث خطأ أثناء رفع الملف';
    } else {
        $jsonContent = file_get_contents($_FILES['json_file']['tmp_name']);
        $questions = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'ملف JSON غير صالح: ' . json_last_error_msg();
        } else {
            try {
                $pdo->beginTransaction();
                $importedCount = 0;
                
                foreach ($questions as $question) {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
        }
        
        .container:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
            font-weight: 700;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(to right, var(--primary-color), var(--success-color));
            border-radius: 3px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
            cursor: pointer;
        }
        
        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background-color: #f8f9fa;
            border: 2px dashed #ccc;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .file-input-label:hover {
            background-color: #e9ecef;
            border-color: var(--primary-color);
        }
        
        .file-input-label i {
            margin-left: 10px;
            font-size: 24px;
            color: var(--primary-color);
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
        }
        
        .btn:active {
            transform: translateY(1px);
        }
        
        .btn-secondary {
            background: linear-gradient(to right, #6c757d, #5a6268);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(to right, #5a6268, #6c757d);
        }
        
        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            animation: fadeIn 0.5s;
        }
        
        .success {
            background-color: rgba(76, 201, 240, 0.2);
            color: #0a6b7e;
            border-left: 4px solid var(--success-color);
        }
        
        .error {
            background-color: rgba(247, 37, 133, 0.2);
            color: #a4133c;
            border-left: 4px solid var(--danger-color);
        }
        
        .message i {
            margin-left: 10px;
            font-size: 20px;
        }
        
        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 30px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container animate__animated animate__fadeIn">
        <h1><i class="fas fa-file-import"></i> استيراد الأسئلة</h1>
        
        <?php if ($success): ?>
            <div class="message success animate__animated animate__bounceIn">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error animate__animated animate__shakeX">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="json_file">اختر ملف JSON للأسئلة:</label>
                <div class="file-input-wrapper">
                    <label for="json_file" class="file-input-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span id="file-name">اضغط لاختيار الملف (JSON فقط)</span>
                    </label>
                    <input type="file" id="json_file" name="json_file" accept=".json" required 
                           onchange="document.getElementById('file-name').textContent = this.files[0] ? this.files[0].name : 'اضغط لاختيار الملف (JSON فقط)'">
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn animate__animated animate__pulse animate__infinite">
                    <i class="fas fa-file-import"></i> استيراد الأسئلة
                </button>
            </div>
        </form>
        
        <div class="actions">
            <a href="export_questions.php" class="btn btn-secondary">
                <i class="fas fa-file-export"></i> تصدير الأسئلة
            </a>
            <a href="add_question.php" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> العودة للإضافة
            </a>
        </div>
    </div>

    <script>
        // تأثيرات إضافية عند التحميل
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.container');
            setTimeout(() => {
                container.style.opacity = 1;
            }, 100);
            
            // تأثير عند تمرير الماوس على الأزرار
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.classList.add('animate__animated', 'animate__pulse');
                });
                
                button.addEventListener('mouseleave', function() {
                    this.classList.remove('animate__animated', 'animate__pulse');
                });
            });
        });

        


        
    </script>
</body>
</html>