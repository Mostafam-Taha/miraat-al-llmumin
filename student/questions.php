<?php
session_start();
require_once '../config.php';

// التحقق من تسجيل دخول الطالب
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// معالجة معاملات التصفية
$subject_filter = isset($_GET['subject']) ? sanitizeInput($_GET['subject']) : '';
$type_filter = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';

// بناء استعلام SQL مع التصفية
$sql = "SELECT * FROM questions WHERE 1=1";
$params = [];

if (!empty($subject_filter)) {
    $sql .= " AND subject = ?";
    $params[] = $subject_filter;
}

if (!empty($type_filter)) {
    $sql .= " AND question_type = ?";
    $params[] = $type_filter;
}

$sql .= " ORDER BY subject, question_type";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // الحصول على قائمة المواد وأنواع الأسئلة الفريدة للتصفية
    $subjects = $pdo->query("SELECT DISTINCT subject FROM questions ORDER BY subject")->fetchAll(PDO::FETCH_COLUMN);
    $types = $pdo->query("SELECT DISTINCT question_type FROM questions ORDER BY question_type")->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    die("Error fetching questions: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بنك الأسئلة</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .filters {
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .filter-group {
            display: inline-block;
            margin-left: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, button {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        .reset-btn {
            background-color: #e74c3c;
        }
        .reset-btn:hover {
            background-color: #c0392b;
        }
        .question-card {
            background-color: white;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .question-text {
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .question-image {
            max-width: 100%;
            max-height: 300px;
            margin-bottom: 15px;
            display: block;
        }
        .options-list {
            list-style-type: none;
            padding: 0;
        }
        .option {
            padding: 10px;
            margin-bottom: 5px;
            background-color: #f9f9f9;
            border-left: 4px solid #3498db;
        }
        .correct-option {
            background-color: #e8f5e9;
            border-left: 4px solid #2ecc71;
            font-weight: bold;
        }
        .question-meta {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #7f8c8d;
        }
        .meta-item {
            display: inline-block;
            margin-left: 15px;
        }
        .note {
            font-size: 12px;
            color: #e67e22;
            margin-top: 5px;
        }
        .no-questions {
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>بنك الأسئلة التعليمي</h1>
        </header>
        
        <div class="filters">
            <form method="get" action="">
                <div class="filter-group">
                    <label for="subject">المادة:</label>
                    <select id="subject" name="subject">
                        <option value="">كل المواد</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= htmlspecialchars($subject) ?>" <?= $subject_filter === $subject ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subject) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="type">نوع السؤال:</label>
                    <select id="type" name="type">
                        <option value="">كل الأنواع</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $type_filter === $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit">تصفية</button>
                    <a href="?" class="reset-btn" style="padding: 8px 12px; text-decoration: none; color: white; border-radius: 4px;">إعادة تعيين</a>
                </div>
            </form>
        </div>
        
        <?php if (empty($questions)): ?>
            <div class="no-questions">
                <p>لا توجد أسئلة متاحة حسب معايير التصفية المحددة</p>
            </div>
        <?php else: ?>
            <?php foreach ($questions as $question): ?>
                <div class="question-card">
                    <div class="question-text"><?= nl2br(htmlspecialchars($question['question_text'])) ?></div>
                    
                    <?php if (!empty($question['question_image'])): ?>
                        <img src="<?= htmlspecialchars($question['question_image']) ?>" alt="صورة السؤال" class="question-image">
                    <?php endif; ?>
                    
                    <ul class="options-list">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <li class="option <?= $question['correct_answer'] == $i ? 'correct-option' : '' ?>">
                                <?= htmlspecialchars($question['option'.$i]) ?>
                                <?php if (!empty($question['note'.$i])): ?>
                                    <div class="note"><?= htmlspecialchars($question['note'.$i]) ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                    </ul>
                    
                    <div class="question-meta">
                        <span class="meta-item"><strong>المادة:</strong> <?= htmlspecialchars($question['subject']) ?></span>
                        <span class="meta-item"><strong>النوع:</strong> <?= htmlspecialchars($question['question_type']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>