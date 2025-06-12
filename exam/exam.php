<?php
require_once '../php/config.php';
session_start();

// التحقق من تسجيل الدخول ووجود بيانات الاختبار
if (!isset($_SESSION['user_id']) || !isset($_SESSION['exam_data'])) {
    header("Location: test.php");
    exit();
}

// جلب بيانات الاختبار من الجلسة
$examData = $_SESSION['exam_data'];
$subject = $examData['subject'];
$questionType = $examData['question_type'];
$lessonName = $examData['lesson_name'];

// جلب الأسئلة من قاعدة البيانات مع تحديد الحد الأقصى بـ 40 سؤال
$stmt = $pdo->prepare("SELECT * FROM questions WHERE subject = ? AND question_type = ? AND lesson_name = ? ORDER BY RAND() LIMIT 40");
$stmt->execute([$subject, $questionType, $lessonName]);
$allQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// إذا لم يتم العثور على أسئلة
if (empty($allQuestions)) {
    die("لا توجد أسئلة متاحة للاختبار المحدد.");
}

// تخزين جميع أسئلة الاختبار في الجلسة إذا لم تكن مخزنة مسبقاً
if (!isset($_SESSION['exam_questions'])) {
    $_SESSION['exam_questions'] = $allQuestions;
} else {
    $allQuestions = $_SESSION['exam_questions'];
}

$totalQuestions = count($allQuestions);

// تحديد السؤال الحالي
$currentQuestionIndex = isset($_GET['q']) ? (int)$_GET['q'] : 0;

// إذا تجاوز المؤشر عدد الأسئلة، نعيده إلى الأخير
if ($currentQuestionIndex >= $totalQuestions) {
    $currentQuestionIndex = $totalQuestions - 1;
}

// السؤال الحالي
$currentQuestion = $allQuestions[$currentQuestionIndex];

// معالجة إرسال الإجابات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // تخزين الإجابة في الجلسة إذا تم إرسالها
    if (isset($_POST['answer'])) {
        if (!isset($_SESSION['exam_answers'])) {
            $_SESSION['exam_answers'] = [];
        }
        
        $_SESSION['exam_answers'][$currentQuestion['id']] = (int)$_POST['answer'];
    }
    
    // إذا كان زر إنهاء الاختبار تم الضغط عليه
    if (isset($_POST['finish'])) {
        // التحقق من وجود أسئلة لم تتم الإجابة عليها
        $unansweredQuestions = [];
        foreach ($allQuestions as $index => $question) {
            if (!isset($_SESSION['exam_answers'][$question['id']])) {
                $unansweredQuestions[] = $index;
            }
        }
        
        // إذا كان هناك أسئلة لم تتم الإجابة عليها
        if (!empty($unansweredQuestions)) {
            // توجيه المستخدم إلى أول سؤال لم تتم الإجابة عليه
            $_SESSION['show_unanswered_warning'] = true;
            header("Location: exam.php?q=" . $unansweredQuestions[0]);
            exit();
        }
        
        // إذا كانت جميع الأسئلة مجابة، احسب النتيجة
        $score = 0;
        foreach ($allQuestions as $question) {
            if ($_SESSION['exam_answers'][$question['id']] == $question['correct_answer']) {
                $score++;
            }
        }
        
        // حفظ النتيجة في قاعدة البيانات
        $stmt = $pdo->prepare("INSERT INTO exam_results (user_id, subject, lesson_name, question_type, score, total_questions, exam_date) 
                               VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $_SESSION['user_id'],
            $subject,
            $lessonName,
            $questionType,
            $score,
            $totalQuestions
        ]);
        $examResultId = $pdo->lastInsertId();
        
        // حفظ الإجابات التفصيلية
        foreach ($allQuestions as $question) {
            $userAnswer = $_SESSION['exam_answers'][$question['id']];
            $isCorrect = ($userAnswer == $question['correct_answer']) ? 1 : 0;
            
            $stmt = $pdo->prepare("INSERT INTO student_answers 
                                  (user_id, exam_result_id, question_id, user_answer, is_correct) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $examResultId,
                $question['id'],
                $userAnswer,
                $isCorrect
            ]);
        }
        
        // مسح بيانات الاختبار من الجلسة
        unset($_SESSION['exam_answers']);
        unset($_SESSION['exam_questions']);
        unset($_SESSION['exam_data']);
        unset($_SESSION['show_unanswered_warning']);
        
        // توجيه إلى صفحة النتائج
        header("Location: ../profile.php?result_id=" . $examResultId);
        exit();
    }
    
    // تحديد اتجاه الانتقال (التالي أو السابق)
    $direction = isset($_POST['direction']) ? $_POST['direction'] : 'next';
    
    // حساب الفهرس الجديد
    $newIndex = $currentQuestionIndex;
    if ($direction === 'next' && $currentQuestionIndex < $totalQuestions - 1) {
        $newIndex = $currentQuestionIndex + 1;
    } elseif ($direction === 'prev' && $currentQuestionIndex > 0) {
        $newIndex = $currentQuestionIndex - 1;
    }
    
    // الانتقال إلى السؤال الجديد
    header("Location: exam.php?q=" . $newIndex);
    exit();
}

// جلب الإجابة المحفوظة إن وجدت
$userAnswer = isset($_SESSION['exam_answers'][$currentQuestion['id']]) ? $_SESSION['exam_answers'][$currentQuestion['id']] : null;
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
    <link rel="icon" href="../image/logo/book-open-reader-solid.svg" type="image/svg">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-B2Z6G6EY81"></script>
    <script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-B2Z6G6EY81');</script>
    <link rel="stylesheet" href="../css/Dark Mode.css">
    <link rel="stylesheet" href="../css/exam.css">
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Sahat Al-llm - Exam</title>
    <style>
        /* CSS متجاوب لجميع الأجهزة */
        body {
            background-color: #ffffff!important;
            font-family: "Noto Kufi Arabic", 'Cairo', sans-serif;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            padding-right: 0;
            padding-left: 0;
            margin-top: 65px;
            margin-right: auto;
            margin-left: auto;
        }

        .exam-container {
            background-color: white;
            border-radius: 10px;
            margin: 20px auto;
            padding: 20px;
            max-width: 100%;
            overflow-x: hidden;
        }

        .p-4 {
            padding: .7rem !important;
        }

        /* تحسينات للشاشات الصغيرة */
        @media (max-width: 767.98px) {
            .exam-container {
                padding: 15px 5px;
                margin: 0px auto;
                border-radius: 8px;
            }
            
            .exam-header h1 {
                font-size: 1.5rem;
            }
            
            .exam-header h3 {
                font-size: 1.1rem;
            }
            
            .badge, .timer {
                font-size: 0.9rem !important;
            }
            
            .question-card h4 {
                font-size: 1.1rem;
            }
            
            .option-label {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
        }

        /* تحسينات للشاشات المتوسطة */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .exam-container {
                padding: 25px;
            }
            
            .container {
                max-width: 900px;
            }
        }

        /* تحسينات للشاشات الكبيرة */
        @media (min-width: 992px) {
            .container {
                max-width: 960px;
            }
        }

        /* تحسينات إضافية للتجاوب */
        .exam-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .question-card {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            background-color: #fff;
        }

        .question-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 10px 0;
            border: 1px solid #eee;
            cursor: pointer;
            transition: transform 0.3s;
            display: block;
        }

        .question-image:hover {
            transform: scale(1.02);
        }

        .option-label {
            display: block;
            padding: 12px 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            word-break: break-word;
        }

        .option-label:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
        }

        .option-input:checked + .option-label {
            background-color: #e9f7fe;
            border-color: #0d6efd;
            color: #0d6efd;
        }

        .progress-segment {
            height: 10px;
            margin-right: 2px;
            flex-grow: 1;
            cursor: pointer;
        }

        .progress-segment:last-child {
            margin-right: 0;
        }

        .answered {
            background-color: #198754;
        }

        .unanswered {
            background-color: #ffc107;
        }

        .current-question {
            background-color: #0d6efd !important;
        }

        .modal-image {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .timer {
            font-size: 1.1rem;
            font-weight: bold;
            color: #dc3545;
        }

        /* تحسينات لأزرار التنقل */
        .nav-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }

        .nav-buttons .btn {
            flex: 1 1 auto;
            min-width: 120px;
        }

        /* تحسينات للشاشات الصغيرة جدًا */
        @media (max-width: 575.98px) {
            .nav-buttons .btn {
                flex: 1 1 100%;
                margin-bottom: 10px;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 10px;
            }
            
            .progress-segment {
                height: 8px;
            }
        }

        @media (min-width: 576px) {
            .container, .container-sm {
                /* max-width: 680px; */
            }
        }

        @media (min-width: 576px) {
            .container, .container-sm {
                /* max-width: 540px; */
            }
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




 /* التنسيقات الأساسية */
    .navbar-item.dropdown {
        position: relative;
    }
    
    .dropdown-menu {
        display: block;
        position: absolute;
        background-color: #fff;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        right: 0;
        border-radius: 4px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    }
    
    .dropdown-menu li {
        padding: 8px 16px;
    }
    
    .dropdown-menu li a {
        color: #333;
        text-decoration: none;
        display: block;
        transition: all 0.2s;
    }
    
    .dropdown-menu li a:hover {
        background-color: #f5f5f5;
        padding-right: 20px;
    }
    
    .navbar-item.dropdown:hover .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    /* تأثيرات إضافية للروابط الرئيسية */
    .navbar-link {
        position: relative;
        transition: all 0.3s;
    }
    
    .navbar-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -5px;
        right: 0;
        background-color: #3498db;
        transition: width 0.3s;
    }
    
    .navbar-link:hover::after {
        width: 100%;
    }
    
    /* تأثيرات عند النقر */
    .navbar-link:active {
        transform: scale(0.95);
    }
    </style>
</head>
<body>
    <section class="dashboard-header">
        <div class="dashboard-logo">
            <a href="../cour-3years.php" class="logo-link">
                <img src="../image/logo/book-open-reader-solid.svg" alt="Sahat Al-llm Logo" class="logo-img">
                <h1 class="logo-text">Sahat al-llm</h1>
            </a>
        </div>
        <nav class="dashboard-navbar">
            <ul class="navbar-list">
                <li class="navbar-item"><a href="../cour-3years.php" class="navbar-link">الرئيسية</a></li>
                
                <li class="navbar-item dropdown">
                    <a href="./study/study.php" class="navbar-link">الدراسة</a>
                    <ul class="dropdown-menu">
                        <li><a href="./study/courses.php">الدورات</a></li>
                        <li><a href="./study/materials.php">المواد الدراسية</a></li>
                        <li><a href="./study/schedule.php">الجدول الدراسي</a></li>
                    </ul>
                </li>
                
                <li class="navbar-item dropdown">
                    <a href="./exam.php" class="navbar-link">الاختبارات</a>
                    <ul class="dropdown-menu">
                        <li><a href="./top_students.php">الأوائل</a></li>
                        <li><a href="../settings.php">الأعدادات</a></li>
                        <li><a href="./exam/results.php">النتائج</a></li>
                    </ul>
                </li>
                
                <li class="navbar-item"><a href="../profile.php" class="navbar-link">الملف الشخصي</a></li>
            </ul>
        </nav>
    </section>

    <div class="container">
        <div class="exam-container">
            <!-- رأس الاختبار -->
            <div class="exam-header text-center">
                <h1 class="mb-3">اختبار <?= htmlspecialchars($subject) ?></h1>
                <h3 class="text-muted"><?= htmlspecialchars($lessonName) ?> - <?= htmlspecialchars($questionType) ?></h3>
                
                <!-- رسالة التنبيه إذا كان هناك أسئلة غير مجابة -->
                <?php if (isset($_SESSION['show_unanswered_warning'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        لم تتم الإجابة على جميع الأسئلة، يرجى إكمال الإجابات المتبقية
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['show_unanswered_warning']); ?>
                <?php endif; ?>
            </div>
            
            <!-- شريط التقدم -->
            <div class="progress mb-4 d-flex" style="height: 10px;">
                <?php
                for ($i = 0; $i < $totalQuestions; $i++) {
                    $questionId = $allQuestions[$i]['id'];
                    $isAnswered = isset($_SESSION['exam_answers'][$questionId]);
                    $isCurrent = ($i == $currentQuestionIndex);
                    
                    $class = 'progress-segment ';
                    $class .= $isAnswered ? 'answered ' : 'unanswered ';
                    $class .= $isCurrent ? 'current-question' : '';
                    
                    echo '<div class="'.$class.'" title="السؤال '.($i+1).'" onclick="goToQuestion('.$i.')"></div>';
                }
                ?>
            </div>
            
            <!-- رقم السؤال الحالي -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="badge bg-primary fs-6">
                    السؤال <?= $currentQuestionIndex + 1 ?> من <?= $totalQuestions ?>
                </div>
                <div class="timer">
                    <i class="fas fa-clock me-2"></i>
                    <span id="time">30:00</span>
                </div>
            </div>
            
            <!-- نموذج الاختبار -->
            <form method="post" id="examForm">
                <input type="hidden" name="direction" id="direction" value="next">
                
                <!-- السؤال -->
                <div class="question-card mb-4 p-4 border rounded">
                    <h4 class="mb-4"><?= htmlspecialchars($currentQuestion['question_text']) ?></h4>
                    
                    <?php if (!empty($currentQuestion['question_image'])): ?>
                        <img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" 
                             data-src="../admin/<?= htmlspecialchars($currentQuestion['question_image']) ?>" 
                             alt="صورة السؤال" class="question-image img-thumbnail" 
                             loading="lazy" id="questionImage" data-bs-toggle="modal" data-bs-target="#imageModal">
                    <?php endif; ?>
                    
                    <!-- الخيارات -->
                    <div class="options mt-4">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <?php if (!empty($currentQuestion['option' . $i])): ?>
                                <div class="form-check mb-3">
                                    <input class="form-check-input option-input" type="radio" 
                                           name="answer" id="option<?= $i ?>" 
                                           value="<?= $i ?>" 
                                           <?= $userAnswer == $i ? 'checked' : '' ?>>
                                    <label class="form-check-label option-label" for="option<?= $i ?>">
                                        <?= htmlspecialchars($currentQuestion['option' . $i]) ?>
                                    </label>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <!-- أزرار التنقل -->
                <div class="d-flex justify-content-between mt-4">
                    <?php if ($currentQuestionIndex > 0): ?>
                        <button type="button" class="btn btn-primary px-4 py-2" onclick="goToPrevious()">
                            <i class="fas fa-arrow-right me-2"></i> السابق
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary px-4 py-2" disabled>
                            <i class="fas fa-arrow-right me-2"></i> السابق
                        </button>
                    <?php endif; ?>
                    
                    <!-- زر الخروج من الاختبار -->
                    <button type="button" class="btn btn-outline-danger px-4 py-2" data-bs-toggle="modal" data-bs-target="#exitModal">
                        <i class="fas fa-sign-out-alt me-2"></i> الخروج من الاختبار
                    </button>
                    
                    <?php if ($currentQuestionIndex < $totalQuestions - 1): ?>
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            التالي <i class="fas fa-arrow-left ms-2"></i>
                        </button>
                    <?php else: ?>
                        <button type="submit" name="finish" class="btn btn-danger px-4 py-2">
                            إنهاء الاختبار <i class="fas fa-flag-checkered ms-2"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal لعرض الصورة بحجم كبير -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">صورة السؤال</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" class="modal-image img-fluid" id="modalImage">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal لتأكيد الخروج -->
    <div class="modal fade" id="exitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تأكيد الخروج</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد أنك تريد الخروج من الاختبار؟ سيتم فقدان جميع إجاباتك.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <form method="post" action="exit_exam.php" style="display: inline;">
                        <button type="submit" class="btn btn-danger">نعم، أريد الخروج</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // للانتقال إلى السؤال السابق
        function goToPrevious() {
            document.getElementById('direction').value = 'prev';
            document.getElementById('examForm').submit();
        }
        
        // للانتقال إلى سؤال معين
        function goToQuestion(index) {
            window.location.href = 'exam.php?q=' + index;
        }
        
        // تحميل الصور عند ظهورها في الشاشة (Lazy Loading)
        document.addEventListener('DOMContentLoaded', function() {
            // تهيئة الصورة في المودال
            const questionImage = document.getElementById('questionImage');
            if (questionImage) {
                document.getElementById('imageModal').addEventListener('show.bs.modal', function() {
                    document.getElementById('modalImage').src = questionImage.dataset.src;
                });
            }
            
            // تنفيذ Lazy Loading للصور
            const lazyImages = [].slice.call(document.querySelectorAll('img[data-src]'));
            
            if ('IntersectionObserver' in window) {
                let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            let lazyImage = entry.target;
                            lazyImage.src = lazyImage.dataset.src;
                            lazyImageObserver.unobserve(lazyImage);
                        }
                    });
                });
                
                lazyImages.forEach(function(lazyImage) {
                    lazyImageObserver.observe(lazyImage);
                });
            } else {
                // Fallback for browsers without IntersectionObserver
                lazyImages.forEach(function(lazyImage) {
                    lazyImage.src = lazyImage.dataset.src;
                });
            }
            
            // timer countdown
            let timeInMinutes = 30;
            let currentTime = Date.parse(new Date());
            let deadline = new Date(currentTime + timeInMinutes*60*1000);
            
            function getTimeRemaining(endtime) {
                let t = Date.parse(endtime) - Date.parse(new Date());
                let seconds = Math.floor((t / 1000) % 60);
                let minutes = Math.floor((t / 1000 / 60) % 60);
                return {
                    'total': t,
                    'minutes': minutes,
                    'seconds': seconds
                };
            }
            
            function updateClock() {
                let t = getTimeRemaining(deadline);
                
                let minutes = ('0' + t.minutes).slice(-2);
                let seconds = ('0' + t.seconds).slice(-2);
                
                document.getElementById('time').innerHTML = minutes + ':' + seconds;
                
                if (t.total <= 0) {
                    clearInterval(timeinterval);
                    document.getElementById('examForm').submit();
                }
            }
            
            updateClock();
            let timeinterval = setInterval(updateClock, 1000);
        });


        document.addEventListener('DOMContentLoaded', function() {
            // إضافة تأثير التمرير
            window.addEventListener('scroll', function() {
                const header = document.querySelector('.dashboard-header');
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
            
            // القائمة المنسدلة للجوال
            const menuToggle = document.createElement('button');
            menuToggle.className = 'menu-toggle';
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            document.querySelector('.dashboard-header').appendChild(menuToggle);
            
            const navbarList = document.querySelector('.navbar-list');
            menuToggle.addEventListener('click', function() {
                navbarList.classList.toggle('active');
                this.innerHTML = navbarList.classList.contains('active') 
                    ? '<i class="fas fa-times"></i>' 
                    : '<i class="fas fa-bars"></i>';
            });
            
            // إغلاق القائمة عند النقر على رابط
            document.querySelectorAll('.navbar-link').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        navbarList.classList.remove('active');
                        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    }
                });
            });
        });
    </script>
</body>
</html>