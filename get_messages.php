<?php
require_once './php/config.php';
header('Content-Type: application/json');

// الحصول على آخر معرف رسالة تم استلامها
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

// الانتظار حتى 30 ثانية لحدوث تغييرات (Long Polling)
$timeout = 30;
$start_time = time();

while (true) {
    $stmt = $pdo->prepare("
        SELECT 
            cm.id, 
            cm.user_id, 
            cm.message, 
            cm.timestamp, 
            cm.file_name, 
            cm.file_path, 
            cm.file_type, 
            cm.file_size,
            u.username, 
            u.avatar
        FROM chat_messages cm
        JOIN users u ON cm.user_id = u.id
        WHERE cm.id > ?
        ORDER BY cm.timestamp ASC
    ");
    $stmt->execute([$last_id]);
    $new_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($new_messages)) {
        echo json_encode($new_messages);
        exit();
    }

    // التحقق من انتهاء الوقت المحدد
    if (time() - $start_time >= $timeout) {
        echo json_encode([]);
        exit();
    }

    // الانتظار لمدة ثانية قبل إعادة المحاولة
    sleep(1);
}
?>