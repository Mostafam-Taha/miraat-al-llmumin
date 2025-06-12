<?php
require_once './php/config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: ./login.php");
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
                q.correct_answer, q.note1, q.note2, q.note3, q.note4, q.question_image
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
    <link rel="stylesheet" href="./css/Dark Mode.css">
    <!-- <link rel="stylesheet" href="./css/profile.css"> -->
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-K73Z87CS');</script>
    <!-- End Google Tag Manager -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <title>Sahat Al-llm - Profile </title>
    <style>

:root {
    --bs-primary-color: #ffffff;
    --bs-secondary-color: rgba(214, 41, 62, 0.1);
    --fc-background-color: hsla(44, 93%, 57%, 0.149);
    --fc-btn-bg: #066ac9;
    --fc-btn-bg-co: #001e2b;
    --fc-color-btn-new: #d6293e!important;
    --fc-inp-border-co: #dfe2e1;
    --fo-h1-h6-co: #21313c;
    --fc-p-co: #5f5f5f;
    --fc-par-color: #ffc107!important;
    --fc-prod-sale-color: #db3030!important;
    --fc-cursor-poi: pointer;
    --fc-margin-width: 0 100px;
    --fc-padding-width-btn: 7px 22px;
    --fc-border-color: #dfe2e1;
    --fc-border-width-none: none;
    --fc-border-width: 1px;
    --fc-border-style: solid;
    --fc-border-co-hevor: #066ac9;
    --fc-border-radius-width: 7px;
    --fc-box-shadow-hevor: 0px 0px 0px 5px #066bc954;
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
}

.row {
    --fc-breakpoint-labtop-L: 1440px;
    --fc-breakpoint-laptop: 1024px;
    --fc-breakpoint-md: ;
    --fc-breakpoint-lg: ;
    --fc-breakpoint-xl: ;
    --fc-breakpoint-xxl: ;
}

* {
    font-family: "Noto Kufi Arabic", sans-serif;
    font-family: 'Cairo', sans-serif;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: "Noto Kufi Arabic", sans-serif;
    font-family: 'Cairo', sans-serif;
    background-color: var(--bs-primary-color);
    color: var(--fo-h1-h6-co);
    margin: 0;
    padding: 0;
}


body::-webkit-scrollbar {
    display: none;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Cairo', sans-serif;
    color: var(--fo-h1-h6-co);
    margin-bottom: 8px;
    padding: 0;
}

p {
    font-family: 'Cairo', sans-serif;
    margin: 0;
    padding: 0;
}

a {
    font-family: 'Cairo', sans-serif;
    text-decoration: var(--fc-border-width-none);
    color: var(--fc-p-co);
}

hr {
    margin: 35px 0;
    border: var(--fc-border-width-none);
    background-color: var(--fc-inp-border-co);
    width: 100%;
    height: 1px;
}
.dashboard-main {
    padding: 20px;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    margin-top: 80px;
}

.logo-img {
    width: 30px;
    height: 30px;
    filter: brightness(0.8); /* تخفيف سطوع الصورة */
}

.logo-text {
    margin: 0;
    font-size: 1.2rem;
    color: #333;
    font-weight: 600;
}

.logo-link {
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.logo-link:hover {
    opacity: 0.9;
    transform: translateY(-2px); /* تأثير رفع عند hover */
}

.student-profile {
    display: flex;
    flex-wrap: wrap;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}
.student-profile::before {
    content: '';
    /* position: absolute; */
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.1)" d="M0,0 L100,0 L100,100 L0,100 Z"></path></svg>');
    background-size: cover;
    opacity: 0.1;
}

.student-details {
    flex: 1;
    min-width: 300px;
}
.student-name {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 10px;
    color: #2c3e50;
}
.student-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 15px;
    margin-top: 15px;
}
.info-item {
    background: rgba(255,255,255,0.7);
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}
.info-label {
    font-size: 12px;
    color: #7f8c8d;
    margin-bottom: 5px;
}
.info-value {
    font-size: 16px;
    font-weight: 500;
    color: #34495e;
}
.rating-value {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 5px;
}
.rating-text {
    font-size: 18px;
    font-weight: 500;
    padding: 5px 15px;
    border-radius: 20px;
    color: white;
}
.filters-container {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.filter-form ,.filter-fields{
    display: flex;
    gap: 10px;
    justify-content: space-between;
    align-items: center;
}
.form-group {
    margin-bottom: 0;
    margin-right: 5px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #7f8c8d;
}
.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 12px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0px 0px 1px 5px rgba(52, 152, 219, 0.2);
    outline: none;
}

.btn {
    padding: 8px 15px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s;
}
.btn:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}
.btn-reset {
    background-color: #95a5a6;
}
.btn-reset:hover {
    background-color: #7f8c8d;
}
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin: 30px 0;
}
.stat-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    text-align: center;
    transition: all 0.3s;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.stat-icon {
    margin-bottom: 15px;
    color: var(--primary-color);
}
.stat-title {
    font-size: 16px;
    color: #7f8c8d;
    margin-bottom: 10px;
}
.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #2c3e50;
    text-align: left;
    /* margin-bottom: 10px; */
}
.progress-circle {
    width: 80px;
    height: 80px;
    margin: 0 auto 0 0;
    position: relative;
}
.circle-bg {
    fill: none;
    stroke: #eee;
    stroke-width: 3px;
}
.circle-progress {
    fill: none;
    stroke: var(--primary-color);
    stroke-width: 3px;
    stroke-linecap: round;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
    animation: circle-fill 1.5s ease-in-out forwards;
}
.circle-text {
    font-size: 8.5px;
    font-weight: bold;
    fill: #2c3e50;
}
@keyframes circle-fill {
    0% { stroke-dasharray: 0, 100; }
}
.results-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.results-table th {
    background-color: #cdcdcd3d;
    padding: 12px;
    font-size: 14px;
    text-align: center;
}
.results-table td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
}
.results-table tr:nth-child(even) {
    background-color: #f8f9fa;
}
.results-table tr:hover {
    background-color: #e9f7fe;
}
.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}
.badge-success {
    background-color: #d4edda;
    color: #155724;
}
.badge-warning {
    background-color: #fff3cd;
    color: #856404;
}
.badge-danger {
    background-color: #f8d7da;
    color: #721c24;
}
.exam-details {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin: 30px 0;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
.detail-row {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 10px;
}
.detail-label {
    flex: 0 0 150px;
    font-weight: 500;
    color: #7f8c8d;
}
.detail-value {
    flex: 1;
    color: #2c3e50;
}
.wrong-answer {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border-left: 4px solid var(--danger-color);
}
.question-text {
    font-weight: bold;
    margin-bottom: 10px;
    color: #2c3e50;
}
.options-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    margin-top: 10px;
}
.option {
    padding: 8px;
    border-radius: 5px;
    position: relative;
    padding-right: 30px;
}
.user-choice {
    background-color: #fff3cd;
    border: 1px solid #ffeeba;
}
.correct-answer {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
}
.option-icon {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
}
.chart-container {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin: 30px 0;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
.no-results {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
    font-size: 18px;
}
@media (max-width: 768px) {
    .student-profile {
        flex-direction: column;
        align-items: center;
        text-align: center;;
    }
    .student-avatar {
        margin-left: 0;
        margin-bottom: 20px;
    }
    .performance-rating {
        margin-left: 0;
        margin-top: 20px;
    }
    .student-info-grid {
        grid-template-columns: 1fr;
    }
    .filter-form {
        grid-template-columns: 1fr;
    }
}

.exam-details {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(1); /* الحجم الطبيعي عند الظهور */
    width: 90%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
    background-color: white;
    transition: all 0.3s ease; /* مدة وتوقيت الحركة */
    opacity: 1; /* ظاهر بالكامل في البداية */
    z-index: 1000;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    border-radius: 8px;
    padding: 20px;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.exam-details.closing {
    opacity: 0;
    transform: translate(-50%, -50%) scale(0.9); /* يصغر ويختفي */
}

.exam-details .bi-x-look {
    position: absolute;
    top: 5px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
    color: #7f8c8d;
    transition: color 0.3s ease-in-out;
}

.exam-details .bi-x-look:hover {
    color: #e74c3c;
}

.avatar-section {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    direction: rtl;
    text-align: right;
    position: relative;
    /* margin: 20px; */

}

.student-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    cursor: pointer;
    border: 5px solid white;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.student-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.avatar-image {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-initial {
    color: #3498db;
    font-size: 60px;
    font-weight: bold;
}

.avatar-upload-input {
    display: none;
}

.avatar-upload-hint {
    position: absolute;
    bottom: -25px;
    background: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    color: #3498db;
    opacity: 0;
    transition: all 0.3s ease;
}

.student-avatar:hover .avatar-upload-hint {
    opacity: 1;
    bottom: -20px;
}

.info-section {
    padding: 30px;
}

.student-name {
    color: #2c3e50;
    margin: 0 0 -25px 0;
    font-size: 32px;
}

.student-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.item {
    margin: 0 0 0 -12px;
    padding: 7px 15px 0 0px;
    display: flex;
    flex-direction: row-reverse;
    align-items: center;
    transition: all 0.3s ease;
    color: var(--fc-p-co);
    font-size: 14px;
}

.icon-i {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: -5px;
    flex-shrink: 0;
}

.detail-item {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.detail-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.detail-icon {
    background: #3498db;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 15px;
    flex-shrink: 0;
}

.detail-content {
    flex-grow: 1;
}

.detail-label {
    color: #7f8c8d;
    font-size: 14px;
    margin-bottom: 5px;
}

.detail-value {
    color: #2c3e50;
    font-weight: 500;
    font-size: 16px;
}

.performance-rating {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 20px;
}

.rating-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: conic-gradient(var(--rating-color) 0% var(--rating-percent), #eee var(--rating-percent) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.rating-circle::before {
    content: '';
    position: absolute;
    width: 70px;
    height: 70px;
    background: white;
    border-radius: 50%;
}

.rating-value {
    font-weight: bold;
    font-size: 18px;
    color: #2c3e50;
    z-index: 1;
}

.rating-text {
    margin-top: 10px;
    padding: 5px 15px;
    border-radius: 20px;
    color: white;
    font-weight: bold;
    font-size: 14px;
}

/* تصميم متجاوب */
@media (max-width: 768px) {
    .student-profile-card {
        flex-direction: column;
    }
    
    .avatar-section {
        padding: 20px;
    }
    
    .student-avatar {
        width: 120px;
        height: 120px;
    }
    
    .info-section {
        padding: 20px;
    }
    
    .student-details-grid {
        grid-template-columns: 1fr;
    }
}
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

/* تنسيق عام للرأس */
.dashboard-header {
    background-color: #ffffff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    transition: all 0.3s ease;
}

/* تأثير عند التمرير */
.dashboard-header.scrolled {
    padding: 10px 30px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* تنسيق الشعار */
.dashboard-logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    transition: transform 0.3s ease;
}

.logo-link:hover {
    transform: translateY(-2px);
}

.logo-img {
    width: 32px;
    height: 32px;
    object-fit: contain;
}

.logo-text {
    color: var(--h1-h6-co);
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    font-family: 'Tajawal', sans-serif;
    background: var(--h1-h6-co);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* تنسيق قائمة التنقل */
.dashboard-navbar {
    display: flex;
    align-items: center;
}

.navbar-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 25px;
}

.navbar-item {
    position: relative;
}

.navbar-link {
    color: #4a5568;
    text-decoration: none;
    font-size: 1.1rem;
    font-weight: 600;
    padding: 8px 12px;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.navbar-link:hover {
    color: #4361ee;
    background-color: #eef2ff;
}

.navbar-link.active {
    color: #4361ee;
    background-color: #eef2ff;
}

/* تأثير عند التفعيل */
.navbar-item::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 3px;
    background: linear-gradient(to right, #4361ee, #3f37c9);
    border-radius: 3px;
    transition: width 0.3s ease;
}

.navbar-item:hover::after,
.navbar-item.active::after {
    width: 70%;
}

/* زر القائمة المنسدلة للجوال */
.menu-toggle {
    display: none;
    background: none;
    border: none;
    color: #4361ee;
    font-size: 1.5rem;
    cursor: pointer;
}

/* التكيف مع الشاشات الصغيرة */
@media (max-width: 768px) {
    .dashboard-header {
        padding: 15px 20px;
    }
    
    .navbar-list {
        position: fixed;
        top: 67px;
        right: -100%;
        width: 80%;
        max-width: 300px;
        background: white;
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
        box-shadow: -3px 13px 15px rgba(0, 0, 0, 0.1);
        border-radius: 0 0 0 15px;
        transition: right 0.3s ease;
        gap: 15px;
        height: 100vh;
    }
    
    .navbar-list.active {
        right: 0;
    }
    
    .navbar-link {
        width: 100%;
        padding: 12px 15px;
    }
    
    .menu-toggle {
        display: block;
    }
    
    .navbar-item::after {
        bottom: 0;
        left: 0;
        transform: none;
        width: 3px;
        height: 0;
    }
    
    .navbar-item:hover::after,
    .navbar-item.active::after {
        width: 3px;
        height: 70%;
    }
}

/* إضافة تأثيرات حركية */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.navbar-item {
    animation: fadeIn 0.5s ease forwards;
}

.navbar-item:nth-child(1) { animation-delay: 0.1s; }
.navbar-item:nth-child(2) { animation-delay: 0.2s; }
.navbar-item:nth-child(3) { animation-delay: 0.3s; }
.navbar-item:nth-child(4) { animation-delay: 0.4s; }

/* التعديلات لشاشات 768px وأصغر */
@media (max-width: 768px) {
    .dashboard-main {
        margin-top: 0;
        padding: 10px;
    }
    .student-profile-container {
        padding: 15px;
    }
    
    .student-profile-card {
        padding: 20px;
    }
    
    .avatar-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        text-align: center;
        margin-bottom: 20px;
        margin-top: 30px;
    }
    
    .student-avatar {
        width: 120px;
        height: 120px;
        margin: 0 auto 15px;
    }
    
    .avatar-initial {
        font-size: 50px;
        line-height: 120px;
    }
    
    .avatar-upload-hint {
        bottom: -5px;
        right: calc(50% - 60px);
    }
    
    .student-name {
        font-size: 1.5rem;
        margin-bottom: 15px;
    }
    
    .item {
        align-items: center;
        margin-bottom: 15px;
    }
    
    .icon-i {
        margin-bottom: 5px;
        margin-right: 0;
    }
    
    .performance-rating {
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
    }
    
    .rating-circle {
        width: 80px;
        height: 80px;
        margin-bottom: 10px;
    }
    
    .rating-value {
        font-size: 1.2rem;
    }
    
    .rating-text {
        padding: 5px 15px;
        font-size: 0.9rem;
    }
}

/* التعديلات لشاشات 480px وأصغر */
@media (max-width: 480px) {
    .student-profile-card {
        padding: 15px;
    }
    
    .student-avatar {
        width: 100px;
        height: 100px;
    }
    
    .avatar-initial {
        font-size: 40px;
        line-height: 100px;
    }
    
    .student-name {
        font-size: 1.3rem;
    }
    
    .d-value {
        font-size: 0.9rem;
    }
    
    .rating-circle {
        width: 70px;
        height: 70px;
    }
    
    .rating-value {
        font-size: 1rem;
    }
}

/* التعديلات لشاشات 320px وأصغر */
@media (max-width: 320px) {
    .student-profile-card {
        padding: 10px;
    }
    
    .student-avatar {
        width: 80px;
        height: 80px;
    }
    
    .avatar-initial {
        font-size: 30px;
        line-height: 80px;
    }
    
    .avatar-upload-hint {
        font-size: 0.7rem;
        padding: 3px 8px;
        bottom: -8px;
        right: calc(50% - 50px);
    }
    
    .student-name {
        font-size: 1.1rem;
    }
    
    .d-value {
        font-size: 0.8rem;
    }
    
    .rating-circle {
        width: 60px;
        height: 60px;
    }
    
    .rating-value {
        font-size: 0.9rem;
    }
    
    .rating-text {
        padding: 3px 10px;
        font-size: 0.8rem;
    }
}


/* التعديلات لشاشات 768px وأصغر */
@media (max-width: 768px) {
    .filters-container {
        padding: 15px;
    }
    
    .filter-form {
        flex-direction: column;
        gap: 15px;
    }
    
    .filter-fields {
        flex-wrap: wrap;
        justify-content: flex-start;
        width: 100%;
    }
    
    .form-group {
        width: calc(50% - 5px);
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .form-control {
        padding: 7px 10px;
        font-size: 13px;
    }
    
    .btn {
        padding: 7px 12px;
    }
}

/* التعديلات لشاشات 480px وأصغر */
@media (max-width: 480px) {
    .filters-container {
        padding: 12px;
    }
    
    .filter-fields {
        gap: 8px;
    }
    
    .form-group {
        width: 100%;
    }
    
    .form-control {
        padding: 6px 8px;
        font-size: 12px;
    }
    
    .btn {
        padding: 6px 10px;
        font-size: 12px;
    }
}

/* التعديلات لشاشات 320px وأصغر */
@media (max-width: 320px) {
    .filters-container {
        padding: 10px;
    }
    
    .filter-fields {
        gap: 5px;
    }
    
    .form-control {
        padding: 5px 7px;
    }
    
    .btn {
        padding: 5px 8px;
    }
    
    .filters-container h3 {
        font-size: 1rem;
    }
}

/* القائمة المنسدلة الأساسية */
.navbar-item.dropdown {
    position: relative;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background-color: #fff;
    min-width: 200px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border-radius: 4px;
    z-index: 1000;
    padding: 0;
    margin: 0;
    list-style: none;
}

.dropdown-menu li {
    padding: 0;
    margin: 0;
}

.dropdown-link {
    display: block;
    padding: 10px 15px;
    color: #333;
    text-decoration: none;
    transition: background-color 0.3s;
}

.dropdown-link:hover {
    background-color: #f5f5f5;
}

/* إظهار القائمة عند تفعيلها */
.dropdown.active .dropdown-menu {
    display: block;
}

/* القائمة المنسدلة */
.navbar-item.dropdown {
    position: relative;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background-color: #fff;
    min-width: 200px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    border-radius: 4px;
    z-index: 1000;
    padding: 0;
    margin: 0;
    list-style: none;
}

.dropdown-menu li {
    padding: 0;
    margin: 0;
}

.dropdown-link {
    display: block;
    padding: 10px 15px;
    color: #333;
    text-decoration: none;
    transition: background-color 0.3s;
}

.dropdown-link:hover {
    background-color: #f5f5f5;
}

.dropdown.active .dropdown-menu {
    display: block;
}

.navbar-item.dropdown .navbar-link::after {
    content: "▼";
    font-size: 0.6em;
    margin-right: 5px;
}

.dis-none {
    display: none;
}

@media (max-width: 768px) {
    
    .dis-none {
        display: block;
    }
}


/* صورة السؤال */

.question-image-container {
    text-align: center;
    margin: 10px 0;
    border: 1px solid #eee;
    padding: 10px;
    border-radius: 5px;
    background: #f9f9f9;
}

.question-image {
    max-height: 200px;
    max-width: 100%;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.question-image:hover {
    transform: scale(1.02);
}

    </style>
</head>
<body>
    <section class="heart-profile">
        <section class="dashboard-header">
            <div class="dashboard-logo">
                <a href="./index.html" class="logo-link">
                    <img src="./image/logo/book-open-reader-solid.svg" alt="Sahat Al-llm Logo" class="logo-img">
                    <h1 class="logo-text">Sahat al-llm</h1>
                </a>
            </div>
            <nav class="dashboard-navbar">
                <ul class="navbar-list">
                    <li class="navbar-item"><a href="./index.html" class="navbar-link">الرئيسية</a></li>
                    <li class="navbar-item"><a href="./study/study.php" class="navbar-link">الدراسة</a></li>
                    <li class="navbar-item dropdown">
                        <a href="./exam/exam.php" class="navbar-link">الاختبارات</a>
                        <ul class="dropdown-menu">
                            <li><a href="./exam/exam.php?type=quiz" class="dropdown-link">اختبار قصير</a></li>
                            <li><a href="./exam/exam.php?type=midterm" class="dropdown-link">اختبار منتصف الفصل</a></li>
                            <li><a href="./exam/exam.php?type=final" class="dropdown-link">اختبار نهائي</a></li>
                        </ul>
                    </li>
                    <li class="navbar-item dropdown">
                        <div class="navbar-link">الملف الشخصي</div>
                        <ul class="dropdown-menu">
                            <li><a href="./profile.php?page=info" class="dropdown-link">معلوماتي</a></li>
                            <li><a href="./profile.php?page=settings" class="dropdown-link">الإعدادات</a></li>
                            <li><a href="./profile.php?page=progress" class="dropdown-link">تقدمي الدراسي</a></li>
                            <li><a href="./logout.php" class="dropdown-link">تسجيل الخروج</a></li>
                        </ul>
                    </li>
                    <li class="navbar-item"><a href="./settings.php" class="navbar-link">الاعدادات</a></li>
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
                                    <img src="./api/<?= htmlspecialchars($userData['avatar']) ?>" alt="صورة المستخدم" class="avatar-image">
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
                                elseif (strpos($stat['subject'], 'الكيمياء') !== false) $subject_icon = 'fa-flask';
                                elseif (strpos($stat['subject'], 'الفيزياء') !== false) $subject_icon = 'fa-atom';
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
                    <div class="results-table-container" style="overflow: scroll;">
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
                                    <td><?= (strlen($text = htmlspecialchars($result['subject']))) > 15 ? substr($text, 0, 15) . '...' : $text ?></td>
                                    <td><?= strlen($text = htmlspecialchars($result['lesson_name'])) > 15 ? substr($text, 0, 15) . '...' : $text ?></td>
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
                        <h2 style="margin-top: 19px;"><i class="fas fa-info-circle"></i> تفاصيل الاختبار</h2>
                        
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
                                    
                                    <?php if (!empty($answer['question_image'])): ?>
                                        <div class="question-image-container">
                                            <img src="./admin/<?= htmlspecialchars($answer['question_image']) ?>" 
                                                alt="صورة السؤال" 
                                                class="question-image"
                                                onclick="window.open(this.src, '_blank')"
                                                style="cursor: pointer; max-width: 100%; max-height: 200px; margin: 10px 0;">
                                        </div>
                                    <?php endif; ?>
                                    
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
    <a href="./api/share_profile.php" class="btn-share" style="position: fixed; bottom: 20px; left: 20px; background: #4361ee; color: white; padding: 10px 15px; border-radius: 50px; text-decoration: none; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
        <i class="fas fa-share-alt"></i> مشاركة الملف
    </a>
    <script src="./js/profile.js"></script>
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


    document.addEventListener('DOMContentLoaded', function() {
        // تحديد جميع عناصر القوائم المنسدلة
        const dropdownItems = document.querySelectorAll('.navbar-item.dropdown');
        
        dropdownItems.forEach(item => {
            const dropdownLink = item.querySelector('.navbar-link');
            
            dropdownLink.addEventListener('click', function(e) {
                // إغلاق جميع القوائم المفتوحة أولاً
                dropdownItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // فتح/إغلاق القائمة الحالية
                e.preventDefault();
                item.classList.toggle('active');
            });
        });
        
        // إغلاق القوائم عند النقر خارجها
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.navbar-item.dropdown')) {
                dropdownItems.forEach(item => {
                    item.classList.remove('active');
                });
            }
        });
    });

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
            
            fetch('api/upload_avatar.php', {
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
