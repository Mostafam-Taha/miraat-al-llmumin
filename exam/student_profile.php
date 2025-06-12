<?php
require_once '../php/config.php';

// التحقق من وجود معرف الطالب في الرابط
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: top_students.php");
    exit();
}

$student_id = $_GET['id'];

// استرجاع بيانات الطالب الأساسية مع إعدادات الخصوصية
function getStudentInfo($pdo, $student_id) {
    $query = "SELECT *, 
              (SELECT show_rank FROM users WHERE id = ?) as show_rank,
              (SELECT show_scores FROM users WHERE id = ?) as show_scores,
              (SELECT show_tests FROM users WHERE id = ?) as show_tests,
              (SELECT show_class FROM users WHERE id = ?) as show_class,
              (SELECT show_avatar FROM users WHERE id = ?) as show_avatar
              FROM users WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$student_id, $student_id, $student_id, $student_id, $student_id, $student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
// استرجاع نتائج اختبارات الطالب
function getStudentResults($pdo, $student_id) {
    $query = "SELECT * FROM exam_results WHERE user_id = ? ORDER BY exam_date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// استرجاع ترتيب الطالب
function getStudentRank($pdo, $student_id) {
    $query = "SELECT rank FROM (
                SELECT 
                  u.id,
                  RANK() OVER (ORDER BY SUM(er.score) DESC) as rank
                FROM 
                  users u
                JOIN 
                  exam_results er ON u.id = er.user_id
                GROUP BY 
                  u.id
              ) as ranked_users
              WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$student_id]);
    return $stmt->fetchColumn();
}

// استرجاع المجموع الكلي للطالب
function getStudentTotalScore($pdo, $student_id) {
    $query = "SELECT SUM(score) as total_score FROM exam_results WHERE user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$student_id]);
    return $stmt->fetchColumn();
}

$studentInfo = getStudentInfo($pdo, $student_id);
$studentResults = $studentInfo['show_tests'] ? getStudentResults($pdo, $student_id) : [];
$studentRank = $studentInfo['show_rank'] ? getStudentRank($pdo, $student_id) : null;
$totalScore = $studentInfo['show_scores'] ? getStudentTotalScore($pdo, $student_id) : null;

if (!$studentInfo) {
    header("Location: top_students.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملف الطالب - <?php echo sanitizeInput($studentInfo['username']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --border-radius: 12px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: var(--dark-color);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .back-button {
            position: absolute;
            right: 20px;
            top: 20px;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .back-button:hover {
            transform: translateX(-5px);
        }

        .student-profile {
            display: flex;
            padding: 30px;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .student-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--light-color);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-left: 30px;
        }

        .student-details {
            flex: 1;
        }

        .student-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .student-class {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 15px;
        }

        .student-stats {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }

        .stat-card {
            background: var(--light-color);
            padding: 15px 20px;
            border-radius: var(--border-radius);
            text-align: center;
            min-width: 120px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }

        .results-section {
            padding: 20px 30px;
        }

        .section-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--dark-color);
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-color);
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .results-table th {
            background-color: var(--light-color);
            padding: 15px;
            text-align: right;
            font-weight: 700;
        }

        .results-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            text-align: right;
        }

        .results-table tr:last-child td {
            border-bottom: none;
        }

        .results-table tr:hover {
            background-color: #f9f9f9;
        }

        .score-cell {
            font-weight: 700;
            color: var(--primary-color);
        }

        .percentage {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            background-color: var(--light-color);
            color: var(--dark-color);
        }

        .progress-container {
            width: 100%;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin-top: 5px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border-radius: 4px;
        }

        .no-results {
            text-align: center;
            padding: 30px;
            color: #666;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .student-profile {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }
            
            .student-avatar {
                margin: 0 0 20px 0;
                width: 100px;
                height: 100px;
            }
            
            .student-stats {
                flex-direction: column;
                gap: 10px;
            }
            
            .stat-card {
                width: 100%;
            }
            
            .results-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
<div class="header">
            <a href="top_students.php" class="back-button">
                <i class="fas fa-arrow-right"></i> العودة للقائمة
            </a>
            <h1>ملف الطالب</h1>
        </div>
        
        <div class="student-profile">
            <?php if ($studentInfo['show_avatar']): ?>
                <img src="../api/<?php echo !empty($studentInfo['avatar']) ? sanitizeInput($studentInfo['avatar']) : 'default_avatar.png'; ?>" 
                     alt="صورة الطالب" class="student-avatar">
            <?php else: ?>
                <div class="student-avatar" style="background-color: #eee; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user" style="font-size: 3rem; color: #999;"></i>
                </div>
            <?php endif; ?>
            
            <div class="student-details">
                <h2 class="student-name"><?php echo sanitizeInput($studentInfo['username']); ?></h2>
                <?php if ($studentInfo['show_class']): ?>
                    <p class="student-class"><?php echo sanitizeInput($studentInfo['student_class']); ?></p>
                <?php endif; ?>
                
                <div class="student-stats">
                    <?php if ($studentInfo['show_rank'] && $studentRank !== null): ?>
                        <div class="stat-card">
                            <div class="stat-value">#<?php echo sanitizeInput($studentRank); ?></div>
                            <div class="stat-label">الترتيب</div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($studentInfo['show_scores'] && $totalScore !== null): ?>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo sanitizeInput($totalScore); ?></div>
                            <div class="stat-label">النقاط الكلية</div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo count($studentResults); ?></div>
                        <div class="stat-label">عدد الاختبارات</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="results-section">
            <h3 class="section-title">نتائج الاختبارات</h3>
            
            <?php if (!$studentInfo['show_tests']): ?>
                <div class="no-results">
                    <i class="fas fa-lock" style="font-size: 2rem; margin-bottom: 15px; color: #ccc;"></i>
                    <p>قام هذا الطالب بإخفاء نتائج اختباراته</p>
                </div>
            <?php elseif (count($studentResults) > 0): ?>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>نوع الاختبار</th>
                            <th>المادة</th>
                            <th>الدرس</th>
                            <th>التاريخ</th>
                            <th>النتيجة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($studentResults as $result): 
                            $percentage = round(($result['score'] / $result['total_questions']) * 100);
                        ?>
                            <tr>
                                <td><?php echo sanitizeInput($result['question_type']); ?></td>
                                <td><?php echo sanitizeInput($result['subject']); ?></td>
                                <td><?php echo sanitizeInput($result['lesson_name']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($result['exam_date'])); ?></td>
                                <td>
                                    <div class="score-cell">
                                        <?php echo sanitizeInput($result['score']); ?> / <?php echo sanitizeInput($result['total_questions']); ?>
                                        <span class="percentage"><?php echo $percentage; ?>%</span>
                                    </div>
                                    <div class="progress-container">
                                        <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 15px; color: #ccc;"></i>
                    <p>لا توجد نتائج اختبارات مسجلة لهذا الطالب بعد</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>