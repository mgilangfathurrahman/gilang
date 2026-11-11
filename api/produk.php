<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }
require 'koneksi.php';

// Proses Tambah Produk
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO produk (nama_produk, harga, stok) VALUES (:nama, :harga, :stok)");
    $stmt->execute([
        ':nama'  => $_POST['nama_produk'],
        ':harga' => $_POST['harga'],
        ':stok'  => $_POST['stok']
    ]);
    header("Location: produk.php");
    exit;
}

$produk_list = $pdo->query("SELECT * FROM produk ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk</title>
    <style>
        body { font-family: sans-serif; margin: 20px 40px; }
        nav a { margin-right: 15px; text-decoration: none; font-weight: bold; color: #24b47e; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #24b47e; color: white; }
        form input, button { padding: 8px; margin-right: 5px; }
    </style>
</head>
<body>
    <nav>
        <a href="laporan.php">Laporan Real-Time</a>
        <a href="produk.php">Produk</a>
        <a href="pesanan.php">Pesanan</a>
        <a href="logout.php" style="color:red;">Logout</a>
    </nav>
    <hr>

    <h2>Kelola Produk</h2>
    <form method="POST">
        <input type="text" name="nama_produk" placeholder="Nama Produk" required>
        <input type="number" name="harga" placeholder="Harga (Rp)" required>
        <input type="number" name="stok" placeholder="Stok" required>
        <button type="submit">Tambah Produk</button>
    </form>

    <table>
        <tr><th>ID</th><th>Nama Produk</th><th>Harga</th><th>Stok</th></tr>
        <?php foreach ($produk_list as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['nama_produk']) ?></td>
            <td>Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
            <td><?= $p['stok'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
