<?php
require_once '../config/session_security.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

function validateCsrfToken() {
    $headers = getallheaders();
    $token = $headers['X-CSRF-Token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
}

require_once '../config/db.php';

$db = new Database();
$pdo = $db->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $all = isset($_GET['all']) && $_GET['all'] == 'true';
    if ($all) {
        // Marketplace: tampilkan SEMUA listing tanpa filter apapun
        $stmt = $pdo->prepare('
            SELECT l.*, u.username as seller_username, u.fullname as seller_fullname, u.whatsapp as seller_whatsapp
            FROM listings l
            JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
        ');
        $stmt->execute();
        $listings = $stmt->fetchAll();
        foreach ($listings as &$listing) {
            $listing['bids'] = json_decode($listing['bids'] ?? '[]', true) ?? [];
        }
        echo json_encode(['listings' => $listings]);
    } else {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare('SELECT * FROM listings WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        $listings = $stmt->fetchAll();
        foreach ($listings as &$listing) {
            $listing['bids'] = json_decode($listing['bids'] ?? '[]', true) ?? [];
        }
        echo json_encode(['listings' => $listings]);
    }
}
elseif ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    validateCsrfToken();
    
    $userId = $_SESSION['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }
    
    $type = $input['type'] ?? 'direct';
    $cardName = substr(trim($input['card_name'] ?? ''), 0, 100);
    if (empty($cardName)) {
        http_response_code(400);
        echo json_encode(['error' => 'Nama kartu wajib diisi']);
        exit;
    }
    if ($type === 'direct') {
        if (empty($input['price']) || empty($input['link'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Harga dan link produk wajib diisi']);
            exit;
        }
        $price = (int)$input['price'];
        if ($price < 1000) {
            http_response_code(400);
            echo json_encode(['error' => 'Harga minimal Rp 1.000']);
            exit;
        }
        if ($price > 1000000000) {
            http_response_code(400);
            echo json_encode(['error' => 'Harga maksimal Rp 1.000.000.000']);
            exit;
        }
    } else {
        if (empty($input['start_price'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Harga awal lelang wajib diisi']);
            exit;
        }
        $startPrice = (int)$input['start_price'];
        if ($startPrice < 1000) {
            http_response_code(400);
            echo json_encode(['error' => 'Harga awal minimal Rp 1.000']);
            exit;
        }
    }
    
    $fields = [
        'user_id' => $userId,
        'type' => $type,
        'card_name' => $cardName,
        'set' => substr($input['set'] ?? '', 0, 50),
        'rarity' => $input['rarity'] ?? '',
        'condition' => $input['condition'] ?? '',
        'image' => substr($input['image'] ?? '', 0, 500),
        'desc' => substr($input['desc'] ?? '', 0, 500),
        'bids' => json_encode([])
    ];
    if ($type === 'direct') {
        $fields['price'] = (int)$input['price'];
        $fields['link'] = substr($input['link'] ?? '', 0, 500);
        $fields['platform'] = $input['platform'] ?? '';
    } else {
        $duration = (int)($input['auction_duration'] ?? 3);
        $endTime = date('Y-m-d H:i:s', strtotime("+{$duration} days"));
        $fields['start_price'] = (int)$input['start_price'];
        $minBidIncrement = (int)($input['min_bid_increment'] ?? 10000);
        if ($minBidIncrement < 1000) $minBidIncrement = 1000;
        if ($minBidIncrement > 1000000) {
            http_response_code(400);
            echo json_encode(['error' => 'Minimal kenaikan maksimal Rp 1.000.000']);
            exit;
        }
        $fields['min_bid_increment'] = $minBidIncrement;
        $fields['buy_now_price'] = !empty($input['buy_now_price']) ? (int)$input['buy_now_price'] : null;
        $fields['end_time'] = $endTime;
    }
    
    $columns = implode('`,`', array_keys($fields));
    $placeholders = ':' . implode(', :', array_keys($fields));
    $sql = "INSERT INTO listings (`$columns`) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    foreach ($fields as $key => $val) $stmt->bindValue(":$key", $val);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menyimpan listing']);
    }
}
elseif ($method === 'DELETE') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    validateCsrfToken();
    
    $userId = $_SESSION['user_id'];
    parse_str(file_get_contents('php://input'), $input);
    $id = (int)($input['id'] ?? 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID listing diperlukan']);
        exit;
    }
    
    // Cek apakah lelang aktif
    $stmt = $pdo->prepare('SELECT type, end_time, image FROM listings WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $listing = $stmt->fetch();
    if (!$listing) {
        http_response_code(404);
        echo json_encode(['error' => 'Listing tidak ditemukan']);
        exit;
    }
    if ($listing['type'] === 'auction' && strtotime($listing['end_time']) > time()) {
        http_response_code(403);
        echo json_encode(['error' => 'Lelang aktif tidak dapat dihapus']);
        exit;
    }
    
    $stmt = $pdo->prepare('DELETE FROM listings WHERE id = ? AND user_id = ?');
    if ($stmt->execute([$id, $userId])) {
        if (!empty($listing['image'])) {
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . $listing['image'];
            if (file_exists($imagePath) && is_file($imagePath)) {
                unlink($imagePath);
            }
        }
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menghapus listing']);
    }
}
else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}