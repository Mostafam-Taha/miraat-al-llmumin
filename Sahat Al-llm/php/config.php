<?php
$host = 'localhost';
$dbname = 'sahat_alllm';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // استخدام القيمة الرقمية مباشرة بدلاً من الثابت (3 = PDO::ATTR_ERRMODE_EXCEPTION)
    $pdo->setAttribute(3, 2); // 3 = ATTR_ERRMODE, 2 = ERRMODE_EXCEPTION
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>