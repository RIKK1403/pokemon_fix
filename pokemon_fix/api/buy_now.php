// Setelah update listing sukses
$stmt = $pdo->prepare('SELECT user_id FROM listings WHERE id = ?');
$stmt->execute([$listingId]);
$sellerId = $stmt->fetchColumn();

// Notifikasi untuk pembeli
$stmt = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([
    $_SESSION['user_id'],
    'buy_now_success',
    'Pembelian Berhasil',
    'Anda telah membeli kartu ' . $listing['card_name'] . '. Seller akan menghubungi Anda.',
    '/pokemon_fix/index.php?listing_id=' . $listingId
]);

// Notifikasi untuk seller
$stmt->execute([
    $sellerId,
    'item_sold',
    'Kartu Terjual',
    'Kartu ' . $listing['card_name'] . ' telah dibeli oleh ' . $_SESSION['user']['username'],
    '/pokemon_fix/index.php?listing_id=' . $listingId
]);