<?php
require_once '../php/config.php';

if (!isset($_GET['token'])) {
    header("Location: ../login.php");
    exit();
}

$token = $_GET['token'];

// التحقق من صحة الرمز وجلب بيانات المستخدم
$stmt = $pdo->prepare("
    SELECT u.* 
    FROM users u
    WHERE u.share_token = ? 
    AND u.share_token_expiry > NOW()
");
$stmt->execute([$token]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userData) {
    die("رابط المشاركة غير صالح أو منتهي الصلاحية");
}

$user_id = $userData['id'];

// جلب نتائج الطالب مع مراعاة إعدادات الخصوصية
$query = "SELECT * FROM exam_results WHERE user_id = ?";
$params = [$user_id];

// تطبيق إعدادات الخصوصية
if (!$userData['show_tests']) {
    $query .= " AND 1=0"; // لا تظهر أي نتائج
}

$query .= " ORDER BY exam_date DESC LIMIT 10"; // عرض آخر 10 نتائج فقط

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب إحصائيات الطالب مع مراعاة الخصوصية
$stats = [];
if ($userData['show_scores']) {
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
    $statsStmt->execute([$user_id]);
    $stats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);
}

// عرض صفحة الملف الشخصي بنفس تصميم student_profile.php
// ولكن مع مراعاة إعدادات الخصوصية في كل قسم
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملف الطالب - <?php echo sanitizeInput($userData['username']); ?></title>
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="ملف الطالب - <?php echo sanitizeInput($userData['username']); ?>">
    <meta property="og:description" content="عرض ملف الطالب الأكاديمي والنتائج">
    <meta property="og:url" content="<?php echo "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:type" content="profile">
    <meta property="og:image" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/api/' . (!empty($userData['avatar']) ? sanitizeInput($userData['avatar']) : 'default_avatar.png'); ?>">
    <meta property="og:image:width" content="600">
    <meta property="og:image:height" content="600">
    <meta property="og:locale" content="ar_AR">
    <meta property="profile:username" content="<?php echo sanitizeInput($userData['username']); ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="ملف الطالب - <?php echo sanitizeInput($userData['username']); ?>">
    <meta name="twitter:description" content="عرض ملف الطالب الأكاديمي والنتائج">
    <meta name="twitter:image" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/api/' . (!empty($userData['avatar']) ? sanitizeInput($userData['avatar']) : 'default_avatar.png'); ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --light-text: #7f8c8d;
            --border-color: #e0e0e0;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark-text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header h1 {
            color: var(--secondary-color);
            font-size: 28px;
            font-weight: 700;
            position: relative;
            display: inline-block;
            padding-bottom: 10px;
        }
        
        .header h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 50%;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .student-profile {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            flex-direction: row-reverse;
            position: relative;
            overflow: hidden;
        }
        
        .student-profile::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 5px;
            height: 100%;
            background-color: var(--primary-color);
        }
        
        .student-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--light-bg);
            margin-left: 30px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .student-details {
            flex: 1;
        }
        
        .student-name {
            color: var(--secondary-color);
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .student-class {
            color: var(--light-text);
            font-size: 16px;
            margin-bottom: 20px;
            position: relative;
            padding-right: 15px;
        }
        
        .student-class::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background-color: var(--primary-color);
            border-radius: 50%;
        }
        
        .student-stats {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-card {
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 15px 20px;
            text-align: center;
            min-width: 120px;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--light-text);
        }
        
        .results-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .section-title {
            color: var(--secondary-color);
            font-size: 22px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -1px;
            right: 0;
            width: 100px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .results-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: right;
            font-weight: 500;
        }
        
        .results-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            text-align: right;
        }
        
        .results-table tr:last-child td {
            border-bottom: none;
        }
        
        .results-table tr:hover td {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .percentage {
            font-weight: 700;
        }
        
        .percentage.high {
            color: var(--success-color);
        }
        
        .percentage.medium {
            color: var(--warning-color);
        }
        
        .percentage.low {
            color: var(--accent-color);
        }
        
        .subject-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            background-color: #e3f2fd;
            color: var(--primary-color);
        }
        
        .no-results {
            text-align: center;
            padding: 30px;
            color: var(--light-text);
            font-size: 16px;
        }
        
        @media (max-width: 768px) {
            .student-profile {
                flex-direction: column;
                text-align: center;
            }
            
            .student-avatar {
                margin-left: 0;
                margin-bottom: 20px;
            }
            
            .student-class::before {
                right: 50%;
                transform: translate(50%, -50%);
            }
            
            .student-stats {
                justify-content: center;
                flex-wrap: wrap;
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
            <h1>ملف الطالب المشترك</h1>
        </div>
        
        <div class="student-profile">
            <?php if ($userData['show_avatar']): ?>
                <img src="../api/<?php echo !empty($userData['avatar']) ? sanitizeInput($userData['avatar']) : '<i class="fa-regular fa-user"></i>'; ?>" 
                     alt="صورة الطالب" class="student-avatar">
            <?php endif; ?>
            
            <div class="student-details">
                <h2 class="student-name"><?php echo sanitizeInput($userData['username']); ?></h2>
                <?php if ($userData['show_class']): ?>
                    <p class="student-class"><?php echo sanitizeInput($userData['student_class']); ?></p>
                <?php endif; ?>
                
                <div class="student-stats">
                    <?php if ($userData['show_rank']): ?>
                        <div class="stat-card">
                            <div class="stat-value">#<?php echo $userData['rank'] ?? '--'; ?></div>
                            <div class="stat-label">الترتيب</div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($userData['show_scores'] && !empty($stats)): ?>
                        <div class="stat-card">
                            <div class="stat-value">
                                <?php echo round(array_sum(array_column($stats, 'avg_percentage')) / count($stats)); ?>%
                            </div>
                            <div class="stat-label">المعدل العام</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $stats[0]['total_exams'] ?? 0; ?></div>
                            <div class="stat-label">عدد الاختبارات</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- عرض النتائج مع مراعاة الخصوصية -->
        <?php if ($userData['show_tests']): ?>
            <div class="results-section">
                <h3 class="section-title">أحدث النتائج</h3>
                
                <?php if (!empty($results)): ?>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>المادة</th>
                                <th>التاريخ</th>
                                <th>الدرجة</th>
                                <th>النسبة</th>
                                <th>المدة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result): ?>
                                <?php 
                                    $percentage = round(($result['score'] / $result['total_questions']) * 100);
                                    $percentageClass = '';
                                    if ($percentage >= 80) $percentageClass = 'high';
                                    elseif ($percentage >= 50) $percentageClass = 'medium';
                                    else $percentageClass = 'low';
                                ?>
                                <tr>
                                    <td><span class="subject-badge"><?php echo sanitizeInput($result['subject']); ?></span></td>
                                    <td><?php echo sanitizeInput($result['exam_date']); ?></td>
                                    <td><?php echo sanitizeInput($result['score']); ?>/<?php echo sanitizeInput($result['total_questions']); ?></td>
                                    <td><span class="percentage <?php echo $percentageClass; ?>"><?php echo $percentage; ?>%</span></td>
                                    <td><?php echo sanitizeInput($result['duration']); ?> دقائق</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        <p>لا توجد نتائج متاحة للعرض</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($userData['show_scores'] && !empty($stats)): ?>
            <div class="results-section">
                <h3 class="section-title">الإحصائيات حسب المادة</h3>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>المادة</th>
                            <th>عدد الاختبارات</th>
                            <th>إجمالي الإجابات الصحيحة</th>
                            <th>المعدل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats as $stat): ?>
                            <tr>
                                <td><span class="subject-badge"><?php echo sanitizeInput($stat['subject']); ?></span></td>
                                <td><?php echo sanitizeInput($stat['total_exams']); ?></td>
                                <td><?php echo sanitizeInput($stat['total_correct']); ?>/<?php echo sanitizeInput($stat['total_questions']); ?></td>
                                <td><span class="percentage"><?php echo round($stat['avg_percentage']); ?>%</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>