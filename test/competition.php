<?php
session_start();
require_once '../php/config.php';


// تحسين التحقق من صحة المنافسة
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['competition_id'])) {
    header("Location: competition.php");
    exit();
}

$competition_id = intval($_GET['competition_id']);
$user_id = $_SESSION['user_id'];

// استعلام أكثر دقة للتحقق من صحة المنافسة
$stmt = $pdo->prepare("SELECT * FROM competitions WHERE id = ? AND player1_id = ? AND status = 'waiting'");
$stmt->execute([$competition_id, $user_id]);
$competition = $stmt->fetch();

if (!$competition) {
    // إضافة رسالة خطأ إلى الجلسة قبل إعادة التوجيه
    $_SESSION['error'] = "المنافسة غير موجودة أو غير صالحة";
    header("Location: competition.php");
    exit();
}

// البحث عن منافس
$stmt = $pdo->prepare("SELECT id FROM competitions 
                       WHERE subject = ? AND difficulty_level = ? AND questions_count = ? 
                       AND player1_id != ? AND status = 'waiting' AND id != ?
                       ORDER BY created_at ASC LIMIT 1");
$stmt->execute([
    $competition['subject'],
    $competition['difficulty_level'],
    $competition['questions_count'],
    $user_id,
    $competition_id
]);
$matching_competition = $stmt->fetch();

if ($matching_competition) {
    // وجد منافس - تحديث المنافستين
    $pdo->beginTransaction();
    
    try {
        // تحديث منافسة المستخدم الحالي
        $stmt = $pdo->prepare("UPDATE competitions SET player2_id = ?, status = 'in_progress', started_at = NOW() WHERE id = ?");
        $stmt->execute([$matching_competition['player1_id'], $competition_id]);
        
        // تحديث منافسة المنافس
        $stmt = $pdo->prepare("UPDATE competitions SET player2_id = ?, status = 'in_progress', started_at = NOW() WHERE id = ?");
        $stmt->execute([$user_id, $matching_competition['id']]);
        
        // إنشاء الأسئلة للمنافسة
        generate_competition_questions($pdo, $competition_id);
        
        $pdo->commit();
        
        // توجيه إلى صفحة المنافسة
        header("Location: take_competition.php?competition_id=" . $competition_id);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "حدث خطأ أثناء بدء المنافسة: " . $e->getMessage();
    }
}

// دالة لإنشاء أسئلة المنافسة
function generate_competition_questions($pdo, $competition_id) {
    // جلب معلومات المنافسة
    $stmt = $pdo->prepare("SELECT * FROM competitions WHERE id = ?");
    $stmt->execute([$competition_id]);
    $competition = $stmt->fetch();
    
    // جلب أسئلة عشوائية تناسب المعايير
    $stmt = $pdo->prepare("SELECT id FROM questions 
                          WHERE subject = ? AND difficulty_level = ? 
                          ORDER BY RAND() LIMIT ?");
    $stmt->execute([
        $competition['subject'],
        $competition['difficulty_level'],
        $competition['questions_count']
    ]);
    $questions = $stmt->fetchAll();
    
    // إدراج الأسئلة في جدول المنافسة
    $order_num = 1;
    foreach ($questions as $question) {
        $stmt = $pdo->prepare("INSERT INTO competition_questions (competition_id, question_id, order_num) VALUES (?, ?, ?)");
        $stmt->execute([$competition_id, $question['id'], $order_num]);
        $order_num++;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انتظار المنافس</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        .spinner {
            width: 5rem;
            height: 5rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="mb-4">جاري البحث عن منافس...</h1>
                
                <div class="spinner-border text-primary spinner mb-4" role="status">
                    <span class="visually-hidden">جارٍ التحميل...</span>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h4>تفاصيل المنافسة</h4>
                        <p><strong>المادة:</strong> <?= htmlspecialchars($competition['subject']) ?></p>
                        <p><strong>المستوى:</strong> <?= htmlspecialchars($competition['difficulty_level']) ?></p>
                        <p><strong>عدد الأسئلة:</strong> <?= $competition['questions_count'] ?></p>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <p>سيتم إعلامك بمجرد العثور على منافس مناسب. يمكنك ترك هذه الصفحة مفتوحة.</p>
                    <p>إذا لم يتم العثور على منافس خلال 5 دقائق، سيتم إلغاء المنافسة تلقائياً.</p>
                </div>
                
                <a href="cancel_competition.php?competition_id=<?= $competition_id ?>" class="btn btn-danger">إلغاء البحث</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // التحقق من وجود منافس كل 5 ثوان
        function checkForOpponent() {
            fetch('check_opponent.php?competition_id=<?= $competition_id ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.found) {
                        window.location.href = 'take_competition.php?competition_id=<?= $competition_id ?>';
                    }
                });
        }
        
        // التحقق كل 5 ثوان
        setInterval(checkForOpponent, 5000);
        
        // التحقق فور تحميل الصفحة
        checkForOpponent();
    </script>
</body>
</html>