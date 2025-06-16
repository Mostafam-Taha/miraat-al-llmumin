<?php
require_once './php/config.php';

// تحديد الفترات الزمنية
$today = date('Y-m-d 00:00:00');
$one_week_ago = date('Y-m-d 00:00:00', strtotime('-1 week'));

try {
    // استعلامات الحصول على الإحصاءات
    $total_questions = $pdo->query("SELECT COUNT(*) as total FROM questions")->fetch()['total'];
    $today_questions = $pdo->prepare("SELECT COUNT(*) as count FROM questions WHERE added_date >= ?");
    $today_questions->execute([$today]);
    $today_count = $today_questions->fetch()['count'];
    
    $week_questions = $pdo->prepare("SELECT COUNT(*) as count FROM questions WHERE added_date >= ?");
    $week_questions->execute([$one_week_ago]);
    $week_count = $week_questions->fetch()['count'];
    
    // إحصاءات حسب المادة
    $subjects_stats = $pdo->query("
        SELECT subject, COUNT(*) as question_count 
        FROM questions 
        GROUP BY subject 
        ORDER BY question_count DESC
    ")->fetchAll();
    
    // إحصاءات حسب الدروس
    $lessons_stats = $pdo->query("
        SELECT subject, lesson_name, COUNT(*) as question_count 
        FROM questions 
        GROUP BY subject, lesson_name 
        ORDER BY subject, question_count DESC
        LIMIT 20
    ")->fetchAll();
    
    // إحصاءات حسب مستوى الصعوبة
    $difficulty_stats = $pdo->query("
        SELECT difficulty_level, COUNT(*) as question_count 
        FROM questions 
        GROUP BY difficulty_level 
        ORDER BY question_count DESC
    ")->fetchAll();
    
    // إحصاءات حسب نوع السؤال
    $type_stats = $pdo->query("
        SELECT question_type, COUNT(*) as question_count 
        FROM questions 
        GROUP BY question_type 
        ORDER BY question_count DESC
    ")->fetchAll();

} catch (PDOException $e) {
    die("حدث خطأ في جلب البيانات: " . $e->getMessage());
}


// استعلام للحصول على الإحصاءات الشهرية (آخر 30 يوم)
$monthly_stats = $pdo->query("
    SELECT 
        DATE(added_date) as day,
        COUNT(*) as question_count
    FROM questions
    WHERE added_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(added_date)
    ORDER BY day ASC
")->fetchAll();

// تحضير بيانات الرسم البياني الشهري
$monthly_labels = [];
$monthly_data = [];
foreach ($monthly_stats as $stat) {
    $monthly_labels[] = date('d M', strtotime($stat['day']));
    $monthly_data[] = $stat['question_count'];
}

// استعلام للحصول على توزيع الأسئلة حسب الأيام في الأسبوع
$weekday_stats = $pdo->query("
    SELECT 
        DAYNAME(added_date) as weekday,
        COUNT(*) as question_count
    FROM questions
    GROUP BY DAYNAME(added_date)
    ORDER BY FIELD(weekday, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')
")->fetchAll();

// تحضير بيانات أيام الأسبوع
$weekday_labels = [];
$weekday_data = [];
foreach ($weekday_stats as $stat) {
    $weekday_labels[] = $stat['weekday'];
    $weekday_data[] = $stat['question_count'];
}

// استعلام للحصول على توزيع الأسئلة حسب ساعات اليوم
$hourly_stats = $pdo->query("
    SELECT 
        HOUR(added_date) as hour,
        COUNT(*) as question_count
    FROM questions
    GROUP BY HOUR(added_date)
    ORDER BY hour ASC
")->fetchAll();

// تحضير بيانات ساعات اليوم
$hourly_labels = [];
$hourly_data = [];
for ($i = 0; $i < 24; $i++) {
    $hourly_labels[] = sprintf("%02d:00", $i);
    $hourly_data[$i] = 0;
}
foreach ($hourly_stats as $stat) {
        $hourly_data[$stat['hour']] = $stat['question_count'];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="منصة تعليمية متكاملة لمساعدة الطلاب على تنظيم الدراسة، وضع خطط دراسية ذكية، تحسين الأداء الأكاديمي، وحل اختبارات تفاعلية. ابدأ رحلتك نحو التفوق مع أدواتنا الذكية وتقارير الأداء المُفصّلة!">
    <meta name="keywords" content="تنظيم الدراسة, خطط دراسية, تحسين الأداء الدراسي, اختبارات تفاعلية, نصائح دراسية, جدول مذاكرة, مهارات التعلم, مراجعة الدروس, حلول امتحانات, منصة تعليمية, موارد دراسية, إدارة الوقت للطلاب, تعلم فعال, تقنيات الحفظ, التحضير للامتحانات, دروس مجانية, تمارين تدريبية, تقييم ذاتي, تعليم عن بعد, أدوات الدراسة الذكية">
    <meta name="author" content="ساحة العلم - رفيقك نحو التفوق الأكاديمي">
    <meta property="og:url" content="https://starlit-axolotl-737204.netlify.app">
    <meta property="og:image" content="https://starlit-axolotl-737204.netlify.app/image/logo/logo.png">
    <meta property="og:type" content="website">
    <meta name="twitter:image" content="https://starlit-axolotl-737204.netlify.app/image/logo/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="image/logo/book-open-reader-solid.svg" type="image/svg">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-B2Z6G6EY81"></script>
    <script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-B2Z6G6EY81');</script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/Dark Mode.css">
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-K73Z87CS');</script>
    <!-- End Google Tag Manager -->
    <title>إحصاءات الأسئلة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
            --info-color: #4895ef;
            --warning-color: #f72585;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 25px;
            overflow: hidden;
            background: white;
            position: relative;
            z-index: 1;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .stat-card .card-body {
            padding: 2rem;
            position: relative;
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .stat-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .stat-card h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0;
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.2rem 1.5rem;
            border-bottom: none;
        }
        
        .card-header h4 {
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .filter-section label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 700;
            background-color: #f8f9fa;
            border-top: none;
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
        }
        
        .progress-bar {
            background-color: var(--accent-color);
            border-radius: 5px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        
        .floating-buttons {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        
        .floating-btn {
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }
        
        .floating-btn:hover {
            transform: translateY(-5px) scale(1.1);
        }
        
        .scroll-to-top {
            background-color: var(--primary-color);
            color: white;
        }
        
        .print-btn {
            background-color: var(--success-color);
            color: white;
        }
        
        .export-btn {
            background-color: var(--warning-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .stat-card h2 {
                font-size: 2rem;
            }
            
            .stat-icon {
                font-size: 2.5rem;
            }
            
            .header {
                padding: 1.5rem 0;
            }
        }
        
        /* Animation classes */
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--accent-color);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="header animate__animated animate__fadeInDown">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 fw-bold mb-3 animate__animated animate__fadeInLeft">إحصاءات الأسئلة</h1>
                    <p class="lead mb-0 animate__animated animate__fadeInLeft animate__delay-1s">نظرة شاملة على قاعدة بيانات الأسئلة التعليمية</p>
                </div>
                <div class="col-md-4 text-start animate__animated animate__fadeInRight">
                    <i class="bi bi-bar-chart-line" style="font-size: 5rem; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container py-3">
        <!-- فلترة البيانات -->
        <div class="filter-section animate__animated animate__fadeIn animate__delay-1s">
            <h5 class="mb-4"><i class="bi bi-funnel me-2"></i>تصفية البيانات</h5>
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="subject" class="form-label">المادة:</label>
                    <select class="form-select" id="subject" name="subject">
                        <option value="">كل المواد</option>
                        <?php
                        $subjects = $pdo->query("SELECT DISTINCT subject FROM questions ORDER BY subject")->fetchAll();
                        foreach ($subjects as $subject) {
                            $selected = (isset($_GET['subject']) && $_GET['subject'] == $subject['subject']) ? 'selected' : '';
                            echo "<option value='{$subject['subject']}' $selected>{$subject['subject']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="lesson" class="form-label">الدرس:</label>
                    <select class="form-select" id="lesson" name="lesson">
                        <option value="">كل الدروس</option>
                        <?php
                        if (isset($_GET['subject'])) {
                            $lessons = $pdo->prepare("SELECT DISTINCT lesson_name FROM questions WHERE subject = ? ORDER BY lesson_name");
                            $lessons->execute([$_GET['subject']]);
                            $lessons = $lessons->fetchAll();
                            
                            foreach ($lessons as $lesson) {
                                $selected = (isset($_GET['lesson']) && $_GET['lesson'] == $lesson['lesson_name']) ? 'selected' : '';
                                echo "<option value='{$lesson['lesson_name']}' $selected>{$lesson['lesson_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">نوع السؤال:</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">كل الأنواع</option>
                        <?php
                        $types = $pdo->query("SELECT DISTINCT question_type FROM questions ORDER BY question_type")->fetchAll();
                        foreach ($types as $type) {
                            $selected = (isset($_GET['type'])) && $_GET['type'] == $type['question_type'] ? 'selected' : '';
                            echo "<option value='{$type['question_type']}' $selected>{$type['question_type']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="difficulty" class="form-label">مستوى الصعوبة:</label>
                    <select class="form-select" id="difficulty" name="difficulty">
                        <option value="">كل المستويات</option>
                        <?php
                        $difficulties = $pdo->query("SELECT DISTINCT difficulty_level FROM questions WHERE difficulty_level IS NOT NULL ORDER BY difficulty_level")->fetchAll();
                        foreach ($difficulties as $difficulty) {
                            $selected = (isset($_GET['difficulty'])) && $_GET['difficulty'] == $difficulty['difficulty_level'] ? 'selected' : '';
                            echo "<option value='{$difficulty['difficulty_level']}' $selected>{$difficulty['difficulty_level']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2"><i class="bi bi-funnel me-1"></i>تصفية</button>
                    <a href="question_stats.php" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>إعادة تعيين</a>
                </div>
            </form>
        </div>
        
        <!-- بطاقات الإحصاءات -->
        <div class="row animate__animated animate__fadeIn animate__delay-1s">
            <div class="col-md-4">
                <div class="stat-card" style="background: linear-gradient(135deg, #4361ee, #3f37c9);">
                    <div class="card-body text-center">
                        <i class="bi bi-question-circle stat-icon"></i>
                        <h3>إجمالي الأسئلة</h3>
                        <h2><?php echo number_format($total_questions); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="background: linear-gradient(135deg, #4cc9f0, #4895ef);">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-week stat-icon"></i>
                        <h3>آخر أسبوع</h3>
                        <h2><?php echo number_format($week_count); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="background: linear-gradient(135deg, #f72585, #b5179e);">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-day stat-icon"></i>
                        <h3>آخر يوم</h3>
                        <h2><?php echo number_format($today_count); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-sm btn-outline-primary refresh-btn" title="تحديث البيانات">
            <i class="bi bi-arrow-repeat"></i> تحديث البيانات
        </button>
        
        <!-- الرسوم البيانية -->
        <div class="row mt-4">
            <div class="col-md-6 animate__animated animate__fadeInLeft">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><i class="bi bi-pie-chart me-2"></i>توزيع الأسئلة حسب المادة</h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="subjectChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 animate__animated animate__fadeInRight">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><i class="bi bi-bar-chart me-2"></i>توزيع الأسئلة حسب مستوى الصعوبة</h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="difficultyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الرسوم البيانية الزمنية -->
        <div class="row mt-4 animate__animated animate__fadeInUp">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><i class="bi bi-calendar-range me-2"></i>الإحصاءات الزمنية</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="chart-container mb-4" style="height: 300px;">
                                    <h5 class="text-center mb-3">عدد الأسئلة المضافة يومياً (آخر 30 يوم)</h5>
                                    <canvas id="monthlyChart"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="chart-container mb-4" style="height: 300px;">
                                    <h5 class="text-center mb-3">توزيع الأسئلة حسب أيام الأسبوع</h5>
                                    <canvas id="weekdayChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="chart-container mb-4" style="height: 300px;">
                                    <h5 class="text-center mb-3">توزيع الأسئلة حسب ساعات اليوم</h5>
                                    <canvas id="hourlyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- إحصاءات حسب المادة -->
        <div class="row mt-4">
            <div class="col-md-6 animate__animated animate__fadeInUp">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><i class="bi bi-book me-2"></i>توزيع الأسئلة حسب المادة</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>المادة</th>
                                        <th>عدد الأسئلة</th>
                                        <th>النسبة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects_stats as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['subject']); ?></td>
                                        <td><?php echo number_format($stat['question_count']); ?></td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?php echo ($stat['question_count'] / $total_questions) * 100; ?>%" 
                                                     aria-valuenow="<?php echo ($stat['question_count'] / $total_questions) * 100; ?>" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                    <?php echo round(($stat['question_count'] / $total_questions) * 100, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- إحصاءات حسب مستوى الصعوبة -->
            <div class="col-md-6 animate__animated animate__fadeInUp">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><i class="bi bi-speedometer2 me-2"></i>توزيع الأسئلة حسب مستوى الصعوبة</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>مستوى الصعوبة</th>
                                        <th>عدد الأسئلة</th>
                                        <th>النسبة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($difficulty_stats as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['difficulty_level'] ?? 'غير محدد'); ?></td>
                                        <td><?php echo number_format($stat['question_count']); ?></td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?php echo ($stat['question_count'] / $total_questions) * 100; ?>%" 
                                                     aria-valuenow="<?php echo ($stat['question_count'] / $total_questions) * 100; ?>" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                    <?php echo round(($stat['question_count'] / $total_questions) * 100, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- إحصاءات حسب الدروس -->
        <div class="row mt-4 animate__animated animate__fadeInUp">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><i class="bi bi-collection me-2"></i>أكثر الدروس من حيث عدد الأسئلة</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>المادة</th>
                                        <th>الدرس</th>
                                        <th>عدد الأسئلة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lessons_stats as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['subject']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['lesson_name']); ?></td>
                                        <td><?php echo number_format($stat['question_count']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- إحصاءات حسب نوع السؤال -->
        <div class="row mt-4 animate__animated animate__fadeInUp">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0"><i class="bi bi-tags me-2"></i>توزيع الأسئلة حسب النوع</h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container mb-4">
                            <canvas id="typeChart"></canvas>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>نوع السؤال</th>
                                        <th>عدد الأسئلة</th>
                                        <th>النسبة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($type_stats as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['question_type']); ?></td>
                                        <td><?php echo number_format($stat['question_count']); ?></td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?php echo ($stat['question_count'] / $total_questions) * 100; ?>%" 
                                                     aria-valuenow="<?php echo ($stat['question_count'] / $total_questions) * 100; ?>" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                    <?php echo round(($stat['question_count'] / $total_questions) * 100, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- الأزرار العائمة -->
    <div class="floating-buttons">
        <button class="floating-btn scroll-to-top" title="العودة للأعلى">
            <i class="bi bi-arrow-up"></i>
        </button>
        <button class="floating-btn print-btn" title="طباعة التقرير">
            <i class="bi bi-printer"></i>
        </button>
        <button class="floating-btn export-btn" title="تصدير البيانات">
            <i class="bi bi-download"></i>
        </button>
        <button class="floating-btn chat-btn" title="دردشة">
            <a href="./qdl-o.php">
                <i class="bi bi-chat-dots-fill"></i>
            </a>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // جعل قائمة الدروس تعتمد على المادة المختارة
        document.getElementById('subject').addEventListener('change', function() {
            const subject = this.value;
            const lessonSelect = document.getElementById('lesson');
            
            if (subject) {
                fetch(`get_lessons.php?subject=${encodeURIComponent(subject)}`)
                    .then(response => response.json())
                    .then(data => {
                        lessonSelect.innerHTML = '<option value="">كل الدروس</option>';
                        data.forEach(lesson => {
                            const option = document.createElement('option');
                            option.value = lesson.lesson_name;
                            option.textContent = lesson.lesson_name;
                            lessonSelect.appendChild(option);
                        });
                    });
            } else {
                lessonSelect.innerHTML = '<option value="">كل الدروس</option>';
            }
        });

        // إنشاء الرسوم البيانية
        document.addEventListener('DOMContentLoaded', function() {
            // بيانات المواد
            const subjectLabels = <?php echo json_encode(array_column($subjects_stats, 'subject')); ?>;
            const subjectData = <?php echo json_encode(array_column($subjects_stats, 'question_count')); ?>;
            
            // بيانات مستوى الصعوبة
            const difficultyLabels = <?php echo json_encode(array_map(function($item) { 
                return $item['difficulty_level'] ?? 'غير محدد'; 
            }, $difficulty_stats)); ?>;
            const difficultyData = <?php echo json_encode(array_column($difficulty_stats, 'question_count')); ?>;
            
            // بيانات نوع السؤال
            const typeLabels = <?php echo json_encode(array_column($type_stats, 'question_type')); ?>;
            const typeData = <?php echo json_encode(array_column($type_stats, 'question_count')); ?>;
            
            // ألوان الرسوم البيانية
            const colors = [
                '#4361ee', '#3f37c9', '#4895ef', '#4cc9f0', 
                '#f72585', '#b5179e', '#7209b7', '#560bad',
                '#480ca8', '#3a0ca3', '#3f37c9', '#4361ee',
                '#4895ef', '#4cc9f0', '#f72585', '#b5179e'
            ];
            
            // رسم بياني للمواد
            const subjectCtx = document.getElementById('subjectChart').getContext('2d');
            new Chart(subjectCtx, {
                type: 'doughnut',
                data: {
                    labels: subjectLabels,
                    datasets: [{
                        data: subjectData,
                        backgroundColor: colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            rtl: true
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            // رسم بياني لمستوى الصعوبة
            const difficultyCtx = document.getElementById('difficultyChart').getContext('2d');
            new Chart(difficultyCtx, {
                type: 'bar',
                data: {
                    labels: difficultyLabels,
                    datasets: [{
                        label: 'عدد الأسئلة',
                        data: difficultyData,
                        backgroundColor: colors.slice(2),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // رسم بياني لأنواع الأسئلة
            const typeCtx = document.getElementById('typeChart').getContext('2d');
            new Chart(typeCtx, {
                type: 'pie',
                data: {
                    labels: typeLabels,
                    datasets: [{
                        data: typeData,
                        backgroundColor: colors.slice(4),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            rtl: true
                        }
                    }
                }
            });
            
            // زر العودة للأعلى
            const scrollToTopBtn = document.querySelector('.scroll-to-top');
            scrollToTopBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // زر الطباعة
            document.querySelector('.print-btn').addEventListener('click', () => {
                window.print();
            });
            
            // زر التصدير
            document.querySelector('.export-btn').addEventListener('click', () => {
                // يمكنك هنا إضافة كود لتصدير البيانات كملف Excel أو PDF
                alert('سيتم تصدير البيانات قريبًا');
            });
            
            // إظهار/إخفاء زر العودة للأعلى عند التمرير
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    scrollToTopBtn.style.display = 'flex';
                } else {
                    scrollToTopBtn.style.display = 'none';
                }
            });
            
            // إضافة تأثيرات للعناصر عند التمرير
            const animateElements = document.querySelectorAll('.animate__animated');
            
            const animateOnScroll = () => {
                animateElements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;
                    
                    if (elementPosition < windowHeight - 100) {
                        element.classList.add(element.classList[1]);
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // تشغيل مرة أولى عند التحميل
        });


        // إضافة تأثيرات للعناصر عند التمرير
            const animateElements = document.querySelectorAll('.animate__animated');
            
            const animateOnScroll = () => {
                animateElements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;
                    
                    if (elementPosition < windowHeight - 100) {
                        element.classList.add(element.classList[1]);
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // تشغيل مرة أولى عند التحميل
            
            // تحديث الصفحة كل 30 ثانية (30000 ميلي ثانية)
            setTimeout(function(){
                location.reload();
            }, 90000);



            // رسم بياني شهري
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($monthly_labels); ?>,
                    datasets: [{
                        label: 'عدد الأسئلة',
                        data: <?php echo json_encode($monthly_data); ?>,
                        backgroundColor: 'rgba(67, 97, 238, 0.2)',
                        borderColor: 'rgba(67, 97, 238, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `عدد الأسئلة: ${context.raw}`;
                                }
                            }
                        }
                    }
                }
            });

            // رسم بياني لأيام الأسبوع
            const weekdayCtx = document.getElementById('weekdayChart').getContext('2d');
            new Chart(weekdayCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($weekday_labels); ?>,
                    datasets: [{
                        label: 'عدد الأسئلة',
                        data: <?php echo json_encode($weekday_data); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // رسم بياني لساعات اليوم
            const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
            new Chart(hourlyCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($hourly_labels); ?>,
                    datasets: [{
                        label: 'عدد الأسئلة',
                        data: <?php echo json_encode($hourly_data); ?>,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            document.querySelector('.refresh-btn').addEventListener('click', function() {
                location.reload();
            });
    </script>
</body>
</html>