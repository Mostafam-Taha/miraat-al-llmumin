<?php
require_once '../php/config.php';
require_once '../php/check_session.php';

// استعلام للحصول على الأسئلة
try {
    $stmt = $pdo->query("SELECT * FROM questions");
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // معالجة كل سؤال لتحويل الصور إلى base64
    foreach ($questions as &$question) {
        if (!empty($question['question_image'])) {
            $imagePath = './' . $question['question_image']; // المسار الكامل للصورة
            if (file_exists($imagePath)) {
                $imageData = file_get_contents($imagePath);
                $question['question_image'] = base64_encode($imageData);
            } else {
                $question['question_image'] = null; // إذا لم توجد الصورة
            }
        } else {
            $question['question_image'] = null; // إذا لم يكن هناك صورة
        }
        // إزالة مسار الصورة الأصلي إذا كنت تريد ذلك (اختياري)
        unset($question['question_image']);
    }
    
    // تعيين رأس JSON
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="questions_with_images_'.date('Y-m-d').'.json"');
    
    // تصدير البيانات كملف JSON
    echo json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
    
} catch (PDOException $e) {
    die("Error exporting questions: " . $e->getMessage());
}
?>