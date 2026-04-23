<?php
require_once '../config/session_security.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$listingId = (int)($_GET['listing_id'] ?? 0);
if (!$listingId) {
    http_response_code(400);
    echo json_encode(['error' => 'ID listing diperlukan']);
    exit;
}

require_once '../config/db.php';
$db = new Database();
$pdo = $db->getConnection();

// Ambil seller dari listing
$stmt = $pdo->prepare('SELECT user_id FROM listings WHERE id = ?');
$stmt->execute([$listingId]);
$sellerId = $stmt->fetchColumn();
if (!$sellerId) {
    http_response_code(404);
    echo json_encode(['error' => 'Listing tidak ditemukan']);
    exit;
}

$userId = $_SESSION['user_id'];

// Cek apakah user login adalah seller atau pernah mengirim/menerima pesan di listing ini
$stmt = $pdo->prepare('SELECT COUNT(*) FROM chat_messages WHERE listing_id = ? AND (sender_id = ? OR receiver_id = ?)');
$stmt->execute([$listingId, $userId, $userId]);
$hasInteraction = $stmt->fetchColumn() > 0;

if ($userId != $sellerId && !$hasInteraction) {
    // User tidak berhak melihat chat
    echo json_encode(['messages' => [], 'seller_id' => $sellerId, 'error' => 'Tidak ada akses']);
    exit;
}

// Ambil SEMUA pesan untuk listing ini (karena hanya dua pihak yang terlibat)
$stmt = $pdo->prepare('
    SELECT cm.*, u1.username as sender_name, u2.username as receiver_name
    FROM chat_messages cm
    JOIN users u1 ON cm.sender_id = u1.id
    JOIN users u2 ON cm.receiver_id = u2.id
    WHERE cm.listing_id = ?
    ORDER BY cm.created_at ASC
');
$stmt->execute([$listingId]);
$messages = $stmt->fetchAll();

// Tandai pesan yang diterima user sebagai sudah dibaca
$update = $pdo->prepare('UPDATE chat_messages SET is_read = 1 WHERE listing_id = ? AND receiver_id = ?');
$update->execute([$listingId, $userId]);

echo json_encode(['messages' => $messages, 'seller_id' => $sellerId]);