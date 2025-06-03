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

// جلب الأسئلة من قاعدة البيانات
$stmt = $pdo->prepare("SELECT * FROM questions WHERE subject = ? AND question_type = ? AND lesson_name = ? ORDER BY RAND()");
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
        header("Location: profile.php?result_id=" . $examResultId);
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
    <title>الاختبار</title>
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Tajawal', sans-serif;
        }
        .exam-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            margin-bottom: 30px;
            padding: 30px;
        }
        .exam-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .question-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #eee;
            cursor: pointer;
            transition: transform 0.3s;
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
        }
        .timer {
            font-size: 1.2rem;
            font-weight: bold;
            color: #dc3545;
        }
    </style>
</head>
<body>
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
    </script>
</body>
</html>