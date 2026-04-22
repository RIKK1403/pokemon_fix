<?php
require_once '../config/session_security.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$tabToken = $input['tab_token'] ?? '';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['valid' => false, 'reason' => 'not_logged_in']);
    exit;
}

if (!isset($_SESSION['tab_token']) || !hash_equals($_SESSION['tab_token'], $tabToken)) {
    $_SESSION = [];
    session_destroy();
    echo json_encode(['valid' => false, 'reason' => 'invalid_tab_token']);
    exit;
}

echo json_encode(['valid' => true]);