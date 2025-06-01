<?php
require_once '../php/config.php';
session_start();

// جلب المواد المتاحة
$subjects = [];
$stmt = $pdo->query("SELECT DISTINCT subject FROM exam_results");
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

// جلب الصفوف المتاحة
$classes = [];
$stmt = $pdo->query("SELECT DISTINCT student_class FROM users");
$classes = $stmt->fetchAll(PDO::FETCH_COLUMN);

// تحديد التصنيف الحالي
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : 'general';

// جلب الأوائل حسب التصنيف المختار
$topStudents = [];
$title = "التصنيف العام لأول 10 طلاب";

switch ($category) {
    case 'by_class':
        $title = "أوائل كل صف حسب المجموع الكلي";
        $query = "
            SELECT 
                er.*, 
                u.username,
                u.student_class,
                (@rank := IF(@prev_class = u.student_class, 
                    IF(@prev_score = er.score, @rank, @rank + 1), 
                    1)) as rank,
                (@prev_score := er.score) as score_calc,
                (@prev_class := u.student_class) as class_calc
            FROM 
                exam_results er
            JOIN 
                users u ON er.user_id = u.id,
                (SELECT @rank := 0, @prev_score := -1, @prev_class := '') as r
            ORDER BY 
                u.student_class, er.score DESC, er.exam_date ASC
        ";
        $stmt = $pdo->query($query);
        $allStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // تجميع أول 10 طلاب لكل صف
        $grouped = [];
        foreach ($allStudents as $student) {
            $class = $student['student_class'];
            if (!isset($grouped[$class]) || count($grouped[$class]) < 10) {
                $grouped[$class][] = $student;
            }
        }
        
        // دمج جميع الطلاب في مصفوفة واحدة للعرض
        foreach ($grouped as $classStudents) {
            $topStudents = array_merge($topStudents, $classStudents);
        }
        break;
        
    case 'by_month':
        $title = "أوائل الشهر حسب المجموع الكلي";
        $month = date('Y-m');
        $query = "
            SELECT 
                er.*, 
                u.username,
                u.student_class,
                (@rank := IF(@prev_score = er.score, @rank, @rank + 1)) as rank,
                (@prev_score := er.score) as score_calc
            FROM 
                exam_results er
            JOIN 
                users u ON er.user_id = u.id,
                (SELECT @rank := 0, @prev_score := -1) as r
            WHERE 
                DATE_FORMAT(er.exam_date, '%Y-%m') = '$month'
            ORDER BY 
                er.score DESC, er.exam_date ASC
            LIMIT 10
        ";
        $stmt = $pdo->query($query);
        $topStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
        
    case 'by_subject':
        $title = "أوائل كل مادة حسب المجموع الكلي";
        $query = "
            SELECT 
                er.*, 
                u.username,
                u.student_class,
                (@rank := IF(@prev_subject = er.subject, 
                    IF(@prev_score = er.score, @rank, @rank + 1), 
                    1)) as rank,
                (@prev_score := er.score) as score_calc,
                (@prev_subject := er.subject) as subject_calc
            FROM 
                exam_results er
            JOIN 
                users u ON er.user_id = u.id,
                (SELECT @rank := 0, @prev_score := -1, @prev_subject := '') as r
            ORDER BY 
                er.subject, er.score DESC, er.exam_date ASC
        ";
        $stmt = $pdo->query($query);
        $allStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // تجميع أول 10 طلاب لكل مادة
        $grouped = [];
        foreach ($allStudents as $student) {
            $subject = $student['subject'];
            if (!isset($grouped[$subject]) || count($grouped[$subject]) < 10) {
                $grouped[$subject][] = $student;
            }
        }
        
        // دمج جميع الطلاب في مصفوفة واحدة للعرض
        foreach ($grouped as $subjectStudents) {
            $topStudents = array_merge($topStudents, $subjectStudents);
        }
        break;
        
    default: // general
        $title = "أول 10 طلاب حسب المجموع الكلي";
        $query = "
            SELECT 
                er.*, 
                u.username,
                u.student_class,
                (@rank := IF(@prev_score = er.score, @rank, @rank + 1)) as rank,
                (@prev_score := er.score) as score_calc
            FROM 
                exam_results er
            JOIN 
                users u ON er.user_id = u.id,
                (SELECT @rank := 0, @prev_score := -1) as r
            ORDER BY 
                er.score DESC, er.exam_date ASC
            LIMIT 10
        ";
        $stmt = $pdo->query($query);
        $topStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// جلب إحصائيات عامة
$statsQuery = "
    SELECT 
        COUNT(DISTINCT user_id) as total_students,
        COUNT(*) as total_exams,
        SUM(score) as total_points
    FROM exam_results
";
$statsStmt = $pdo->query($statsQuery);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحة الأوائل حسب المجموع الكلي</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #4CAF50;
            --accent: #FF9800;
            --light: #f8f9fa;
            --dark: #343a40;
            --gold: #FFD700;
            --silver: #C0C0C0;
            --bronze: #CD7F32;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: #333;
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary) 0%, #1a2530 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        h1 {
            font-size: 2.8rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }
        
        .subtitle {
            font-size: 1.4rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .tabs {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            padding: 25px 20px;
            background: var(--light);
            border-bottom: 2px solid #eaeaea;
        }
        
        .tab {
            padding: 14px 32px;
            background: white;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
        }
        
        .tab:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            border-color: var(--secondary);
        }
        
        .tab.active {
            background: var(--secondary);
            color: white;
            border-color: var(--secondary);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.3);
        }
        
        .stats-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 25px;
            padding: 35px 20px;
            background: linear-gradient(135deg, #f8fff8 0%, #f0f8ff 100%);
        }
        
        .stat-box {
            background: white;
            border-radius: 18px;
            padding: 28px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            flex: 1;
            min-width: 250px;
            transition: transform 0.3s ease;
            border-top: 5px solid var(--secondary);
        }
        
        .stat-box:hover {
            transform: translateY(-10px);
        }
        
        .stat-icon {
            font-size: 3.2rem;
            color: var(--secondary);
            margin-bottom: 20px;
        }
        
        .stat-value {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary);
            margin: 15px 0;
        }
        
        .stat-label {
            font-size: 1.3rem;
            color: #666;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.2rem;
            color: var(--primary);
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 20px;
        }
        
        .section-title::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }
        
        .top-students-container {
            overflow-x: auto;
            margin-bottom: 50px;
        }
        
        .top-students-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 800px;
        }
        
        .top-students-table th {
            background: var(--primary);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: bold;
            position: sticky;
            top: 0;
        }
        
        .top-students-table td {
            padding: 18px;
            text-align: center;
            border-bottom: 1px solid #eee;
            font-size: 1.1rem;
        }
        
        .top-students-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .top-students-table tr:hover {
            background-color: #f0fff4;
        }
        
        .rank-1 { background: linear-gradient(to right, #fffdf6, #fff9e6); }
        .rank-2 { background: linear-gradient(to right, #f9f9f9, #f0f0f0); }
        .rank-3 { background: linear-gradient(to right, #fdf6f0, #f9ebe0); }
        
        .medal {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 1.4rem;
            font-weight: bold;
            margin: 0 auto;
        }
        
        .gold {
            background: radial-gradient(circle at 30% 30%, var(--gold), #daa520);
            color: white;
            box-shadow: 0 4px 12px rgba(218, 165, 32, 0.4);
        }
        
        .silver {
            background: radial-gradient(circle at 30% 30%, var(--silver), #a9a9a9);
            color: white;
            box-shadow: 0 4px 12px rgba(169, 169, 169, 0.4);
        }
        
        .bronze {
            background: radial-gradient(circle at 30% 30%, var(--bronze), #8b4513);
            color: white;
            box-shadow: 0 4px 12px rgba(139, 69, 19, 0.4);
        }
        
        .score-badge {
            display: inline-block;
            padding: 8px 18px;
            background: var(--secondary);
            color: white;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
        }
        
        .actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 25px;
            margin-top: 50px;
        }
        
        .action-btn {
            padding: 18px 40px;
            border: none;
            border-radius: 50px;
            font-size: 1.3rem;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            min-width: 280px;
            justify-content: center;
        }
        
        .action-btn:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }
        
        .export-pdf {
            background: linear-gradient(135deg, #e53935 0%, #b71c1c 100%);
            color: white;
        }
        
        .export-excel {
            background: linear-gradient(135deg, #43a047 0%, #1b5e20 100%);
            color: white;
        }
        
        .send-certificates {
            background: linear-gradient(135deg, #0288d1 0%, #01579b 100%);
            color: white;
        }
        
        .notification {
            background: linear-gradient(135deg, #ff9800 0%, #e65100 100%);
            color: white;
        }
        
        footer {
            text-align: center;
            padding: 25px;
            background: var(--primary);
            color: white;
            font-size: 1.1rem;
        }
        
        @media (max-width: 992px) {
            .stat-box {
                min-width: 100%;
            }
            
            .action-btn {
                min-width: 100%;
            }
            
            h1 {
                font-size: 2.2rem;
            }
            
            .subtitle {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 768px) {
            .tabs {
                flex-direction: column;
                align-items: center;
            }
            
            .tab {
                width: 100%;
                justify-content: center;
            }
            
            .top-students-table {
                font-size: 0.9rem;
            }
            
            .top-students-table th, 
            .top-students-table td {
                padding: 12px 8px;
            }
            
            .action-btn {
                padding: 15px;
                font-size: 1.1rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-trophy"></i> لوحة الأوائل</h1>
            <div class="subtitle">تصنيف الطلاب حسب المجموع الكلي في الاختبارات</div>
        </header>
        
        <div class="tabs">
            <div class="tab <?= $category == 'general' ? 'active' : '' ?>" onclick="changeCategory('general')">
                <i class="fas fa-globe"></i> التصنيف العام
            </div>
            <div class="tab <?= $category == 'by_class' ? 'active' : '' ?>" onclick="changeCategory('by_class')">
                <i class="fas fa-users"></i> أوائل كل صف
            </div>
            <div class="tab <?= $category == 'by_month' ? 'active' : '' ?>" onclick="changeCategory('by_month')">
                <i class="fas fa-calendar"></i> أوائل الشهر
            </div>
            <div class="tab <?= $category == 'by_subject' ? 'active' : '' ?>" onclick="changeCategory('by_subject')">
                <i class="fas fa-book"></i> أوائل كل مادة
            </div>
        </div>
        
        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-value"><?= number_format($stats['total_students']) ?></div>
                <div class="stat-label">عدد الطلاب</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-value"><?= number_format($stats['total_exams']) ?></div>
                <div class="stat-label">عدد الاختبارات</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-value"><?= number_format($stats['total_points']) ?></div>
                <div class="stat-label">إجمالي النقاط</div>
            </div>
        </div>
        
        <div class="content">
            <h2 class="section-title"><?= $title ?></h2>
            
            <div class="top-students-container">
                <table class="top-students-table">
                    <thead>
                        <tr>
                            <th>الترتيب</th>
                            <th>اسم الطالب</th>
                            <th>الصف</th>
                            <th>المادة</th>
                            <th>الاختبار</th>
                            <th>المجموع الكلي</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topStudents as $student): ?>
                        <tr class="rank-<?= $student['rank'] <= 3 ? $student['rank'] : '' ?>">
                            <td>
                                <?php if ($student['rank'] == 1): ?>
                                    <div class="medal gold">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                <?php elseif ($student['rank'] == 2): ?>
                                    <div class="medal silver">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                <?php elseif ($student['rank'] == 3): ?>
                                    <div class="medal bronze">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                <?php else: ?>
                                    <div style="font-size: 1.4rem; font-weight: bold;">
                                        <?= $student['rank'] ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($student['username']) ?></td>
                            <td><?= htmlspecialchars($student['student_class']) ?></td>
                            <td><?= htmlspecialchars($student['subject']) ?></td>
                            <td><?= htmlspecialchars($student['question_type']) ?></td>
                            <td>
                                <span class="score-badge">
                                    <?= $student['score'] ?> نقطة
                                </span>
                            </td>
                            <td><?= date('Y-m-d', strtotime($student['exam_date'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="actions">
                <button class="action-btn export-pdf" onclick="exportPDF()">
                    <i class="fas fa-file-pdf"></i> تصدير PDF
                </button>
                <button class="action-btn export-excel" onclick="exportExcel()">
                    <i class="fas fa-file-excel"></i> تصدير Excel
                </button>
                <button class="action-btn send-certificates" onclick="sendCertificates()">
                    <i class="fas fa-award"></i> إرسال الشهادات
                </button>
                <button class="action-btn notification" onclick="sendNotifications()">
                    <i class="fas fa-bell"></i> إرسال إشعارات
                </button>
            </div>
        </div>
        
        <footer>
            <p>جميع الحقوق محفوظة &copy; <?= date('Y') ?> - نظام إدارة الاختبارات التعليمية</p>
        </footer>
    </div>

    <script>
        // تغيير التصنيف
        function changeCategory(category) {
            window.location.href = `top_students.php?category=${category}`;
        }
        
        // تصدير PDF
        function exportPDF() {
            alert('جاري تصدير بيانات الأوائل إلى ملف PDF...');
            // هنا سيتم وضع كود التصدير الفعلي
        }
        
        // تصدير Excel
        function exportExcel() {
            alert('جاري تصدير بيانات الأوائل إلى ملف Excel...');
            // هنا سيتم وضع كود التصدير الفعلي
        }
        
        // إرسال الشهادات
        function sendCertificates() {
            if (confirm('هل تريد إرسال شهادات التقدير للطلاب الأوائل؟')) {
                alert('سيتم إرسال الشهادات للطلاب الموجودين في القائمة.');
                // هنا سيتم وضع كود الإرسال الفعلي
            }
        }
        
        // إرسال إشعارات
        function sendNotifications() {
            if (confirm('هل تريد إرسال إشعارات للطلاب الأوائل؟')) {
                alert('تم إرسال الإشعارات للطلاب بنجاح!');
                // هنا سيتم وضع كود الإرسال الفعلي
            }
        }
    </script>
</body>
</html>