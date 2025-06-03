<?php
require_once '../php/config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// جلب بيانات الطالب
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$userData = $userStmt->fetch(PDO::FETCH_ASSOC);

// جلب جميع نتائج الطالب مع إمكانية التصفية
$subject_filter = isset($_GET['subject']) ? $_GET['subject'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$query = "SELECT * FROM exam_results WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if (!empty($subject_filter)) {
    $query .= " AND subject = ?";
    $params[] = $subject_filter;
}

if (!empty($type_filter)) {
    $query .= " AND question_type = ?";
    $params[] = $type_filter;
}

if (!empty($date_from)) {
    $query .= " AND exam_date >= ?";
    $params[] = $date_from . ' 00:00:00';
}

if (!empty($date_to)) {
    $query .= " AND exam_date <= ?";
    $params[] = $date_to . ' 23:59:59';
}

$query .= " ORDER BY exam_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب المواد المختلفة للفلتر
$subjectsStmt = $pdo->prepare("SELECT DISTINCT subject FROM exam_results WHERE user_id = ?");
$subjectsStmt->execute([$_SESSION['user_id']]);
$subjects = $subjectsStmt->fetchAll(PDO::FETCH_COLUMN);

// جلب أنواع الاختبارات المختلفة للفلتر
$typesStmt = $pdo->prepare("SELECT DISTINCT question_type FROM exam_results WHERE user_id = ?");
$typesStmt->execute([$_SESSION['user_id']]);
$types = $typesStmt->fetchAll(PDO::FETCH_COLUMN);

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

// حساب التقييم العام للطالب
$overallAvg = 0;
if (!empty($stats)) {
    $total = 0;
    foreach ($stats as $stat) {
        $total += $stat['avg_percentage'];
    }
    $overallAvg = $total / count($stats);
}

$performanceRating = '';
$ratingColor = '';
if ($overallAvg >= 80) {
    $performanceRating = 'ممتاز';
    $ratingColor = '#28a745';
} elseif ($overallAvg >= 60) {
    $performanceRating = 'جيد جداً';
    $ratingColor = '#17a2b8';
} elseif ($overallAvg >= 50) {
    $performanceRating = 'متوسط';
    $ratingColor = '#ffc107';
} else {
    $performanceRating = 'ضعيف';
    $ratingColor = '#dc3545';
}

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="../image/logo/book-open-reader-solid.svg" type="image/svg">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-B2Z6G6EY81"></script>
    <script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-B2Z6G6EY81');</script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/study.css">
    <link rel="stylesheet" href="../css/study-mat.css">
    <link rel="stylesheet" href="../css/Dark Mode.css">
    <link rel="stylesheet" href="../css/profile.css">
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-K73Z87CS');</script>
    <!-- End Google Tag Manager -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <title>Sahat Al-llm - Profile </title>
    <style>
        .stat-card {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: space-between;
        }
        .stat-card-row {
            display: flex;
            /* align-items: center; */
            justify-content: space-between;
            /* margin-bottom: 10px; */
        }

        .stat-card .stat-icon {
            background-color: #6f42c11a;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-card .stat-icon i {
            color: #6f42c1;
            font-size: 20px;
        }

        .stat-card .t-icon {
            background-color: #6f42c11a;
        }

        .stat-card .t-icon .fa-check-circle {
            color: #1d3b53;
        }

        .stat-card .s-icon {
            background-color: #f7c32e26;
        }

        .stat-card .s-icon .fa-percentage {
            color: #f8c32f;
        }


        /* إضافة هذه الأنماط إلى ملف CSS الخاص بك */

        @media (max-width: 860px) {
            .dashboard-header {
                display: flex;
                flex-direction: column;
                align-items: center;
                min-width: 50px;
                width: 75px;
            }
            
            .dashboard-logo a img {
                margin: 0;
            }

            .dashboard-header .dashboard-logo {
                text-align: center;
                margin-bottom: 20px;
                display: flex;
                flex-direction: column !important;
                align-items: center;
            }

            .dashboard-logo .logo-text {
                display: none;
            }
            
            .navbar-list {
                display: flex;
                flex-direction: column;
                justify-content: space-around;
                width: 100%;
                padding: 0;
            }
            
            .navbar-item {
                margin: 0;
            }
            
            .navbar-link {
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 10px;
                font-size: 0; /* إخفاء النص */
            }
            
            .navbar-link::before {
                font-family: "Font Awesome 5 Free";
                font-weight: 900;
                font-size: 1.2rem;
                margin-bottom: 5px;
            }
            
            .navbar-list .navbar-item:nth-child(1) .navbar-link::before {
                content: "\f015"; /* أيقونة الرئيسية */
            }
            
            .navbar-list .navbar-item:nth-child(2) .navbar-link::before {
                content: "\f19d"; /* أيقونة الدراسة */
            }
            
            .navbar-list .navbar-item:nth-child(3) .navbar-link::before {
                content: "\f044"; /* أيقونة الاختبارات */
            }
            
            .navbar-list .navbar-item:nth-child(4) .navbar-link::before {
                content: "\f007"; /* أيقونة الملف الشخصي */
            }
        }

        @media (max-width: 725px) {
            .dashboard-header {
                min-width: 65px;
            }

            .stats-container {
                display: grid;
                grid-template-columns: repeat(1, 1fr);
                gap: 5px;
                margin: 30px 0;
            }
        }

        @media (max-width: 930px) {
            .dashboard-main {
                padding: 10px;
                width: 100%;
            }

            .results-table-container {
                overflow-x: auto;
                width: 100%;
                -webkit-overflow-scrolling: touch; /* لسلاسة التمرير على الأجهزة المحمولة */
            }
            
            .results-table {
                min-width: 800px; /* عرض أكبر من الحاوية لفرض شريط التمرير */
                width: 100%;
            }
            
            .results-table th,
            .results-table td {
                white-space: nowrap; /* منع التفاف النص */
                padding: 8px 12px; /* تعديل المساحات لتناسب الشاشات الصغيرة */
            }
            
            .results-table .btn {
                padding: 5px 8px; /* تصغير حجم الأزرار */
                font-size: 12px;
            }
            
            .results-table .badge {
                font-size: 12px; /* تصغير حجم الشارات */
                padding: 4px 8px;
            }
        }


    </style>
</head>
<body>
    <section class="heart-profile">
        <section class="dashboard-header">
            <div class="dashboard-logo">
                <a href="../index.html" class="logo-link">
                    <img src="../image/logo/book-open-reader-solid.svg" alt="Sahat Al-llm Logo" class="logo-img">
                    <h1 class="logo-text">Sahat al-llm</h1>
                </a>
            </div>
            <nav class="dashboard-navbar">
                <ul class="navbar-list">
                    <li class="navbar-item"><a href="../index.php" class="navbar-link">الرئيسية</a></li>
                    <li class="navbar-item"><a href="../study/study.php" class="navbar-link">الدراسة</a></li>
                    <li class="navbar-item"><a href="../exam/exam.php" class="navbar-link">الاختبارات</a></li>
                    <li class="navbar-item"><a href="../profile/profile.php" class="navbar-link">الملف الشخصي</a></li>
                </ul>
            </nav>
        </section>
        <!-- Start Profile -->
        <main class="dashboard-main">
            <div class="container">
                <!-- <h1><i class="fas fa-chart-line"></i> لوحة نتائج الطالب</h1> -->
                <!-- قسم معلومات الطالب -->
                <div class="student-profile-container">
                    <!-- بطاقة ملف الطالب -->
                    <div class="student-profile-card">
                        <!-- قسم الصورة الشخصية -->
                        <div class="avatar-section">
                            <div class="student-avatar" id="avatar-container">
                                <?php if(!empty($userData['avatar'])): ?>
                                    <img src="<?= htmlspecialchars($userData['avatar']) ?>" alt="صورة المستخدم" class="avatar-image">
                                <?php else: ?>
                                    <div class="avatar-initial"><?= substr($userData['username'], 0, 1) ?></div>
                                <?php endif; ?>
                                <input type="file" id="avatar-upload" accept="image/*" class="avatar-upload-input">
                                <div class="avatar-upload-hint">
                                    <i class="fas fa-camera"></i>
                                    <span>تغيير الصورة</span>
                                </div>
                            </div>

                            <h2 class="student-name"><?= htmlspecialchars($userData['username']) ?></h2>
                            <div class="item">
                                <div class="icon-i"><i class="fas fa-envelope"></i></div>
                                <div class="d-content">
                                    <div class="d-value"><?= htmlspecialchars($userData['email']) ?></div>
                                </div>
                            </div>
                            
                            <div class="performance-rating">
                                <div class="rating-circle" style="--rating-color: <?= $ratingColor ?>; --rating-percent: <?= $overallAvg ?>%">
                                    <div class="rating-value"><?= round($overallAvg, 1) ?>%</div>
                                </div>
                                <div class="rating-text" style="background-color: <?= $ratingColor ?>"><?= $performanceRating ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- إحصائيات الطالب -->
                <h2><i class="fas fa-chart-pie"></i> إحصائيات الأداء</h2>
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-card-row">
                            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                            <div class="stat-title">عدد الاختبارات</div>
                        </div>
                        <div class="stat-value"><?= count($results) ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-row">
                            <div class="stat-icon t-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-title">إجابات صحيحة</div>
                        </div>
                            <div class="stat-value">
                            <?php 
                            $total_correct = 0;
                            $total_questions = 0;
                            foreach ($results as $result) {
                                $total_correct += $result['score'];
                                $total_questions += $result['total_questions'];
                            }
                            echo $total_correct . '/' . $total_questions;
                            ?>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-card-row">
                            <div class="stat-icon s-icon"><i class="fas fa-percentage"></i></div>
                            <div class="stat-title">المعدل العام</div>
                        </div>
                            <div class="progress-circle">
                                <svg viewBox="0 0 36 36">
                                    <path class="circle-bg" d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <path class="circle-progress" stroke-dasharray="<?= round($overallAvg) ?>, 100" d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <text x="18" y="20.5" class="circle-text" text-anchor="middle" dominant-baseline="middle">
                                        <?= round($overallAvg) ?>%
                                    </text>
                                </svg>
                            </div>
                    </div>
                    
                    <?php foreach ($stats as $stat): ?>
                    <div class="stat-card">
                        <div class="stat-card-row">
                            <div class="stat-icon">
                                <?php 
                                $subject_icon = 'fa-book';
                                if (strpos($stat['subject'], 'رياضيات') !== false) $subject_icon = 'fa-square-root-alt';
                                elseif (strpos($stat['subject'], 'علوم') !== false) $subject_icon = 'fa-flask';
                                elseif (strpos($stat['subject'], 'لغة') !== false) $subject_icon = 'fa-language';
                                ?>
                                <i class="fas <?= $subject_icon ?>"></i>
                            </div>
                            <div class="stat-title"><?= htmlspecialchars($stat['subject']) ?></div>
                        </div>
                        <div class="progress-circle">
                            <svg viewBox="0 0 36 36">
                                <path class="circle-bg" d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="circle-progress" stroke-dasharray="<?= round($stat['avg_percentage']) ?>, 100" d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <text x="18" y="20.5" class="circle-text" text-anchor="middle" dominant-baseline="middle">
                                    <?= round($stat['avg_percentage']) ?>%
                                </text>
                            </svg>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- مخطط تطور الأداء -->
                <div class="chart-container">
                    <h3><i class="fas fa-chart-line"></i> تطور الأداء</h3>
                    <canvas id="performanceChart" style="max-height: 350px;"></canvas>
                </div>
                
                <!-- فلتر النتائج -->
                <div class="filters-container">
                    <h3><i class="fas fa-filter"></i> تصفية النتائج</h3>
                    <form method="GET" class="filter-form">
                        <div class="filter-fields">
                            <div class="form-group">
                                <!-- <label for="subject">المادة</label> -->
                                <select id="subject" name="subject" class="form-control">
                                    <option value="">كل المواد</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?= htmlspecialchars($subject) ?>" <?= $subject_filter == $subject ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($subject) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <!-- <label for="type">نوع الاختبار</label> -->
                                <select id="type" name="type" class="form-control">
                                    <option value="">كل الأنواع</option>
                                    <?php foreach ($types as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>" <?= $type_filter == $type ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <!-- <label for="date_from">من تاريخ</label> -->
                                <input type="date" id="date_from" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
                            </div>
                            
                            <div class="form-group">
                                <!-- <label for="date_to">إلى تاريخ</label> -->
                                <input type="date" id="date_to" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn" title="تطبيق الفلتر"><i class="fas fa-search"></i></button>
                            <a href="profile.php" class="btn btn-reset" title="إعادة نعيين الفلتر"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>

                <!-- سجل الاختبارات -->
                <h2><i class="fas fa-history"></i> سجل الاختبارات</h2>
                <?php if (empty($results)): ?>
                    <div class="no-results">
                        <i class="fas fa-inbox" style="font-size: 50px; margin-bottom: 15px;"></i>
                        <p>لا توجد نتائج اختبارات متاحة</p>
                    </div>
                <?php else: ?>
                    <div class="results-table-container">
                        <table id="results-table" class="results-table">
                            <thead>
                                <tr>
                                    <th>التاريخ</th>
                                    <th>المادة</th>
                                    <th>الدرس</th>
                                    <th>نوع الاختبار</th>
                                    <th>النتيجة</th>
                                    <th>النسبة</th>
                                    <th>التقييم</th>
                                    <th>تفاصيل</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $result): 
                                    $percentage = round(($result['score'] / $result['total_questions']) * 100);
                                    $rating = '';
                                    $badge_class = '';
                                    if ($percentage >= 80) {
                                        $rating = 'ممتاز';
                                        $badge_class = 'badge-success';
                                    } elseif ($percentage >= 60) {
                                        $rating = 'جيد';
                                        $badge_class = 'badge-success';
                                    } elseif ($percentage >= 50) {
                                        $rating = 'متوسط';
                                        $badge_class = 'badge-warning';
                                    } else {
                                        $rating = 'ضعيف';
                                        $badge_class = 'badge-danger';
                                    }
                                ?>
                                <tr>
                                    <td><?= date('Y-m-d', strtotime($result['exam_date'])) ?></td>
                                    <td><?= htmlspecialchars($result['subject']) ?></td>
                                    <td><?= htmlspecialchars($result['lesson_name']) ?></td>
                                    <td><?= htmlspecialchars($result['question_type']) ?></td>
                                    <td><?= $result['score'] ?> / <?= $result['total_questions'] ?></td>
                                    <td><?= $percentage ?>%</td>
                                    <td><span class="badge <?= $badge_class ?>"><?= $rating ?></span></td>
                                    <td>
                                        <a href="profile.php?result_id=<?= $result['id'] ?>#results-table" class="btn" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($examDetails)): ?>
                <!-- تفاصيل الاختبار -->
                    <div class="exam-details">
                        <div class="bi-x-look" onclick="closeWindow()" style="cursor: pointer;">
                            <i class="bi bi-x"></i>
                        </div>
                        <h2><i class="fas fa-info-circle"></i> تفاصيل الاختبار</h2>
                        
                        <div class="detail-row">
                            <div class="detail-label">المادة:</div>
                            <div class="detail-value"><?= htmlspecialchars($examDetails['subject']) ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">الدرس:</div>
                            <div class="detail-value"><?= htmlspecialchars($examDetails['lesson_name']) ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">نوع الاختبار:</div>
                            <div class="detail-value"><?= htmlspecialchars($examDetails['question_type']) ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">التاريخ:</div>
                            <div class="detail-value"><?= date('Y-m-d H:i', strtotime($examDetails['exam_date'])) ?></div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-label">النتيجة:</div>
                            <div class="detail-value">
                                <?php 
                                $percentage = round(($examDetails['score'] / $examDetails['total_questions']) * 100);
                                $rating_color = '';
                                if ($percentage >= 80) $rating_color = '#28a745';
                                elseif ($percentage >= 60) $rating_color = '#17a2b8';
                                elseif ($percentage >= 50) $rating_color = '#ffc107';
                                else $rating_color = '#dc3545';
                                ?>
                                <strong style="color: <?= $rating_color ?>">
                                    <?= $examDetails['score'] ?> من <?= $examDetails['total_questions'] ?> 
                                    (<?= $percentage ?>%)
                                </strong>
                            </div>
                        </div>
                        
                        <h3 style="margin-top: 30px;"><i class="fas fa-times-circle"></i> الإجابات الخاطئة</h3>
                        
                        <?php if (empty($wrongAnswers)): ?>
                            <div style="text-align: center; padding: 20px; background: #d4edda; border-radius: 5px; margin-top: 15px;">
                                <i class="fas fa-check-circle" style="font-size: 24px; color: #28a745;"></i>
                                <p style="margin-top: 10px; font-size: 16px; color: #155724;">
                                    لا توجد إجابات خاطئة في هذا الاختبار!
                                </p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($wrongAnswers as $answer): ?>
                                <div class="wrong-answer">
                                    <div class="question-text"><?= htmlspecialchars($answer['question_text']) ?></div>
                                    
                                    <div class="options-container">
                                        <?php for ($i = 1; $i <= 4; $i++): ?>
                                            <?php if (!empty($answer['option' . $i])): ?>
                                                <div class="option 
                                                    <?= $answer['user_answer'] == $i ? 'user-choice' : '' ?> 
                                                    <?= $answer['correct_answer'] == $i ? 'correct-answer' : '' ?>">
                                                    
                                                    <?php if ($answer['user_answer'] == $i): ?>
                                                        <span class="option-icon"><i class="fas fa-times" style="color: #dc3545;"></i></span>
                                                    <?php elseif ($answer['correct_answer'] == $i): ?>
                                                        <span class="option-icon"><i class="fas fa-check" style="color: #28a745;"></i></span>
                                                    <?php endif; ?>
                                                    
                                                    <?= htmlspecialchars($answer['option' . $i]) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    
                                    <?php if (!empty($answer['note' . $answer['user_answer']])): ?>
                                        <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-radius: 5px;">
                                            <strong><i class="fas fa-lightbulb"></i> ملاحظة:</strong> 
                                            <?= htmlspecialchars($answer['note' . $answer['user_answer']]) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </section>
    <script>
        function closeWindow() {
            // إزالة query parameters و hash من الرابط دون إعادة تحميل
            const newUrl = window.location.origin + window.location.pathname;
            window.history.pushState({}, '', newUrl);
            
            // يمكنك إضافة تأثيرات إضافية هنا إذا كنت تريد إخفاء العنصر
            document.querySelector('.exam-details').style.display = 'none';
        }

        function closeWindow() {
            // 1. تأثير إغلاق النافذة (اختياري)
            const examDetails = document.querySelector('.exam-details');
            examDetails.classList.add('closing'); // أضف هذا الكلاس في CSS
            
            // 2. تغيير الرابط بعد التأثير
            setTimeout(() => {
                const cleanUrl = window.location.origin + window.location.pathname;
                window.history.pushState({}, '', cleanUrl);
                examDetails.remove();
            }, 300); // نفس مدة الانتقال في CSS
        }
    </script>
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
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                    },
                    legend: {
                        display: false
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
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                elements: {
                    line: {
                        cubicInterpolationMode: 'monotone'
                    }
                }
            }
        });
    </script>
    <script>
        document.getElementById('avatar-container').addEventListener('click', function() {
            document.getElementById('avatar-upload').click();
        });

        document.getElementById('avatar-upload').addEventListener('change', function(e) {
            if(e.target.files.length > 0) {
                uploadAvatar(e.target.files[0]);
            }
        });

        function uploadAvatar(file) {
            const formData = new FormData();
            formData.append('avatar', file);
            formData.append('user_id', <?= $userData['id'] ?>);
            
            fetch('upload_avatar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload(); // إعادة تحميل الصفحة لعرض الصورة الجديدة
                } else {
                    alert('حدث خطأ أثناء رفع الصورة: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في الاتصال بالخادم');
            });
        }
    </script>
</body>
</html>
