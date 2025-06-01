<?php
require_once '../php/config.php';
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// جلب المواد المتاحة
$subjects = [];
$stmt = $pdo->query("SELECT DISTINCT subject FROM questions");
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

// إذا تم اختيار المادة، جلب أنواع الاختبارات المتاحة
$questionTypes = [];
if (isset($_POST['subject'])) {
    $subject = sanitizeInput($_POST['subject']);
    $stmt = $pdo->prepare("SELECT DISTINCT question_type FROM questions WHERE subject = ?");
    $stmt->execute([$subject]);
    $questionTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// إذا تم اختيار النوع، جلب الدروس المتاحة
$lessons = [];
if (isset($_POST['question_type'])) {
    $questionType = sanitizeInput($_POST['question_type']);
    $stmt = $pdo->prepare("SELECT DISTINCT lesson_name FROM questions WHERE subject = ? AND question_type = ?");
    $stmt->execute([$subject, $questionType]);
    $lessons = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// إذا تم اختيار الدرس، توجيه إلى صفحة الاختبار
if (isset($_POST['lesson_name'])) {
    $_SESSION['exam_data'] = [
        'subject' => $subject,
        'question_type' => $questionType,
        'lesson_name' => sanitizeInput($_POST['lesson_name'])
    ];
    header("Location: exam.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar">
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
    <title>Sahat Al-llm</title>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-K73Z87CS');</script>
    <!-- End Google Tag Manager -->
</head>
<body>
    <div class="container">        
        <form method="post" id="examForm">
            <main class="all-section">
                <div class="container">
                    <div class="dash-prt">
                        <h3>المواد الدراسية</h3>
                        <span><a href="cour-3years.php">الرئسية</a><span class="back-slach">/</span><span>المواد الدراسية</span></span>
                    </div>
                </div>
                <section class="mat-study">
                    <div class="contanier">
                        <div class="study-card">
                            <div class="study-card-item">
                                <!-- اختيار المادة -->
                                <?php foreach ($subjects as $subj): ?>
                                    <article class="card card-ar">
                                        <section class="card__hero">
                                            <div class="card__hero-header">
                                                <span>0 سؤال</span>
                                                <div class="card__icon">
                                                    <svg height="20" width="20" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" stroke-linejoin="round" stroke-linecap="round"></path></svg>
                                                </div>  
                                            </div>
                                            <p style="text-align: right;" class="card__job-title"><?= $subj ?></p>
                                        </section>
                                        <section class="card__footer">
                                            <div class="card__job-summary">
                                                <div class="card__job-icon">
                                                    <svg class="logo-sahat-alllm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#183153" d="M160 96a96 96 0 1 1 192 0A96 96 0 1 1 160 96zm80 152l0 264-48.4-24.2c-20.9-10.4-43.5-17-66.8-19.3l-96-9.6C12.5 457.2 0 443.5 0 427L0 224c0-17.7 14.3-32 32-32l30.3 0c63.6 0 125.6 19.6 177.7 56zm32 264l0-264c52.1-36.4 114.1-56 177.7-56l30.3 0c17.7 0 32 14.3 32 32l0 203c0 16.4-12.5 30.2-28.8 31.8l-96 9.6c-23.2 2.3-45.9 8.9-66.8 19.3L272 512z"/></svg>
                                                    <svg style="display: none;" height="35" width="28" viewBox="0 0 250 250" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill="#4285F4" d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027"></path>
                                                        <path fill="#34A853"d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1"></path>
                                                        <path fill="#FBBC05"d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782"></path>
                                                        <path fill="#EB4335" d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251"></path>
                                                    </svg>
                                                </div>
                                                <div class="card__job">
                                                    <p class="card__job-title"><?= $subj ?><br/></p>
                                                </div>
                                            </div>
                                            <button type="button" class="card__btn" onclick="selectSubject('<?= $subj ?>')">اختر</button>
                                        </section>
                                    </article>
                                <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="subject" id="selectedSubject" value="<?= $subject ?? '' ?>">
                            </div>
                        </div>
                    </section>
                </main>
            <main class="all-section">
                <!-- اختيار نوع الاختبار -->
                <main class="cent">
                    <section class="le-matir">
                        <div class="container">
                            <?php if (!empty($questionTypes)): ?>
                            <div class="card-le-totril">
                                <?php foreach ($questionTypes as $type): ?>
                                    <div class="le-card-to">
                                        <div class="lech-top">
                                            <?php if ($type == 'اختبارات شاملة'): ?>
                                                <i class="bi bi-ui-checks"></i>
                                            <?php elseif ($type == 'بنك الأسئلة'): ?>
                                                <i class="bi bi-ui-checks-grid"></i>
                                            <?php elseif ($type == 'امتحانات الثانوية'): ?>
                                                <img src="../image/element/logo technical education.png" alt="لا توجد صورة" loading="lazy">
                                            <?php else: ?>
                                                <i class="bi bi-rocket-takeoff"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="lech-buttom">
                                            <h4><?= htmlspecialchars($type) ?></h4>
                                            <p>
                                                <?php 
                                                    if ($type == 'اختبارات شاملة') {
                                                        echo 'مجموعة شاملة من الأسئلة السابقة المختلفة';
                                                    } elseif ($type == 'بنك الأسئلة') {
                                                        echo 'اسئلة من مصادر متنوعة';
                                                    } elseif ($type == 'امتحانات الثانوية') {
                                                        echo 'إمتحانات السنين السابقة 2025';
                                                    } else {
                                                        echo 'إبدأ متحانات يومية شاملة لكل درس';
                                                    }
                                                ?>
                                            </p>
                                            <button type="button" class="card-btn" onclick="selectQuestionType('<?= htmlspecialchars($type) ?>')">إبدأ الآن</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                    <!-- section right -->
                    <section class="ri-matir">
                        <div class="container-ri">
                            <div class="dashboard-quikli">
                                <svg class="logo-sahat-alllm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="#183153" d="M160 96a96 96 0 1 1 192 0A96 96 0 1 1 160 96zm80 152l0 264-48.4-24.2c-20.9-10.4-43.5-17-66.8-19.3l-96-9.6C12.5 457.2 0 443.5 0 427L0 224c0-17.7 14.3-32 32-32l30.3 0c63.6 0 125.6 19.6 177.7 56zm32 264l0-264c52.1-36.4 114.1-56 177.7-56l30.3 0c17.7 0 32 14.3 32 32l0 203c0 16.4-12.5 30.2-28.8 31.8l-96 9.6c-23.2 2.3-45.9 8.9-66.8 19.3L272 512z"/></svg>
                                <h2><?= htmlspecialchars($subject) ?></h2>
                            </div>
                            <div class="dashboard-qui">
                                <div class="row-ri">
                                    <h4>الأسئلة</h4>
                                    <span>
                                        <?php 
                                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE subject = ?");
                                            $stmt->execute([$subject]);
                                            echo $stmt->fetchColumn();
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>
                    <input type="hidden" name="question_type" id="selectedQuestionType" value="<?= $questionType ?? '' ?>">
                    <?php endif; ?>
                </main>
            </main>
            <!-- اختيار الدرس (يبقى كما هو) -->
            <!-- <?php if (!empty($lessons)): ?>
            <div>
                <label for="lesson_name">اختر الدرس:</label>
                <select name="lesson_name" id="lesson_name" required>
                    <option value="">-- اختر الدرس --</option>
                    <?php foreach ($lessons as $lesson): ?>
                        <option value="<?= $lesson ?>"><?= $lesson ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($lessons)): ?>
            <button type="submit">ابدأ الاختبار</button>
            <?php endif; ?> -->
        </form>
    </div>
    
    <script>
        function selectSubject(subject) {
            document.getElementById('selectedSubject').value = subject;
            document.getElementById('examForm').submit();
        }
        
        function selectQuestionType(type) {
            document.getElementById('selectedQuestionType').value = type;
            document.getElementById('examForm').submit();
        }
        
        document.getElementById('lesson_name')?.addEventListener('change', function() {
            this.form.submit();
        });
    </script>
<script>
// دالة لإخفاء جميع الأقسام غير المطلوبة
function hideAllSections() {
    const leMatir = document.querySelector('.le-matir');
    const riMatir = document.querySelector('.ri-matir');
    
    if (leMatir) leMatir.style.display = 'none';
    if (riMatir) riMatir.style.display = 'none';
    
    const lessonSelection = document.getElementById('lessonSelection');
    if (lessonSelection) lessonSelection.remove();
}

// دالة لإنشاء قسم اختيار الدروس
function createLessonSelection() {
    // إزالة قسم اختيار الدروس إذا كان موجودًا
    const existingSelection = document.getElementById('lessonSelection');
    if (existingSelection) existingSelection.remove();
    
    // إنشاء العناصر المطلوبة
    const div = document.createElement('div');
    div.id = 'lessonSelection';
    div.style.margin = '20px auto';
    div.style.maxWidth = '500px';
    div.style.textAlign = 'center';
    
    const label = document.createElement('label');
    label.htmlFor = 'lesson_name';
    label.textContent = 'اختر الدرس:';
    label.style.display = 'block';
    label.style.marginBottom = '10px';
    label.style.fontWeight = 'bold';
    
    const select = document.createElement('select');
    select.name = 'lesson_name';
    select.id = 'lesson_name';
    select.required = true;
    select.style.width = '100%';
    select.style.padding = '10px';
    select.style.borderRadius = '5px';
    select.style.border = '1px solid #ddd';
    
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = '-- اختر الدرس --';
    select.appendChild(defaultOption);
    
    // إضافة الأزرار
    const buttonDiv = document.createElement('div');
    buttonDiv.style.marginTop = '20px';
    
    const submitButton = document.createElement('button');
    submitButton.type = 'submit';
    submitButton.textContent = 'ابدأ الاختبار';
    submitButton.style.padding = '10px 20px';
    submitButton.style.backgroundColor = '#4CAF50';
    submitButton.style.color = 'white';
    submitButton.style.border = 'none';
    submitButton.style.borderRadius = '5px';
    submitButton.style.cursor = 'pointer';
    
    const backButton = document.createElement('button');
    backButton.type = 'button';
    backButton.textContent = 'رجوع';
    backButton.style.padding = '10px 20px';
    backButton.style.marginRight = '10px';
    backButton.style.backgroundColor = '#f44336';
    backButton.style.color = 'white';
    backButton.style.border = 'none';
    backButton.style.borderRadius = '5px';
    backButton.style.cursor = 'pointer';
    backButton.onclick = function() {
        hideAllSections();
        const leMatir = document.querySelector('.le-matir');
        const riMatir = document.querySelector('.ri-matir');
        if (leMatir) leMatir.style.display = 'block';
        if (riMatir) riMatir.style.display = 'block';
    };
    
    buttonDiv.appendChild(backButton);
    buttonDiv.appendChild(submitButton);
    
    div.appendChild(label);
    div.appendChild(select);
    div.appendChild(buttonDiv);
    
    // إضافة العناصر إلى الفورم
    const form = document.getElementById('examForm');
    if (form) form.appendChild(div);
    
    // تعبئة الدروس المتاحة إذا كانت موجودة
    const lessons = <?= json_encode($lessons ?? []) ?>;
    if (lessons && lessons.length > 0) {
        lessons.forEach(lesson => {
            const option = document.createElement('option');
            option.value = lesson;
            option.textContent = lesson;
            select.appendChild(option);
        });
    }
}

// دالة لاختيار المادة
function selectSubject(subject) {
    const subjectInput = document.getElementById('selectedSubject');
    if (subjectInput) {
        subjectInput.value = subject;
        document.getElementById('examForm').submit();
    }
    
    // إخفاء قسم المواد وإظهار قسم أنواع الاختبارات
    const matStudy = document.querySelector('.mat-study');
    if (matStudy) matStudy.style.display = 'none';
    
    const leMatir = document.querySelector('.le-matir');
    const riMatir = document.querySelector('.ri-matir');
    if (leMatir) leMatir.style.display = 'block';
    if (riMatir) riMatir.style.display = 'block';
}

// دالة لاختيار نوع الاختبار
function selectQuestionType(type) {
    const typeInput = document.getElementById('selectedQuestionType');
    if (typeInput) {
        typeInput.value = type;
        document.getElementById('examForm').submit();
    }
    
    // إخفاء قسم أنواع الاختبارات وإظهار قسم الدروس
    const leMatir = document.querySelector('.le-matir');
    const riMatir = document.querySelector('.ri-matir');
    if (leMatir) leMatir.style.display = 'none';
    if (riMatir) riMatir.style.display = 'none';
    
    createLessonSelection();
}

// تهيئة الصفحة عند التحميل
document.addEventListener('DOMContentLoaded', function() {
    // إخفاء الأقسام غير الضرورية في البداية
    const subjectInput = document.getElementById('selectedSubject');
    const typeInput = document.getElementById('selectedQuestionType');
    
    const matStudy = document.querySelector('.mat-study');
    const leMatir = document.querySelector('.le-matir');
    const riMatir = document.querySelector('.ri-matir');
    
    if (subjectInput && subjectInput.value) {
        if (matStudy) matStudy.style.display = 'none';
        
        if (typeInput && typeInput.value) {
            if (leMatir) leMatir.style.display = 'none';
            if (riMatir) riMatir.style.display = 'none';
            createLessonSelection();
        } else {
            if (leMatir) leMatir.style.display = 'block';
            if (riMatir) riMatir.style.display = 'block';
        }
    } else {
        if (leMatir) leMatir.style.display = 'none';
        if (riMatir) riMatir.style.display = 'none';
    }
    
    // إضافة حدث تغيير لاختيار الدرس
    const examForm = document.getElementById('examForm');
    if (examForm) {
        examForm.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'lesson_name') {
                this.submit();
            }
        });
    }
});
</script>
</body>
</html>