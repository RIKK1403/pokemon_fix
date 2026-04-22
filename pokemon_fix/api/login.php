<?php
require_once '../config/session_security.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['last_attempt_time'])) $_SESSION['last_attempt_time'] = time();
if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt_time']) < 900) {
    http_response_code(429);
    echo json_encode(['error' => 'Terlalu banyak percobaan login. Coba lagi nanti.']);
    exit;
}

require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$username = trim(strtolower($input['username'] ?? ''));
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Username dan password wajib diisi']);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare('SELECT id, username, password_hash, fullname, email, whatsapp FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['login_attempts'] = 0;
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'fullname' => $user['fullname'],
        'email' => $user['email'],
        'whatsapp' => $user['whatsapp']
    ];
    $tabToken = bin2hex(random_bytes(32));
    $_SESSION['tab_token'] = $tabToken;
    
    echo json_encode([
        'success' => true,
        'user' => $_SESSION['user'],
        'tab_token' => $tabToken
    ]);
} else {
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    http_response_code(401);
    echo json_encode(['error' => 'Username atau password salah']);
}