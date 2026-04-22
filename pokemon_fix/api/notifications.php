<?php
require_once '../config/session_security.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
require_once '../config/db.php';

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();

// Hitung belum dibaca
$stmt = $pdo->prepare('SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0');
$stmt->execute([$userId]);
$unread = $stmt->fetch()['unread'];

echo json_encode(['notifications' => $notifications, 'unread' => $unread]);