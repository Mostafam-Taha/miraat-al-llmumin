<?php
require_once '../php/config.php';

function saveMessage($userId, $message, $isBot, $methodUsed) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO messages (user_id, message, is_bot, method_used, sent_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isis", $userId, $message, $isBot, $methodUsed);
    $stmt->execute();
    $stmt->close();
}

function handleMenuOption($option) {
    switch($option) {
        case 'الاستعلام عن الدروس':
            return 'يمكنك الاطلاع على الدروس من خلال زيارة قسم الدروس في لوحة التحكم.';
        case 'الواجبات المطلوبة':
            return 'الواجبات الحالية: \n1. حل تمارين الرياضيات ص 45\n2. كتابة موضوع تعبير\n3. بحث في العلوم';
        case 'الجدول الدراسي':
            return 'جدولك الدراسي لهذا الأسبوع:\nالأحد: رياضيات، علوم\nالاثنين: لغة عربية، تاريخ\n...';
        case 'الامتحانات القادمة':
            return 'الامتحانات القادمة:\n- اختبار الرياضيات: 15 مايو\n- اختبار العلوم: 20 مايو';
        case 'تواصل مع المعلم':
            return 'للتواصل مع المعلم، يرجى إرسال رسالة عبر نظام الرسائل الداخلية أو الاتصال على الرقم 0123456789';
        default:
            return 'تم استلام طلبك. سيتم الرد عليك قريباً.';
    }
}

function getBotResponse($message, $method = 'text') {
    $message = strtolower($message);
    
    if ($method === 'menu') {
        return handleMenuOption($message);
    }
    
    // معالجة الرسائل النصية
    if (strpos($message, 'مرحبا') !== false || strpos($message, 'اهلا') !== false) {
        return 'مرحباً بك! كيف يمكنني مساعدتك اليوم؟';
    } elseif (strpos($message, 'كيف حالك') !== false) {
        return 'أنا بخير، شكراً لسؤالك! كيف يمكنني مساعدتك؟';
    } elseif (strpos($message, 'شكرا') !== false || strpos($message, 'متشكر') !== false) {
        return 'العفو! دائماً في خدمتك. هل لديك أي استفسار آخر؟';
    } else {
        return 'أنا آسف، لم أفهم سؤالك. هل يمكنك إعادة صياغته بطريقة أخرى؟';
    }
}
?>