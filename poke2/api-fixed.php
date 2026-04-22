<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require 'config.php';  // ← sekarang pakai config.php (MySQL)

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim(str_replace($_SERVER['SCRIPT_NAME'], '', $path), '/');
if (empty($path)) $path = 'api/test';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($path) {
        case 'api/login':
            $data = json_decode(file_get_contents('php://input'), true);
            login($data);
            break;
        case 'api/register':
            $data = json_decode(file_get_contents('php://input'), true);
            register($data);
            break;
        case 'api/listings':
            handleListings();
            break;
        case 'api/my-listings':
            myListings();
            break;
        case 'api/delete':
            deleteListing();
            break;
        case 'api/bid':
            bid();
            break;
        case 'api/buy-now':
            buyNow();
            break;
        case 'api/report':
            report();
            break;
        case 'api/test':
            echo json_encode(['status' => 'API ready - MySQL connected']);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function login($data) {
    $pdo = getDB();  // ← MySQL
    $username = strtolower(trim($data['username'] ?? ''));
    $password = $data['password'] ?? '';
    
    if (strlen($username) < 3 || strlen($password) < 6) {
        throw new Exception('Invalid credentials');
    }
    
    $hash = hash('sha256', $password);
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND password_hash = ? AND is_active = 1');
    $stmt->execute([$username, $hash]);
    $user = $stmt->fetch();
    
    if (!$user) throw new Exception('Invalid credentials');
    
    $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
    
    unset($user['password_hash']);
    session_start();
    $_SESSION['user_id'] = $user['id'];
    
    echo json_encode($user);
}

function register($data) {
    $pdo = getDB();  // ← MySQL
    $username = strtolower(trim($data['username'] ?? ''));
    $fullname = trim($data['fullname'] ?? '');
    $email = trim($data['email'] ?? '');
    $whatsapp = trim($data['whatsapp'] ?? '');
    $password = $data['password'] ?? '';
    
    if (!preg_match('/^[a-z0-9]{3,20}$/i', $username) || strlen($password) < 6) {
        throw new Exception('Invalid username/password');
    }
    
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$username]);
    if ($stmt->fetch()) throw new Exception('Username taken');
    
    $id = uuid();  // dari config.php
    $hash = hash('sha256', $password);
    
    $stmt = $pdo->prepare('INSERT INTO users (id, username, fullname, email, whatsapp, password_hash) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$id, $username, $fullname, $email, $whatsapp, $hash]);
    
    $user = ['id' => $id, 'username' => $username, 'fullname' => $fullname];
    echo json_encode($user);
}

function handleListings() {
    $pdo = getDB();  // ← MySQL
    $type = $_GET['type'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $where = '1=1';
    $params = [];
    
    if ($type) {
        $where .= ' AND type = ?';
        $params[] = $type;
    }
    if ($search) {
        $where .= ' AND card_name LIKE ?';
        $params[] = "%$search%";
    }
    
    if ($type === 'auction') $where .= ' AND (end_time IS NULL OR end_time > NOW())';
    
    $stmt = $pdo->prepare("SELECT l.*, u.username as seller_username, 
        COUNT(r.id) as report_count 
        FROM listings l 
        LEFT JOIN users u ON l.seller_id = u.id 
        LEFT JOIN reports r ON l.id = r.listing_id 
        WHERE $where 
        GROUP BY l.id 
        ORDER BY l.date_created DESC 
        LIMIT 50");
    $stmt->execute($params);
    $listings = $stmt->fetchAll();
    
    foreach ($listings as &$l) {
        $l['bids'] = json_decode($l['bids'] ?? '[]', true);
    }
    
    echo json_encode($listings);
}

function myListings() {
    session_start();
    if (!isset($_SESSION['user_id'])) throw new Exception('Auth required');
    
    $pdo = getDB();  // ← MySQL
    $stmt = $pdo->prepare('SELECT * FROM listings WHERE seller_id = ? ORDER BY date_created DESC');
    $stmt->execute([$_SESSION['user_id']]);
    $listings = $stmt->fetchAll();
    
    foreach ($listings as &$l) {
        $l['bids'] = json_decode($l['bids'] ?? '[]', true);
    }
    
    echo json_encode($listings);
}

function deleteListing() {
    session_start();
    if (!isset($_SESSION['user_id'])) throw new Exception('Auth required');
    
    $pdo = getDB();  // ← MySQL
    $id = $_GET['id'] ?? '';
    
    $stmt = $pdo->prepare('DELETE FROM listings WHERE id = ? AND seller_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Not found');
    }
}

function bid() {
    session_start();
    if (!isset($_SESSION['user_id'])) throw new Exception('Auth required');
    
    $pdo = getDB();  // ← MySQL
    $id = $_GET['id'] ?? '';
    $data = json_decode(file_get_contents('php://input'), true);
    $amount = (int)($data['amount'] ?? 0);
    
    $stmt = $pdo->prepare('SELECT * FROM listings WHERE id = ? AND type = "auction" AND end_time > NOW()');
    $stmt->execute([$id]);
    $listing = $stmt->fetch();
    
    if (!$listing) throw new Exception('Auction ended');
    
    $current = 0;
    if ($listing['bids']) {
        $bids = json_decode($listing['bids'], true);
        $current = max(array_column($bids, 'amount'));
    }
    
    $min = $current + ($listing['min_bid_increment'] ?? 10000);
    if ($amount < $min) throw new Exception("Min bid Rp".number_format($min));
    
    $bids = json_decode($listing['bids'] ?? '[]', true);
    $bids[] = ['amount' => $amount, 'bidder_name' => $data['bidder_name'] ?? 'Anon', 'time' => date('c')];
    
    $stmt = $pdo->prepare('UPDATE listings SET bids = ? WHERE id = ?');
    $stmt->execute([json_encode($bids), $id]);
    
    echo json_encode(['success' => true]);
}

function buyNow() {
    session_start();
    if (!isset($_SESSION['user_id'])) throw new Exception('Auth required');
    
    $pdo = getDB();  // ← MySQL
    $id = $_GET['id'] ?? '';
    
    $stmt = $pdo->prepare('SELECT buy_now_price FROM listings WHERE id = ? AND type = "auction" AND end_time > NOW() AND buy_now_price IS NOT NULL');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    
    if (!$row) throw new Exception('Cannot buy now');
    
    $bids = [['amount' => $row['buy_now_price'], 'bidder_name' => $_SESSION['user_id'], 'time' => date('c')]];
    
    $pdo->prepare('UPDATE listings SET end_time = NOW(), bids = ? WHERE id = ?')->execute([json_encode($bids), $id]);
    
    echo json_encode(['success' => true]);
}

function report() {
    session_start();
    $data = json_decode(file_get_contents('php://input'), true);
    
    $pdo = getDB();  // ← MySQL
    $stmt = $pdo->prepare('INSERT INTO reports (id, listing_id, reason, reporter) VALUES (?, ?, ?, ?)');
    $stmt->execute([uuid(), $data['listing_id'], $data['reason'], $_SESSION['user_id'] ?? 'anon']);
    
    echo json_encode(['success' => true]);
}
?>