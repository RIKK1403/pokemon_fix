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

if (!$listingId) {
    http_response_code(400);
    echo json_encode(['error' => 'ID listing tidak valid']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare('SELECT * FROM listings WHERE id = ? AND type = "auction" AND buy_now_price IS NOT NULL');
$stmt->execute([$listingId]);
$listing = $stmt->fetch();

if (!$listing) {
    http_response_code(404);
    echo json_encode(['error' => 'Listing tidak ditemukan atau tidak memiliki opsi buy now']);
    exit;
}

if ($listing['user_id'] == $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'Anda tidak bisa membeli listing milik sendiri']);
    exit;
}

if (strtotime($listing['end_time']) <= time()) {
    http_response_code(400);
    echo json_encode(['error' => 'Lelang sudah berakhir']);
    exit;
}

$bids = json_decode($listing['bids'] ?? '[]', true);
$bidderWhatsapp = $_SESSION['user']['whatsapp'] ?? '';
$bids[] = [
    'amount' => $listing['buy_now_price'],
    'bidder_name' => $_SESSION['user']['username'],
    'bidder_id' => $_SESSION['user_id'],
    'bidder_whatsapp' => $bidderWhatsapp,
    'time' => date('Y-m-d H:i:s'),
    'type' => 'buy_now'
];

$stmt = $pdo->prepare('UPDATE listings SET end_time = NOW(), bids = ? WHERE id = ?');
if ($stmt->execute([json_encode($bids), $listingId])) {
    $stmt = $pdo->prepare('SELECT user_id FROM listings WHERE id = ?');
    $stmt->execute([$listingId]);
    $sellerId = $stmt->fetchColumn();

    $stmt2 = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
    $stmt2->execute([
        $_SESSION['user_id'],
        'buy_now_success',
        'Pembelian Berhasil',
        'Anda telah membeli kartu ' . $listing['card_name'] . '. Seller akan menghubungi Anda.',
        '/pokemon_fix/index.php?listing_id=' . $listingId
    ]);
    $stmt2->execute([
        $sellerId,
        'item_sold',
        'Kartu Terjual',
        'Kartu ' . $listing['card_name'] . ' telah dibeli oleh ' . $_SESSION['user']['username'],
        '/pokemon_fix/index.php?listing_id=' . $listingId
    ]);

    echo json_encode(['success' => true, 'message' => 'Selamat! Anda membeli kartu ini. Seller akan menghubungi Anda.']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal memproses pembelian']);
}