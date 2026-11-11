<?php
session_start();
require 'koneksi.php';

// Hanya mengambil produk yang stoknya lebih dari 0 (tersedia)
$produk_tersedia = $pdo->query("SELECT * FROM produk WHERE stok > 0 ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Produk Tersedia</title>
    <style>
        body { font-family: sans-serif; margin: 20px 40px; background: #f9fafb; }
        header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .btn-login { background: #24b47e; color: white; padding: 10px 18px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn-login:hover { background: #1c8c61; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; margin-top: 25px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); border-top: 4px solid #24b47e; }
        .card h3 { margin: 0 0 10px; color: #333; }
        .harga { font-size: 18px; font-weight: bold; color: #24b47e; margin: 10px 0; }
        .stok { font-size: 13px; color: #666; background: #eee; padding: 4px 8px; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>

    <header>
        <h2>Katalog Toko Gilang</h2>
        <?php if (isset($_SESSION['admin_logged_in'])): ?>
            <a href="laporan.php" class="btn-login">Ke Dashboard Admin</a>
        <?php else: ?>
            <a href="login.php" class="btn-login">Login Admin</a>
        <?php endif; ?>
    </header>

    <div class="grid">
        <?php if (count($produk_tersedia) > 0): ?>
            <?php foreach ($produk_tersedia as $p): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($p['nama_produk']) ?></h3>
                    <div class="harga">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                    <div class="stok">Tersedia: <?= $p['stok'] ?> unit</div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Maaf, saat ini belum ada produk yang tersedia.</p>
        <?php endif; ?>
    </div>

</body>
</html>
