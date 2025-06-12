<?php
require_once '../php/config.php';
session_start();

// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit;
// }

if (!isset($_GET['id'])) {
    header('Location: questions_manager.php');
    exit;
}

$question_id = sanitizeInput($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$question_id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    $_SESSION['message'] = "السؤال غير موجود";
    header('Location: questions_manager.php');
    exit;
}

// معالجة تحديث السؤال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = sanitizeInput($_POST['question_text']);
    $question_image = sanitizeInput($_POST['question_image']);
    $option1 = sanitizeInput($_POST['option1']);
    $option2 = sanitizeInput($_POST['option2']);
    $option3 = sanitizeInput($_POST['option3']);
    $option4 = sanitizeInput($_POST['option4']);
    $correct_answer = (int)$_POST['correct_answer'];
    $subject = sanitizeInput($_POST['subject']);
    $lesson_name = sanitizeInput($_POST['lesson_name']);
    $question_type = sanitizeInput($_POST['question_type']);
    $note1 = sanitizeInput($_POST['note1']);
    $note2 = sanitizeInput($_POST['note2']);
    $note3 = sanitizeInput($_POST['note3']);
    $note4 = sanitizeInput($_POST['note4']);
    
    // في قسم معالجة تحديث السؤال
    $stmt = $pdo->prepare("UPDATE questions SET 
        question_text = ?, 
        question_image = ?, 
        option1 = ?, 
        option2 = ?, 
        option3 = ?, 
        option4 = ?, 
        correct_answer = ?, 
        subject = ?, 
        lesson_name = ?, 
        question_type = ?, 
        note1 = ?, 
        note2 = ?, 
        note3 = ?, 
        note4 = ?,
        modified_date = NOW(),
        modified_by = ?
        WHERE id = ?");

    $stmt->execute([
        $question_text,
        $question_image,
        $option1,
        $option2,
        $option3,
        $option4,
        $correct_answer,
        $subject,
        $lesson_name,
        $question_type,
        $note1,
        $note2,
        $note3,
        $note4,
        $_SESSION['admin_id'], // ID المسؤول الذي قام بالتعديل
        $question_id
    ]);
    
    $_SESSION['message'] = "تم تحديث السؤال بنجاح";
    header('Location: questions_manager.php');
    exit;
}

// جلب المواد وأنواع الأسئلة للقوائم المنسدلة
$subjects = $pdo->query("SELECT DISTINCT subject FROM questions")->fetchAll(PDO::FETCH_COLUMN);
$question_types = $pdo->query("SELECT DISTINCT question_type FROM questions")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل السؤال</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a6bff;
            --secondary-color: #ff6b6b;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --text-color: #333;
            --text-light: #777;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --border-radius: 8px;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --fc-btn-bg: #066ac9;
            --fc-color-btn-new: #d6293e;
            --fc-border-co-hevor: #066ac9;
            --fc-box-shadow-hevor: 0px 0px 0px 5px #066bc954;
        }

        * {
            font-family: 'Cairo', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: #f5f7fa;
            color: var(--text-color);
            direction: rtl;
        }

        .admin-header {
            background-color: white;
            box-shadow: var(--box-shadow);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .form-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            border-top: 4px solid var(--primary-color);
        }

        .page-title {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: var(--border-radius);
            padding: 0.5rem 1rem;
            border: 1px solid var(--fc-border-co-hevor);
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: var(--fc-box-shadow-hevor);
        }

        textarea.form-control {
            min-height: 120px;
        }

        .btn-primary {
            background-color: var(--fc-btn-bg);
            border: none;
            padding: var(--fc-padding-width-btn);
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: #0558a8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
            padding: var(--fc-padding-width-btn);
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--fc-color-btn-new);
            border: none;
            padding: var(--fc-padding-width-btn);
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-danger:hover {
            background-color: #b51f32;
            transform: translateY(-2px);
        }

        .option-number {
            display: inline-block;
            width: 25px;
            height: 25px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 25px;
            margin-left: 0.5rem;
            font-size: 0.8rem;
        }

        .correct-option {
            border-left: 3px solid var(--success-color);
            background-color: rgba(40, 167, 69, 0.05);
        }

        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 1rem;
            border-radius: var(--border-radius);
            display: none;
        }

        .preview-container {
            text-align: center;
            margin-bottom: 1rem;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed #eee;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-left: 0.5rem;
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="m-0"><i class="fas fa-edit me-2"></i>تعديل السؤال</h1>
                </div>
                <div class="col-md-6 text-md-start text-end">
                    <a href="questions_manager.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-container">
                    <h2 class="page-title">تعديل السؤال #<?= $question['id'] ?></h2>
                    
                    <form method="POST" action="">
                        <!-- قسم السؤال الأساسي -->
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-question-circle"></i> معلومات السؤال</h4>
                            
                            <div class="mb-4">
                            <label for="question_text" class="form-label">نص السؤال</label>
                                <textarea class="form-control" id="question_text" name="question_text" rows="4" required><?= htmlspecialchars($question['question_text']) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="question_image" class="form-label">رابط صورة السؤال (اختياري)</label>
                            <input type="text" class="form-control" id="question_image" name="question_image" value="<?= htmlspecialchars($question['question_image']) ?>">
                                <?php if($question['question_image']): ?>
                                <div class="preview-container">
                                    <img src="<?= htmlspecialchars($question['question_image']) ?>" alt="معاينة صورة السؤال" class="img-thumbnail image-preview" id="questionImagePreview" style="display: block;">
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- قسم التصنيفات -->
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-tags"></i> تصنيف السؤال</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">المادة</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <?php foreach ($subjects as $sub): ?>
                                        <option value="<?= $sub ?>" <?= $sub == $question['subject'] ? 'selected' : '' ?>><?= $sub ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                                <div class="col-md-6 mb-3">
                                <label for="lesson_name" class="form-label">اسم الدرس</label>
                                <input type="text" class="form-control" id="lesson_name" name="lesson_name" value="<?= htmlspecialchars($question['lesson_name']) ?>" required>
                            </div>
                        </div>
                        
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                <label for="question_type" class="form-label">نوع السؤال</label>
                                <select class="form-select" id="question_type" name="question_type" required>
                                    <?php foreach ($question_types as $type): ?>
                                        <option value="<?= $type ?>" <?= $type == $question['question_type'] ? 'selected' : '' ?>><?= $type ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                                <div class="col-md-6 mb-3">
                                <label for="correct_answer" class="form-label">الإجابة الصحيحة</label>
                                <select class="form-select" id="correct_answer" name="correct_answer" required>
                                        <option value="1" <?= $question['correct_answer'] == 1 ? 'selected' : '' ?>>الخيار الأول (أ)</option>
                                        <option value="2" <?= $question['correct_answer'] == 2 ? 'selected' : '' ?>>الخيار الثاني (ب)</option>
                                        <option value="3" <?= $question['correct_answer'] == 3 ? 'selected' : '' ?>>الخيار الثالث (ج)</option>
                                        <option value="4" <?= $question['correct_answer'] == 4 ? 'selected' : '' ?>>الخيار الرابع (د)</option>
                                </select>
                            </div>
                        </div>
                        </div>
                        
                        <!-- قسم الخيارات -->
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-list-ol"></i> خيارات الإجابة</h4>
                        
                            <div class="row mb-3 <?= $question['correct_answer'] == 1 ? 'correct-option' : '' ?>">
                                <div class="col-12">
                                    <label for="option1" class="form-label"><span class="option-number">1</span> الخيار الأول (أ)</label>
                                <input type="text" class="form-control" id="option1" name="option1" value="<?= htmlspecialchars($question['option1']) ?>" required>
                            </div>
                            </div>
                            
                            <div class="row mb-3 <?= $question['correct_answer'] == 2 ? 'correct-option' : '' ?>">
                                <div class="col-12">
                                    <label for="option2" class="form-label"><span class="option-number">2</span> الخيار الثاني (ب)</label>
                                <input type="text" class="form-control" id="option2" name="option2" value="<?= htmlspecialchars($question['option2']) ?>" required>
                            </div>
                        </div>
                        
                            <div class="row mb-3 <?= $question['correct_answer'] == 3 ? 'correct-option' : '' ?>">
                                <div class="col-12">
                                    <label for="option3" class="form-label"><span class="option-number">3</span> الخيار الثالث (ج)</label>
                                <input type="text" class="form-control" id="option3" name="option3" value="<?= htmlspecialchars($question['option3']) ?>" required>
                            </div>
                            </div>
                            
                            <div class="row mb-3 <?= $question['correct_answer'] == 4 ? 'correct-option' : '' ?>">
                                <div class="col-12">
                                    <label for="option4" class="form-label"><span class="option-number">4</span> الخيار الرابع (د)</label>
                                <input type="text" class="form-control" id="option4" name="option4" value="<?= htmlspecialchars($question['option4']) ?>" required>
                            </div>
                        </div>
                        </div>
                        
                        <!-- قسم الملاحظات -->
                        <div class="form-section">
                            <h4 class="section-title"><i class="fas fa-sticky-note"></i> ملاحظات إضافية</h4>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="note1" class="form-label">ملاحظة 1</label>
                                    <input type="text" class="form-control" id="note1" name="note1" value="<?= htmlspecialchars($question['note1']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="note2" class="form-label">ملاحظة 2</label>
                                    <input type="text" class="form-control" id="note2" name="note2" value="<?= htmlspecialchars($question['note2']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="note3" class="form-label">ملاحظة 3</label>
                                    <input type="text" class="form-control" id="note3" name="note3" value="<?= htmlspecialchars($question['note3']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="note4" class="form-label">ملاحظة 4</label>
                                    <input type="text" class="form-control" id="note4" name="note4" value="<?= htmlspecialchars($question['note4']) ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- أزرار التحكم -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash-alt me-1"></i> حذف السؤال
                            </button>
                            
                            <div>
                                <a href="questions_manager.php" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-1"></i> إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> حفظ التعديلات
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal لحذف السؤال -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">تأكيد الحذف</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد أنك تريد حذف هذا السؤال؟ هذا الإجراء لا يمكن التراجع عنه.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <a href="delete_question.php?id=<?= $question['id'] ?>" class="btn btn-danger">حذف السؤال</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // عرض معاينة الصورة عند تغيير الرابط
        document.getElementById('question_image').addEventListener('input', function() {
            const preview = document.getElementById('questionImagePreview');
            if (this.value) {
                preview.src = this.value;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });

        // تمييز الخيار الصحيح عند التغيير
        document.getElementById('correct_answer').addEventListener('change', function() {
            document.querySelectorAll('.correct-option').forEach(el => {
                el.classList.remove('correct-option');
            });
            
            const selectedOption = this.value;
            document.querySelectorAll('.row.mb-3').forEach((row, index) => {
                if (index + 1 == selectedOption) {
                    row.classList.add('correct-option');
                }
            });
        });
    </script>
</body>
</html>