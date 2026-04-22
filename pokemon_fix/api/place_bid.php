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
$amount = (int)($input['amount'] ?? 0);
$bidderName = trim($input['bidder_name'] ?? $_SESSION['user']['username']);

if (!$listingId || $amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Data tidak lengkap']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare('SELECT * FROM listings WHERE id = ? AND type = "auction"');
$stmt->execute([$listingId]);
$listing = $stmt->fetch();

if (!$listing) {
    http_response_code(404);
    echo json_encode(['error' => 'Listing tidak ditemukan']);
    exit;
}

if ($listing['user_id'] == $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'Anda tidak bisa menawar listing milik sendiri']);
    exit;
}

if (strtotime($listing['end_time']) <= time()) {
    http_response_code(400);
    echo json_encode(['error' => 'Lelang sudah berakhir']);
    exit;
}

$bids = json_decode($listing['bids'] ?? '[]', true);
$currentHighest = 0;
foreach ($bids as $bid) {
    if ($bid['amount'] > $currentHighest) $currentHighest = $bid['amount'];
}
if ($currentHighest == 0) $currentHighest = $listing['start_price'];

$minBid = $currentHighest + $listing['min_bid_increment'];
if ($amount < $minBid) {
    http_response_code(400);
    echo json_encode(['error' => 'Tawaran minimal Rp ' . number_format($minBid, 0, ',', '.')]);
    exit;
}

$bidderWhatsapp = $_SESSION['user']['whatsapp'] ?? '';
$bids[] = [
    'amount' => $amount,
    'bidder_name' => $bidderName,
    'bidder_id' => $_SESSION['user_id'],
    'bidder_whatsapp' => $bidderWhatsapp,
    'time' => date('Y-m-d H:i:s')
];

$stmt = $pdo->prepare('UPDATE listings SET bids = ? WHERE id = ?');
if ($stmt->execute([json_encode($bids), $listingId])) {
    echo json_encode(['success' => true, 'current_bid' => $amount]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menyimpan tawaran']);
}