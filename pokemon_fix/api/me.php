<?php
require_once '../config/session_security.php';
header('Content-Type: application/json');

if (isset($_SESSION['user_id'], $_SESSION['user'])) {
    echo json_encode(['success' => true, 'user' => $_SESSION['user']]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
}