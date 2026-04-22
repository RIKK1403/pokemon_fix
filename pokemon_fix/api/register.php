<?php
require_once '../config/session_security.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['reg_attempts'])) $_SESSION['reg_attempts'] = 0;
if (!isset($_SESSION['last_reg_time'])) $_SESSION['last_reg_time'] = time();
if ($_SESSION['reg_attempts'] >= 3 && (time() - $_SESSION['last_reg_time']) < 3600) {
    http_response_code(429);
    echo json_encode(['error' => 'Terlalu banyak percobaan daftar. Coba lagi nanti.']);
    exit;
}

require_once '../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$fullname = trim($input['fullname'] ?? '');
$username = trim(strtolower($input['username'] ?? ''));
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$whatsapp = trim($input['whatsapp'] ?? '');

$errors = [];
if (!preg_match('/^[a-z0-9]{3,20}$/', $username)) $errors[] = 'Username harus 3-20 karakter (huruf/angka)';
if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter';
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => implode(', ', $errors)]);
    exit;
}

$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$username]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'Username sudah terdaftar']);
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (username, password_hash, fullname, email, whatsapp) VALUES (?, ?, ?, ?, ?)');
if ($stmt->execute([$username, $passwordHash, $fullname, $email, $whatsapp])) {
    $userId = $pdo->lastInsertId();
    $_SESSION['reg_attempts'] = 0;
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user'] = [
        'id' => $userId,
        'username' => $username,
        'fullname' => $fullname,
        'email' => $email,
        'whatsapp' => $whatsapp
    ];
    $tabToken = bin2hex(random_bytes(32));
    $_SESSION['tab_token'] = $tabToken;
    
    echo json_encode([
        'success' => true,
        'user' => $_SESSION['user'],
        'tab_token' => $tabToken
    ]);
} else {
    $_SESSION['reg_attempts']++;
    $_SESSION['last_reg_time'] = time();
    http_response_code(500);
    echo json_encode(['error' => 'Pendaftaran gagal']);
}