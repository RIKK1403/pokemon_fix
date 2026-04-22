<?php
require 'supabase_config.php';

try {
    $pdo = getSupabaseDB();
    echo "✅ Supabase connection OK\n";
    
    $stmt = $pdo->query("SELECT version()");
    echo "PostgreSQL version: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "\n";
    
    echo "Test complete!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

