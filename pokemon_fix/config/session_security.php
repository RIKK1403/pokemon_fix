<?php
date_default_timezone_set('Asia/Jakarta');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['regenerated_at'])) {
    $_SESSION['regenerated_at'] = time();
} elseif (time() - $_SESSION['regenerated_at'] > 300) {
    session_regenerate_id(true);
    $_SESSION['regenerated_at'] = time();
}

if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>