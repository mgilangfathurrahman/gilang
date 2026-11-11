<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }
require 'koneksi.php';

// Proses Buat Pesanan Baru
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $produk_id = $_POST['produk_id'];
    $jumlah    = $_POST['jumlah'];

    // Ambil harga produk
    $stmt = $pdo->prepare("SELECT harga, stok FROM produk WHERE id = :id");
    $stmt->execute([':id' => $produk_id]);
    $produk = $stmt->fetch();

    if ($produk && $produk['stok'] >= $jumlah) {
        $total_harga = $produk['harga'] * $jumlah;

        // Simpan pesanan
        $ins = $pdo->prepare("INSERT INTO pesanan (nama_pembeli, produk_id, jumlah, total_harga) VALUES (:pembeli, :pid, :jml, :total)");
        $ins->execute([
            ':pembeli' => $_POST['nama_pembeli'],
            ':pid'     => $produk_id,
            ':jml'     => $jumlah,
            ':total'   => $total_harga
        ]);

        // Kurangi stok
        $upd = $pdo->prepare("UPDATE produk SET stok = stok - :jml WHERE id = :pid");
        $upd->execute([':jml' => $jumlah, ':pid' => $produk_id]);
    }
    header("Location: pesanan.php");
    exit;
}

$produk_tersedia = $pdo->query("SELECT * FROM produk WHERE stok > 0")->fetchAll();
$pesanan_list = $pdo->query("SELECT pesanan.*, produk.nama_produk FROM pesanan JOIN produk ON pesanan.produk_id = produk.id ORDER BY pesanan.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pesanan</title>
    <style>
        body { font-family: sans-serif; margin: 20px 40px; }
        nav a { margin-right: 15px; text-decoration: none; font-weight: bold; color: #24b47e; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #24b47e; color: white; }
        form input, select, button { padding: 8px; margin-right: 5px; }
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

    <h2>Catat Pesanan Baru</h2>
    <form method="POST">
        <input type="text" name="nama_pembeli" placeholder="Nama Pembeli" required>
        <select name="produk_id" required>
            <option value="">-- Pilih Produk --</option>
            <?php foreach ($produk_tersedia as $pt): ?>
                <option value="<?= $pt['id'] ?>"><?= htmlspecialchars($pt['nama_produk']) ?> (Stok: <?= $pt['stok'] ?>)</option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="jumlah" placeholder="Jumlah Beli" min="1" required>
        <button type="submit">Simpan Pesanan</button>
    </form>

    <table>
        <tr><th>ID</th><th>Pembeli</th><th>Produk</th><th>Jumlah</th><th>Total Harga</th><th>Waktu</th></tr>
        <?php foreach ($pesanan_list as $ps): ?>
        <tr>
            <td><?= $ps['id'] ?></td>
            <td><?= htmlspecialchars($ps['nama_pembeli']) ?></td>
            <td><?= htmlspecialchars($ps['nama_produk']) ?></td>
            <td><?= $ps['jumlah'] ?></td>
            <td>Rp <?= number_format($ps['total_harga'], 0, ',', '.') ?></td>
            <td><?= $ps['tanggal'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
