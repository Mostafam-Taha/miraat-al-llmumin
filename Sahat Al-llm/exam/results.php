<?php
require_once '../php/config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// جلب جميع نتائج الطالب
$stmt = $pdo->prepare("SELECT * FROM exam_results WHERE user_id = ? ORDER BY exam_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب إحصائيات الطالب
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_exams,
        SUM(score) as total_correct,
        SUM(total_questions) as total_questions,
        AVG(score * 100 / total_questions) as avg_percentage,
        subject
    FROM exam_results 
    WHERE user_id = ?
    GROUP BY subject
");
$statsStmt->execute([$_SESSION['user_id']]);
$stats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);

// إذا طلب عرض نتيجة محددة
$examDetails = [];
$wrongAnswers = [];
if (isset($_GET['result_id'])) {
    $resultId = (int)$_GET['result_id'];
    
    // جلب تفاصيل الاختبار
    $stmt = $pdo->prepare("SELECT * FROM exam_results WHERE id = ? AND user_id = ?");
    $stmt->execute([$resultId, $_SESSION['user_id']]);
    $examDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($examDetails) {
        // جلب الإجابات الخاطئة
        $stmt = $pdo->prepare("
            SELECT sa.*, q.question_text, q.option1, q.option2, q.option3, q.option4, 
                   q.correct_answer, q.note1, q.note2, q.note3, q.note4
            FROM student_answers sa
            JOIN questions q ON sa.question_id = q.id
            WHERE sa.exam_result_id = ? AND sa.is_correct = 0
        ");
        $stmt->execute([$resultId]);
        $wrongAnswers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتائج الاختبارات</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #333;
            text-align: center;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .results-table th, .results-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .results-table th {
            background-color: #f2f2f2;
        }
        .results-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .results-table tr:hover {
            background-color: #f1f1f1;
        }
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin: 20px 0;
        }
        .stat-box {
            background: #e2f0fd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px;
            width: 200px;
            text-align: center;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            color: #7f8c8d;
        }
        .wrong-answer {
            background-color: #f8d7da;
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
        }
        .correct-answer {
            background-color: #d4edda;
        }
        .question-text {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .option {
            padding: 5px;
            margin: 3px 0;
            border-radius: 3px;
        }
        .user-choice {
            background-color: #fff3cd;
        }
        .chart-container {
            width: 100%;
            height: 400px;
            margin: 20px 0;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <h1>نتائج الاختبارات</h1>
        
        <h2>إحصائيات الأداء</h2>
        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-value"><?= count($results) ?></div>
                <div class="stat-label">عدد الاختبارات</div>
            </div>
            
            <?php foreach ($stats as $stat): ?>
            <div class="stat-box">
                <div class="stat-value"><?= round($stat['avg_percentage'], 1) ?>%</div>
                <div class="stat-label">متوسط النسبة في <?= htmlspecialchars($stat['subject']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="chart-container">
            <canvas id="performanceChart"></canvas>
        </div>
        
        <h2>سجل الاختبارات</h2>
        <table class="results-table">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>المادة</th>
                    <th>الدرس</th>
                    <th>نوع الاختبار</th>
                    <th>النتيجة</th>
                    <th>النسبة</th>
                    <th>تفاصيل</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                <tr>
                    <td><?= date('Y-m-d H:i', strtotime($result['exam_date'])) ?></td>
                    <td><?= htmlspecialchars($result['subject']) ?></td>
                    <td><?= htmlspecialchars($result['lesson_name']) ?></td>
                    <td><?= htmlspecialchars($result['question_type']) ?></td>
                    <td><?= $result['score'] ?> / <?= $result['total_questions'] ?></td>
                    <td><?= round(($result['score'] / $result['total_questions']) * 100) ?>%</td>
                    <td>
                        <a href="results.php?result_id=<?= $result['id'] ?>" class="btn">عرض التفاصيل</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (!empty($examDetails)): ?>
        <h2>تفاصيل الاختبار</h2>
        <div style="background: #e2f0fd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <p><strong>المادة:</strong> <?= htmlspecialchars($examDetails['subject']) ?></p>
            <p><strong>الدرس:</strong> <?= htmlspecialchars($examDetails['lesson_name']) ?></p>
            <p><strong>نوع الاختبار:</strong> <?= htmlspecialchars($examDetails['question_type']) ?></p>
            <p><strong>التاريخ:</strong> <?= date('Y-m-d H:i', strtotime($examDetails['exam_date'])) ?></p>
            <p><strong>النتيجة:</strong> <?= $examDetails['score'] ?> من <?= $examDetails['total_questions'] ?> (<?= round(($examDetails['score'] / $examDetails['total_questions']) * 100) ?>%)</p>
        </div>
        
        <h3>الإجابات الخاطئة</h3>
        <?php if (empty($wrongAnswers)): ?>
            <p style="text-align: center; color: green;">لا توجد إجابات خاطئة في هذا الاختبار!</p>
        <?php else: ?>
            <?php foreach ($wrongAnswers as $answer): ?>
                <div class="wrong-answer">
                    <div class="question-text"><?= htmlspecialchars($answer['question_text']) ?></div>
                    
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <?php if (!empty($answer['option' . $i])): ?>
                            <div class="option <?= $answer['user_answer'] == $i ? 'user-choice' : '' ?> <?= $answer['correct_answer'] == $i ? 'correct-answer' : '' ?>">
                                <?= htmlspecialchars($answer['option' . $i]) ?>
                                <?php if ($answer['user_answer'] == $i): ?>
                                    <strong>(إجابتك)</strong>
                                <?php endif; ?>
                                <?php if ($answer['correct_answer'] == $i): ?>
                                    <strong>(الإجابة الصحيحة)</strong>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if (!empty($answer['note' . $answer['user_answer']])): ?>
                        <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 3px;">
                            <strong>ملاحظة:</strong> <?= htmlspecialchars($answer['note' . $answer['user_answer']]) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        // رسم مخطط الأداء
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    $dates = [];
                    $scores = [];
                    foreach ($results as $result) {
                        $dates[] = date('Y-m-d', strtotime($result['exam_date']));
                        $scores[] = round(($result['score'] / $result['total_questions']) * 100);
                    }
                    echo "'" . implode("', '", $dates) . "'";
                    ?>
                ],
                datasets: [{
                    label: 'النسبة المئوية',
                    data: [<?= implode(', ', $scores) ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'تطور أدائك في الاختبارات',
                        font: {
                            size: 18
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>