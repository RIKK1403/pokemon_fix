<?php
// Supabase PostgreSQL Config
// Get from https://myaluqqfvzqxhmpmhifw.supabase.co → Settings → Database

define('SUPABASE_HOST', 'db.myaluqqfvzqxhmpmhifw.supabase.co');  // Change to your project
define('SUPABASE_PORT', 5432);
define('SUPABASE_DB', 'postgres');
define('SUPABASE_USER', 'postgres');
define('SUPABASE_PASS', '8dVz88jOkddZUQjJ');  // Supabase DB password

function getSupabaseDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "pgsql:host=" . SUPABASE_HOST . ";port=" . SUPABASE_PORT . ";dbname=" . SUPABASE_DB . ";sslmode=require";
        $pdo = new PDO($dsn, SUPABASE_USER, SUPABASE_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
    return $pdo;
}

// UUID v4
function uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff), mt_rand(0,0xffff),
        mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000,
        mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff), mt_rand(0,0xffff), mt_rand(0,0xffff)
    );
}

date_default_timezone_set('Asia/Jakarta');
?>

