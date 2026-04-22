<?php
require_once '../config/session_security.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$headers = getallheaders();
$token = $headers['X-CSRF-Token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'File gambar tidak valid']);
    exit;
}

$file = $_FILES['image'];
$maxSize = 2 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'Ukuran file maksimal 2MB']);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Format file harus JPG, PNG, atau WEBP']);
    exit;
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
$uploadPath = '../uploads/' . $filename;

if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    echo json_encode(['success' => true, 'image_url' => '/pokemon_fix/uploads/' . $filename]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menyimpan gambar']);
}