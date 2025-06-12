<?php
session_start();

require_once 'config.php';

// إذا لم يكن المستخدم مسجل دخول، توجيهه لصفحة تسجيل الدخول
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// جلب بيانات المستخدم من قاعدة البيانات إذا لم تكن محملة مسبقًا
if (!isset($_SESSION['user_data'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['user_data'] = $stmt->fetch(PDO::FETCH_ASSOC);
}

// في ملف auth_check.php
if (session_id() != $_COOKIE['PHPSESSID']) {
    session_regenerate_id(true);
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

// جعل بيانات المستخدم متاحة بسهولة
$current_user = $_SESSION['user_data'];
?>