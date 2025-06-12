<?php
require_once '../php/config.php';

if (!isset($_GET['id'])) {
    die('معرف السؤال غير محدد');
}

$question_id = sanitizeInput($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$question_id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    die('السؤال غير موجود');
}
?>

<div class="question-details">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">السؤال #<?= $question['id'] ?></h5>
        <div>
            <span class="badge bg-primary"><?= $question['subject'] ?></span>
            <span class="badge bg-secondary"><?= $question['lesson_name'] ?></span>
            <span class="badge bg-info text-dark"><?= $question['question_type'] ?></span>
        </div>
    </div>
    
    <div class="question-text mb-3">
        <?= nl2br($question['question_text']) ?>
    </div>
    
    <?php if (!empty($question['question_image'])): ?>
        <div class="text-center mb-3">
            <img src="<?= $question['question_image'] ?>" alt="صورة السؤال" class="img-fluid rounded">
        </div>
    <?php endif; ?>
    
    <div class="options mb-3">
        <h6>الخيارات:</h6>
        <div class="mb-2 <?= $question['correct_answer'] == 1 ? 'correct-answer' : '' ?>">
            <strong>أ:</strong> <?= $question['option1'] ?>
        </div>
        <div class="mb-2 <?= $question['correct_answer'] == 2 ? 'correct-answer' : '' ?>">
            <strong>ب:</strong> <?= $question['option2'] ?>
        </div>
        <div class="mb-2 <?= $question['correct_answer'] == 3 ? 'correct-answer' : '' ?>">
            <strong>ج:</strong> <?= $question['option3'] ?>
        </div>
        <div class="mb-2 <?= $question['correct_answer'] == 4 ? 'correct-answer' : '' ?>">
            <strong>د:</strong> <?= $question['option4'] ?>
        </div>
    </div>
    
    <?php if (!empty($question['modified_date'])): ?>
        <div class="modified-info text-muted small mt-2">
            <i class="bi bi-pencil-square"></i> تم التعديل في: <?= date('Y-m-d H:i', strtotime($question['modified_date'])) ?>
            <?php if (!empty($question['modified_by'])): ?>
                بواسطة: <?= getAdminName($pdo, $question['modified_by']) ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($question['note1']) || !empty($question['note2']) || !empty($question['note3']) || !empty($question['note4'])): ?>
        <div class="notes bg-light p-3 rounded mb-3">
            <h6>ملاحظات:</h6>
            <ul class="mb-0">
                <?php if (!empty($question['note1'])): ?><li><?= $question['note1'] ?></li><?php endif; ?>
                <?php if (!empty($question['note2'])): ?><li><?= $question['note2'] ?></li><?php endif; ?>
                <?php if (!empty($question['note3'])): ?><li><?= $question['note3'] ?></li><?php endif; ?>
                <?php if (!empty($question['note4'])): ?><li><?= $question['note4'] ?></li><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="meta-info text-muted small">
        <div>تاريخ الإضافة: <?= date('Y-m-d H:i', strtotime($question['added_date'])) ?></div>
        <div>مضاف بواسطة: <?= $question['added_by'] ?? 'غير معروف' ?></div>
    </div>
</div>