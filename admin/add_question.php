<?php
// استدعاء اتصال قاعدة البيانات
require_once '../php/config.php';
require_once '../php/check_session.php';

// متغير لتخزين بيانات آخر سؤال مضاف
$last_question = null;

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // تنظيف المدخلات
    $question_text = sanitizeInput($_POST['question_text']);
    $option1 = sanitizeInput($_POST['option1']);
    $option2 = sanitizeInput($_POST['option2']);
    $option3 = sanitizeInput($_POST['option3']);
    $option4 = sanitizeInput($_POST['option4']);
    $correct_answer = (int)$_POST['correct_answer'];
    $subject = sanitizeInput($_POST['subject']);
    $lesson_name = sanitizeInput($_POST['lesson_name']);
    $question_type = sanitizeInput($_POST['question_type']);
    $note1 = isset($_POST['note1']) ? sanitizeInput($_POST['note1']) : null;
    $note2 = isset($_POST['note2']) ? sanitizeInput($_POST['note2']) : null;
    $note3 = isset($_POST['note3']) ? sanitizeInput($_POST['note3']) : null;
    $note4 = isset($_POST['note4']) ? sanitizeInput($_POST['note4']) : null;
    $added_by = $_SESSION['user_id'];
    $question_image = null;
    $difficulty_level = sanitizeInput($_POST['difficulty_level']);

    // معالجة رفع الصورة
    if (isset($_FILES['question_image']) && $_FILES['question_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/questions/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($fileInfo, $_FILES['question_image']['tmp_name']);
        finfo_close($fileInfo);

        if (in_array($detectedType, $allowedTypes)) {
            $extension = pathinfo($_FILES['question_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('question_') . '.' . $extension;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['question_image']['tmp_name'], $destination)) {
                $question_image = $destination;
            } else {
                $error_message = "فشل في رفع الصورة.";
            }
        } else {
            $error_message = "نوع الملف غير مسموح به. يرجى رفع صورة (JPEG, PNG, GIF) فقط.";
        }
    }

    if (!isset($error_message)) {
        try {
            // إعداد استعلام الإدراج
            $stmt = $pdo->prepare("INSERT INTO questions 
                (question_text, question_image, option1, option2, option3, option4, 
                correct_answer, subject, lesson_name, question_type, note1, note2, note3, note4, added_by, difficulty_level) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // تنفيذ الاستعلام مع إضافة lesson_name
            $stmt->execute([
                $question_text, $question_image, $option1, $option2, $option3, $option4, 
                $correct_answer, $subject, $lesson_name, $question_type, 
                $note1, $note2, $note3, $note4, $added_by, $difficulty_level
            ]);

            // استرجاع آخر سؤال تمت إضافته بواسطة هذا المستخدم
            $stmt = $pdo->prepare("SELECT * FROM questions WHERE added_by = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$added_by]);
            $last_question = $stmt->fetch(PDO::FETCH_ASSOC);

            $success_message = "تمت إضافة السؤال بنجاح!";
        } catch (PDOException $e) {
            $error_message = "حدث خطأ أثناء إضافة السؤال: " . $e->getMessage();
        }
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
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="icon" href="../image/logo/book-open-reader-solid.svg" type="image/svg">
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-B2Z6G6EY81"></script>
    <script>window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}gtag('js', new Date());gtag('config', 'G-B2Z6G6EY81');</script>
    <link rel="stylesheet" href="../css/add_question.css">
    <link rel="stylesheet" href="../css/Dark Mode.css">
    <title>Sahat Al-llm - Add questions</title>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-K73Z87CS');</script>
    <!-- End Google Tag Manager -->
    <style>
        body.dark-mode .container {
    background-color: #121212;
    color: #e0e0e0;
}

        body.dark-mode {
            background-color: #101010;
            color: #e0e0e0;
        }

        body.dark-mode .card-body {
            background-color: #101010;
            color: #e0e0e0;
        }

        body.dark-mode .card-body label {
            color: #e0e0e0;
        }

        body.dark-mode .form-control {
            background-color: #1a1a1a;
            color: #e0e0e0;
            border: 1px solid #333;
        }

        body.dark-mode .options-container {
            background-color: #101010;
            color: #e0e0e0;
            border: 1px solid #333;
        }

        body.dark-mode .option-item {
            background-color: #101010;
            color: #e0e0e0;
        }

        /* إشعار آخر سؤال - تصميم متقدم */
        .last-question-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .last-question-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }
        
        .last-question-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, #4CAF50, #8BC34A);
        }
        
        .last-question-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .last-question-icon {
            width: 40px;
            height: 40px;
            background-color: #4CAF50;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            font-size: 18px;
        }
        
        .last-question-title {
            font-weight: 700;
            color: #2E7D32;
            margin: 0;
            font-size: 1.2rem;
        }
        
        .last-question-text {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            line-height: 1.6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03);
            border-left: 3px solid #4CAF50;
        }
        
        .last-question-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        
        .last-question-item {
            background-color: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        
        .last-question-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }
        
        .last-question-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
        }
        
        .last-question-value {
            font-weight: 600;
            color: #343a40;
            font-size: 0.95rem;
        }
        
        .correct-answer {
            background-color: #E8F5E9;
            border-left: 3px solid #4CAF50;
        }
        
        /* Dark Mode Styles */
        body.dark-mode .last-question-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
        
        body.dark-mode .last-question-text,
        body.dark-mode .last-question-item {
            background-color: #252525;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        body.dark-mode .last-question-value {
            color: #f8f9fa;
        }
        
        body.dark-mode .last-question-label {
            color: #b0b0b0;
        }
        
        body.dark-mode .correct-answer {
            background-color: #1B5E20;
            border-left: 3px solid #4CAF50;
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .last-question-card {
            animation: fadeIn 0.5s ease-out;
        }
        /* في قسم الـ style */
        .easy-level {
            color: #4CAF50;
        }

        .medium-level {
            color: #FFC107;
        }

        .hard-level {
            color: #F44336;
        }

        /* في كود PHP لعرض مستوى الصعوبة */
        <span class="last-question-value <?php 
            echo $last_question['difficulty_level'] === 'سهل' ? 'easy-level' : 
                ($last_question['difficulty_level'] === 'صعب' ? 'hard-level' : 'medium-level'); 
        ?>">
            <?php echo htmlspecialchars($last_question['difficulty_level']); ?>
        </span>
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1><i class="fas fa-question-circle"></i> إضافة سؤال جديد</h1>
            </div>
            
            <div class="card-body">
                <?php if (isset($success_message) && $last_question): ?>
                    <div class="last-question-card">
                        <div class="last-question-header">
                            <div class="last-question-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <h3 class="last-question-title">تمت إضافة السؤال بنجاح</h3>
                        </div>
                        
                        <div class="last-question-text">
                            <?php 
                            $short_text = mb_substr($last_question['question_text'], 0, 150, 'UTF-8');
                            if (mb_strlen($last_question['question_text'], 'UTF-8') > 150) {
                                $short_text .= '...';
                            }
                            echo htmlspecialchars($short_text);
                            ?>
                        </div>
                        
                        <div class="last-question-grid">
                            <div class="last-question-item correct-answer">
                                <span class="last-question-label">الإجابة الصحيحة</span>
                                <span class="last-question-value">
                                    <?php echo htmlspecialchars($last_question['option'.$last_question['correct_answer']]); ?>
                                </span>
                            </div>
                            
                            <div class="last-question-item">
                                <span class="last-question-label">المادة الدراسية</span>
                                <span class="last-question-value">
                                    <?php echo htmlspecialchars($last_question['subject']); ?>
                                </span>
                            </div>
                            
                            <div class="last-question-item">
                                <span class="last-question-label">اسم الدرس</span>
                                <span class="last-question-value">
                                    <?php echo htmlspecialchars($last_question['lesson_name']); ?>
                                </span>
                            </div>
                            
                            <div class="last-question-item">
                                <span class="last-question-label">نوع السؤال</span>
                                <span class="last-question-value">
                                    <?php echo htmlspecialchars($last_question['question_type']); ?>
                                </span>
                            </div>
                            <div class="last-question-item">
                                <span class="last-question-label">مستوى الصعوبة</span>
                                <span class="last-question-value">
                                    <?php echo htmlspecialchars($last_question['difficulty_level']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                            
                <?php if (isset($success_message)): ?>
                    <div class="message success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="message error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="question_text">نص السؤال</label>
                        <textarea id="question_text" name="question_text" class="form-control" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>صورة السؤال (اختياري)</label>
                        <div class="image-upload-container">
                            <div class="image-upload-wrapper">
                                <label for="question_image" class="custom-file-upload">
                                    <i class="fas fa-cloud-upload-alt"></i> اختر صورة للسؤال
                                </label>
                                <input type="file" id="question_image" name="question_image" accept="image/*" style="display: none;">
                                <div id="file-name"></div>
                            </div>
                            <img id="imagePreview" class="image-preview" src="#" alt="معاينة الصورة">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>خيارات الإجابة</label>
                        <div class="options-container">
                        <div class="option-item">
                                <label for="option1">الاختيار الأول</label>
                                <input type="text" id="option1" name="option1" class="form-control" required>
                                
                                <div class="note-input">
                                    <label for="note1">ملاحظة (اختيارية)</label>
                                    <input type="text" id="note1" name="note1" class="form-control">
                                </div>
                            </div>
                            
                            <div class="option-item">
                                <label for="option2">الاختيار الثاني</label>
                                <input type="text" id="option2" name="option2" class="form-control" required>
                            
                            <div class="note-input">
                                    <label for="note2">ملاحظة (اختيارية)</label>
                                    <input type="text" id="note2" name="note2" class="form-control">
                                </div>
                            </div>
                            
                            <div class="option-item">
                                <label for="option3">الاختيار الثالث</label>
                                <input type="text" id="option3" name="option3" class="form-control" required>
                                
                                <div class="note-input">
                                    <label for="note3">ملاحظة (اختيارية)</label>
                                    <input type="text" id="note3" name="note3" class="form-control">
                                </div>
                            </div>
                            
                            <div class="option-item">
                                <label for="option4">الاختيار الرابع</label>
                                <input type="text" id="option4" name="option4" class="form-control" required>
                                
                                <div class="note-input">
                                    <label for="note4">ملاحظة (اختيارية)</label>
                                    <input type="text" id="note4" name="note4" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="correct_answer">الإجابة الصحيحة</label>
                        <div class="select-wrapper">
                            <select id="correct_answer" name="correct_answer" class="form-control" required>
                                <option value="1">الاختيار الأول</option>
                                <option value="2">الاختيار الثاني</option>
                                <option value="3">الاختيار الثالث</option>
                                <option value="4">الاختيار الرابع</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">المادة الدراسية</label>
                        <input type="text" id="subject" name="subject" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="lesson_name">اسم الدرس</label>
                        <input type="text" id="lesson_name" name="lesson_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="difficulty_level">مستوى الصعوبة</label>
                        <div class="select-wrapper">
                            <select id="difficulty_level" name="difficulty_level" class="form-control" required>
                                <option value="سهل">سهل</option>
                                <option value="متوسط" selected>متوسط</option>
                                <option value="صعب">صعب</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="question_type">نوع السؤال</label>
                        <div class="select-wrapper">
                            <select id="question_type" name="question_type" class="form-control" required>
                                <option value="بنك اسئلة">بنك اسئلة</option>
                                <option value="اختبارات شاملة">اختبارات شاملة</option>
                                <option value="تحدى نفسك">تحدى نفسك</option>
                                <option value="إمتحان الوزارة">إمتحان الوزارة</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-block">
                            <i class="fas fa-plus-circle"></i> إضافة السؤال
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // وظيفة لتبديل الوضع بين الفاتح والداكن
            function toggleTheme() {
                document.body.classList.toggle('dark-mode');
                
                const isDarkMode = document.body.classList.contains('dark-mode');
                const darkButton = document.querySelector('.dark-light div:first-child');
                const lightButton = document.querySelector('.dark-light div:last-child');
                
                if (isDarkMode) {
                    if (darkButton) darkButton.classList.add('active');
                    if (lightButton) lightButton.classList.remove('active');
                    localStorage.setItem('theme', 'dark'); // حفظ الوضع الداكن في localStorage
                } else {
                    if (darkButton) darkButton.classList.remove('active');
                    if (lightButton) lightButton.classList.add('active');
                    localStorage.setItem('theme', 'light'); // حفظ الوضع الفاتح في localStorage
                }
            }

            // ضبط الوضع الافتراضي بناءً على التفضيل المحفوظ في localStorage
            document.addEventListener('DOMContentLoaded', function() {
                const savedTheme = localStorage.getItem('theme');
                const darkButton = document.querySelector('.dark-light div:first-child');
                const lightButton = document.querySelector('.dark-light div:last-child');
                
                if (savedTheme === 'dark') {
                    document.body.classList.add('dark-mode');
                    if (darkButton) darkButton.classList.add('active');
                } else {
                    if (lightButton) lightButton.classList.add('active');
                }
                
                // إضافة حدث للنقر على زر تبديل الوضع
                const themeToggle = document.querySelector('.dark-light');
                if (themeToggle) {
                    themeToggle.addEventListener('click', toggleTheme);
                }
            });
    </script>
    <script>
        // عرض معاينة الصورة قبل الرفع
        document.getElementById('question_image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const fileNameElement = document.getElementById('file-name');
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                fileNameElement.textContent = 'تم اختيار: ' + file.name;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                fileNameElement.textContent = '';
                preview.style.display = 'none';
            }
        });

        // إضافة تأثير عند التركيز على الحقول
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
        </script>
        <script>
        // إضافة تأثيرات لبطاقة آخر سؤال
        document.addEventListener('DOMContentLoaded', function() {
            const lastQuestionCard = document.querySelector('.last-question-card');
            
            if (lastQuestionCard) {
                // تأثير عند الظهور
                setTimeout(() => {
                    lastQuestionCard.style.opacity = '1';
                }, 100);
                
                // إمكانية إغلاق البطاقة
                const closeBtn = document.createElement('button');
                closeBtn.innerHTML = '<i class="fas fa-times"></i>';
                closeBtn.style.position = 'absolute';
                closeBtn.style.top = '15px';
                closeBtn.style.left = '15px';
                closeBtn.style.background = 'transparent';
                closeBtn.style.border = 'none';
                closeBtn.style.color = '#6c757d';
                closeBtn.style.cursor = 'pointer';
                closeBtn.style.fontSize = '1rem';
                closeBtn.style.transition = 'color 0.2s';
                
                closeBtn.addEventListener('mouseover', () => {
                    closeBtn.style.color = '#f44336';
                });
                
                closeBtn.addEventListener('mouseout', () => {
                    closeBtn.style.color = '#6c757d';
                });
                
                closeBtn.addEventListener('click', () => {
                    lastQuestionCard.style.opacity = '0';
                    setTimeout(() => {
                        lastQuestionCard.style.display = 'none';
                    }, 300);
                });
                
                lastQuestionCard.appendChild(closeBtn);
            }
        });


        document.addEventListener('DOMContentLoaded', function() {
            // عناصر الإدخال للاختيارات والملاحظات
            const inputs = {
                option1: document.getElementById('option1'),
                note1: document.getElementById('note1'),
                option2: document.getElementById('option2'),
                note2: document.getElementById('note2'),
                option3: document.getElementById('option3'),
                note3: document.getElementById('note3'),
                option4: document.getElementById('option4'),
                note4: document.getElementById('note4')
            };

            // تحميل البيانات المحفوظة عند تحميل الصفحة
            function loadSavedData() {
                for (let i = 1; i <= 3; i++) {
                const optionKey = `option${i}`;
                const noteKey = `note${i}`;
                
                if (localStorage.getItem(optionKey)) {
                    inputs[optionKey].value = localStorage.getItem(optionKey);
                }
                if (localStorage.getItem(noteKey)) {
                    inputs[noteKey].value = localStorage.getItem(noteKey);
                }
                }
            }

            // حفظ البيانات عند تغييرها
            function setupEventListeners() {
                for (let i = 1; i <= 3; i++) {
                const optionKey = `option${i}`;
                const noteKey = `note${i}`;
                
                inputs[optionKey].addEventListener('input', function() {
                    localStorage.setItem(optionKey, this.value);
                });
                
                inputs[noteKey].addEventListener('input', function() {
                    localStorage.setItem(noteKey, this.value);
                });
                }
                
                // مسح أي بيانات محفوظة للاختيار الرابع (إن وجدت)
                localStorage.removeItem('option4');
                localStorage.removeItem('note4');
            }

            // تهيئة الصفحة
            loadSavedData();
            setupEventListeners();
        });
    </script>
</body>
</html>