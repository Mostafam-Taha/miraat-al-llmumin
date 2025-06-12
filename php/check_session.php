<?php
require_once 'config.php';
session_start();

// إذا لم يكن مسجل دخول، توجيه إلى صفحة تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: login.php");
    exit();
}

// التحقق من وجود بيانات الأدمن في الجلسة
if (!isset($_SESSION['admin_data'])) {
    header("Location: login.php");
    exit();
}

// التحقق من انتهاء مدة الجلسة (72 ساعة)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 72 * 60 * 60)) {
    // انتهت مدة الجلسة
    session_unset();
    session_destroy();
    header("Location: login.php?session_expired=1");
    exit();
}

// تحديث وقت النشاط الأخير (اختياري)
$_SESSION['last_activity'] = time();

// يمكنك إضافة المزيد من التحقق هنا مثل:
// - صلاحيات الأدمن
// - حالة الحساب (مفعل/معطل)
// - آخر نشاط
?>