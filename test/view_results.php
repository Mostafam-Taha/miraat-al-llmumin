<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['competition_id'])) {
    header("Location: competition.php");
    exit();
}

$competition_id = intval($_GET['competition_id']);
$user_id = $_SESSION['user_id'];

// جلب معلومات المنافسة
$stmt = $pdo->prepare("SELECT * FROM competitions WHERE id = ? AND (player1_id = ? OR player2_id = ?)");
$stmt->execute([$competition_id, $user_id, $user_id]);
$competition = $stmt->fetch();

if (!$competition) {
    header("Location: competition.php");
    exit();
}

// تحديد إذا كان المستخدم هو اللاعب الأول أو الثاني
$is_player1 = ($competition['player1_id'] == $user_id);
$my_score = $is_player1 ? $competition['player1_score'] : $competition['player2_score'];
$opponent_score = $is_player1 ? $competition['player2_score'] : $competition['player1_score'];

// جلب معلومات المنافس
$opponent_id = $is_player1 ? $competition['player2_id'] : $competition['player1_id'];
$stmt = $pdo->prepare("SELECT username, avatar FROM users WHERE id = ?");
$stmt->execute([$opponent_id]);
$opponent = $stmt->fetch();

// جلب الأسئلة والإجابات
$stmt = $pdo->prepare("SELECT q.*, cq.player1_answer, cq.player2_answer 
                      FROM competition_questions cq 
                      JOIN questions q ON cq.question_id = q.id 
                      WHERE cq.competition_id = ? 
                      ORDER BY cq.order_num");
$stmt->execute([$competition_id]);
$questions = $stmt->fetchAll();

// تحديد نتيجة المنافسة
$result = '';
if ($competition['winner_id'] == $user_id) {
    $result = 'فوز';
} elseif ($competition['winner_id'] == $opponent_id) {
    $result = 'خسارة';
} else {
    $result = 'تعادل';
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتيجة المنافسة</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        .result-card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .winner {
            border: 3px solid #198754;
        }
        .loser {
            border: 3px solid #dc3545;
        }
        .draw {
            border: 3px solid #ffc107;
        }
        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        .correct-answer {
            background-color: #d1e7dd;
        }
        .wrong-answer {
            background-color: #f8d7da;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-5">نتيجة المنافسة</h1>
        
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="card result-card <?= $result == 'فوز' ? 'winner' : ($result == 'خسارة' ? 'loser' : 'draw') ?>">
                    <div class="card-body text-center py-4">
                        <h2 class="mb-4"><?= htmlspecialchars($competition['subject']) ?></h2>
                        
                        <div class="d-flex justify-content-around align-items-center mb-4">
                            <div class="text-center">
                                <img src="<?= $opponent['avatar'] ? 'uploads/avatars/' . htmlspecialchars($opponent['avatar']) : 'images/default-avatar.png' ?>" class="avatar mb-2">
                                <h4><?= htmlspecialchars($opponent['username']) ?></h4>
                                <h3 class="text-danger"><?= $opponent_score ?></h3>
                            </div>
                            
                            <div class="vs-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <h5 class="mb-0">VS</h5>
                            </div>
                            
                            <div class="text-center">
                                <?php 
                                $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
                                $stmt->execute([$user_id]);
                                $user_avatar = $stmt->fetch()['avatar'];
                                ?>
                                <img src="<?= $user_avatar ? 'uploads/avatars/' . htmlspecialchars($user_avatar) : 'images/default-avatar.png' ?>" class="avatar mb-2">
                                <h4><?= htmlspecialchars($_SESSION['username']) ?></h4>
                                <h3 class="text-success"><?= $my_score ?></h3>
                            </div>
                        </div>
                        
                        <h2 class="text-<?= $result == 'فوز' ? 'success' : ($result == 'خسارة' ? 'danger' : 'warning') ?>">
                            <?= $result ?>
                        </h2>
                        
                        <p class="mt-3"><?= date('Y-m-d H:i', strtotime($competition['finished_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <h2 class="mb-4">تفاصيل الأسئلة</h2>
        <?php foreach ($questions as $index => $question): 
            $my_answer = $is_player1 ? $question['player1_answer'] : $question['player2_answer'];
            $is_correct = ($my_answer == $question['correct_answer']);
        ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">سؤال <?= $index + 1 ?></h4>
                    <span class="badge bg-<?= $is_correct ? 'success' : 'danger' ?>">
                        <?= $is_correct ? 'إجابة صحيحة' : 'إجابة خاطئة' ?>
                    </span>
                </div>
                <div class="card-body">
                    <p class="card-text"><?= nl2br(htmlspecialchars($question['question_text'])) ?></p>
                    
                    <?php if ($question['question_image']): ?>
                        <img src="uploads/questions/<?= htmlspecialchars($question['question_image']) ?>" class="img-fluid mb-3" style="max-height: 200px;">
                    <?php endif; ?>
                    
                    <div class="options">
                        <?php for ($i = 1; $i <= 4; $i++): 
                            $option = 'option' . $i;
                            $is_my_answer = ($my_answer == $i);
                            $is_correct_option = ($question['correct_answer'] == $i);
                        ?>
                            <div class="p-3 mb-2 rounded <?= $is_my_answer ? ($is_correct ? 'correct-answer' : 'wrong-answer') : ($is_correct_option ? 'correct-answer' : '') ?>">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" disabled <?= $is_my_answer ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= htmlspecialchars($question[$option]) ?>
                                        <?php if ($is_correct_option): ?>
                                            <span class="text-success">(الإجابة الصحيحة)</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="text-center mt-4">
            <a href="competition.php" class="btn btn-primary btn-lg">العودة إلى المنافسات</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>