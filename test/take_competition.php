<?php
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['competition_id'])) {
    header("Location: competition.php");
    exit();
}

$competition_id = intval($_GET['competition_id']);
$user_id = $_SESSION['user_id'];

// التحقق من أن المستخدم مشارك في المنافسة
$stmt = $pdo->prepare("SELECT * FROM competitions WHERE id = ? AND (player1_id = ? OR player2_id = ?) AND status = 'in_progress'");
$stmt->execute([$competition_id, $user_id, $user_id]);
$competition = $stmt->fetch();

if (!$competition) {
    header("Location: competition.php");
    exit();
}

// تحديد إذا كان المستخدم هو اللاعب الأول أو الثاني
$is_player1 = ($competition['player1_id'] == $user_id);

// معالجة إجابات الأسئلة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_answers'])) {
    $answers = $_POST['answers'];
    
    // تحديث إجابات المستخدم
    foreach ($answers as $question_id => $answer) {
        $field = $is_player1 ? 'player1_answer' : 'player2_answer';
        $stmt = $pdo->prepare("UPDATE competition_questions SET $field = ? WHERE competition_id = ? AND question_id = ?");
        $stmt->execute([$answer, $competition_id, $question_id]);
    }
    
    // التحقق إذا كان كلا اللاعبين قد أكملوا الاختبار
    $stmt = $pdo->prepare("SELECT COUNT(*) as unanswered FROM competition_questions 
                          WHERE competition_id = ? AND (player1_answer IS NULL OR player2_answer IS NULL)");
    $stmt->execute([$competition_id]);
    $unanswered = $stmt->fetch()['unanswered'];
    
    if ($unanswered == 0) {
        // حساب النتائج
        calculate_results($pdo, $competition_id);
    }
    
    // توجيه إلى صفحة النتائج
    header("Location: view_results.php?competition_id=" . $competition_id);
    exit();
}

// جلب أسئلة المنافسة
$stmt = $pdo->prepare("SELECT q.*, cq.order_num 
                      FROM competition_questions cq 
                      JOIN questions q ON cq.question_id = q.id 
                      WHERE cq.competition_id = ? 
                      ORDER BY cq.order_num");
$stmt->execute([$competition_id]);
$questions = $stmt->fetchAll();

// دالة لحساب النتائج
function calculate_results($pdo, $competition_id) {
    // جلب المنافسة
    $stmt = $pdo->prepare("SELECT * FROM competitions WHERE id = ?");
    $stmt->execute([$competition_id]);
    $competition = $stmt->fetch();
    
    // جلب الأسئلة والإجابات
    $stmt = $pdo->prepare("SELECT * FROM competition_questions WHERE competition_id = ?");
    $stmt->execute([$competition_id]);
    $questions = $stmt->fetchAll();
    
    // حساب النقاط
    $player1_score = 0;
    $player2_score = 0;
    
    foreach ($questions as $q) {
        if ($q['player1_answer'] == $q['correct_answer']) {
            $player1_score++;
        }
        if ($q['player2_answer'] == $q['correct_answer']) {
            $player2_score++;
        }
    }
    
    // تحديد الفائز
    $winner_id = null;
    if ($player1_score > $player2_score) {
        $winner_id = $competition['player1_id'];
    } elseif ($player2_score > $player1_score) {
        $winner_id = $competition['player2_id'];
    }
    
    // تحديث المنافسة بالنتائج
    $stmt = $pdo->prepare("UPDATE competitions 
                          SET player1_score = ?, player2_score = ?, winner_id = ?, status = 'completed', finished_at = NOW() 
                          WHERE id = ?");
    $stmt->execute([$player1_score, $player2_score, $winner_id, $competition_id]);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المنافسة</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        .question-card {
            margin-bottom: 20px;
            border-left: 5px solid #0d6efd;
        }
        .question-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
        }
        .timer {
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>منافسة في <?= htmlspecialchars($competition['subject']) ?></h1>
            <div class="timer" id="timer">20:00</div>
        </div>
        
        <div class="alert alert-info mb-4">
            <h4>منافسك: 
                <?php 
                $opponent_id = $is_player1 ? $competition['player2_id'] : $competition['player1_id'];
                $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                $stmt->execute([$opponent_id]);
                $opponent = $stmt->fetch();
                echo htmlspecialchars($opponent['username']);
                ?>
            </h4>
            <p>عدد الأسئلة: <?= count($questions) ?></p>
            <p>المستوى: <?= htmlspecialchars($competition['difficulty_level']) ?></p>
        </div>
        
        <form method="POST">
            <?php foreach ($questions as $index => $question): ?>
                <div class="card question-card mb-4">
                    <div class="card-header">
                        <h3>سؤال <?= $index + 1 ?></h3>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?= nl2br(htmlspecialchars($question['question_text'])) ?></p>
                        
                        <?php if ($question['question_image']): ?>
                            <img src="uploads/questions/<?= htmlspecialchars($question['question_image']) ?>" class="question-image">
                        <?php endif; ?>
                        
                        <div class="options mt-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="answers[<?= $question['id'] ?>]" id="q<?= $question['id'] ?>_1" value="1">
                                <label class="form-check-label" for="q<?= $question['id'] ?>_1"><?= htmlspecialchars($question['option1']) ?></label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="answers[<?= $question['id'] ?>]" id="q<?= $question['id'] ?>_2" value="2">
                                <label class="form-check-label" for="q<?= $question['id'] ?>_2"><?= htmlspecialchars($question['option2']) ?></label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="answers[<?= $question['id'] ?>]" id="q<?= $question['id'] ?>_3" value="3">
                                <label class="form-check-label" for="q<?= $question['id'] ?>_3"><?= htmlspecialchars($question['option3']) ?></label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="answers[<?= $question['id'] ?>]" id="q<?= $question['id'] ?>_4" value="4">
                                <label class="form-check-label" for="q<?= $question['id'] ?>_4"><?= htmlspecialchars($question['option4']) ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="text-center mt-4">
                <button type="submit" name="submit_answers" class="btn btn-primary btn-lg">إنهاء المنافسة</button>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // عداد تنازلي لمدة 20 دقيقة (1200 ثانية)
        let timeLeft = 1200;
        
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('timer').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                // انتهى الوقت - إرسال النموذج تلقائياً
                document.querySelector('form').submit();
            } else {
                timeLeft--;
                setTimeout(updateTimer, 1000);
            }
        }
        
        // بدء العداد
        updateTimer();
    </script>
</body>
</html>