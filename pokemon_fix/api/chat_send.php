<?php
require_once '../config/session_security.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Harus login terlebih dahulu']);
    exit;
}

$headers = getallheaders();
$token = $headers['X-CSRF-Token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$listingId = (int)($input['listing_id'] ?? 0);
$message = trim($input['message'] ?? '');

if (!$listingId || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak lengkap']);
    exit;
}

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

$senderId = $_SESSION['user_id'];

// Tentukan receiver
if ($senderId == $sellerId) {
    // Penjual: cari pembeli dari pesan sebelumnya
    $stmt = $pdo->prepare('SELECT DISTINCT sender_id FROM chat_messages WHERE listing_id = ? AND sender_id != ? LIMIT 1');
    $stmt->execute([$listingId, $senderId]);
    $receiverId = $stmt->fetchColumn();
    if (!$receiverId) {
        http_response_code(400);
        echo json_encode(['error' => 'Belum ada pesan dari pembeli, tidak bisa membalas']);
        exit;
    }
} else {
    // Pembeli: receiver adalah seller
    $receiverId = $sellerId;
}

if ($senderId == $receiverId) {
    http_response_code(403);
    echo json_encode(['error' => 'Tidak bisa chat dengan diri sendiri']);
    exit;
}

// Simpan pesan
$stmt = $pdo->prepare('INSERT INTO chat_messages (listing_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)');
if ($stmt->execute([$listingId, $senderId, $receiverId, $message])) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengirim pesan']);
}
?>