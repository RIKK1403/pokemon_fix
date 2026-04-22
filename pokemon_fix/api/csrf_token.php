<?php
require_once '../config/session_security.php';
header('Content-Type: application/json');
echo json_encode(['csrf_token' => $_SESSION['csrf_token']]);