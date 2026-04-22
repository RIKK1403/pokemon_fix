<?php
require_once __DIR__ . '/../config/db.php';

$db = new Database();
$pdo = $db->getConnection();

// Pastikan kolom notification_sent ada di tabel listings
try {
    $pdo->query("SELECT notification_sent FROM listings LIMIT 1");
} catch (PDOException $e) {
    // Kolom belum ada, buat
    $pdo->exec("ALTER TABLE listings ADD COLUMN notification_sent TINYINT(1) DEFAULT 0");
}

// Cari lelang yang sudah berakhir dan belum dikirim notifikasi
$stmt = $pdo->prepare('
    SELECT l.id, l.card_name, l.user_id as seller_id, l.bids
    FROM listings l
    WHERE l.type = "auction" 
      AND l.end_time <= NOW()
      AND l.notification_sent = 0
');
$stmt->execute();
$expired = $stmt->fetchAll();

foreach ($expired as $auction) {
    $bids = json_decode($auction['bids'], true);
    if (!empty($bids)) {
        // Cari tawaran tertinggi
        $highest = null;
        foreach ($bids as $bid) {
            if ($highest === null || $bid['amount'] > $highest['amount']) {
                $highest = $bid;
            }
        }
        if ($highest) {
            $winnerId = $highest['bidder_id'] ?? null;
            $winnerName = $highest['bidder_name'] ?? 'Unknown';
            $amount = $highest['amount'];

            // Notifikasi untuk pemenang
            if ($winnerId) {
                $stmt2 = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
                $stmt2->execute([
                    $winnerId,
                    'auction_won',
                    'Anda Memenangkan Lelang',
                    'Selamat! Anda memenangkan lelang untuk kartu ' . $auction['card_name'] . ' dengan tawaran Rp ' . number_format($amount,0,',','.') . '. Hubungi penjual.',
                    '/pokemon_fix/index.php?listing_id=' . $auction['id']
                ]);
            }

            // Notifikasi untuk seller
            $stmt2 = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
            $stmt2->execute([
                $auction['seller_id'],
                'auction_ended',
                'Lelang Berakhir',
                'Lelang kartu ' . $auction['card_name'] . ' telah berakhir. Pemenang: ' . $winnerName . ' dengan tawaran Rp ' . number_format($amount,0,',','.'),
                '/pokemon_fix/index.php?listing_id=' . $auction['id']
            ]);
        }
    } else {
        // Tidak ada tawaran, notifikasi ke seller
        $stmt2 = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
        $stmt2->execute([
            $auction['seller_id'],
            'auction_no_winner',
            'Lelang Tanpa Pemenang',
            'Lelang kartu ' . $auction['card_name'] . ' telah berakhir tanpa ada tawaran.',
            '/pokemon_fix/index.php?listing_id=' . $auction['id']
        ]);
    }
    // Tandai sudah diproses
    $update = $pdo->prepare('UPDATE listings SET notification_sent = 1 WHERE id = ?');
    $update->execute([$auction['id']]);
}
?>