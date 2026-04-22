<?php
// setup_db.php - Jalankan sekali untuk membuat database & tabel MySQL
header('Content-Type: text/html; charset=utf-8');

// Koneksi ke MySQL tanpa memilih database
try {
    $pdo = new PDO('mysql:host=127.0.0.1;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Baca file SQL
    $sqlFile = __DIR__ . '/create_pokemon_db.sql';
    if (!file_exists($sqlFile)) {
        die("❌ File create_pokemon_db.sql tidak ditemukan di folder ini.");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Eksekusi query (multiple statements)
    $pdo->exec("DROP DATABASE IF EXISTS pokemon");
    $pdo->exec("CREATE DATABASE pokemon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE pokemon");
    
    // Pecah SQL per statement (sederhana, asumsi titik koma di akhir baris)
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($queries as $query) {
        if (!empty($query) && strpos($query, 'CREATE DATABASE') === false) {
            $pdo->exec($query);
        }
    }
    
    echo "<h2 style='color:green'>✅ Database 'pokemon' berhasil dibuat!</h2>";
    echo "<p>Tabel: users, listings, reports telah siap.</p>";
    echo "<p>Sekarang <a href='index.php'>klik di sini</a> untuk membuka aplikasi.</p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ Gagal koneksi ke MySQL</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Pastikan Laragon sudah running (MySQL aktif).</p>";
}
?>