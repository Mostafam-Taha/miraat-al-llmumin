<?php
session_start();
require_once '../php/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['competition_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$competition_id = intval($_GET['competition_id']);
$user_id = $_SESSION['user_id'];

// التحقق من وجود منافس
$stmt = $pdo->prepare("SELECT player2_id FROM competitions WHERE id = ? AND (player1_id = ? OR player2_id = ?)");
$stmt->execute([$competition_id, $user_id, $user_id]);
$competition = $stmt->fetch();

header('Content-Type: application/json');
echo json_encode([
    'found' => ($competition && $competition['player2_id'] !== null)
]);