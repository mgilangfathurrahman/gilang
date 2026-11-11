<?php
// Menampilkan pesan error jika terjadi kesalahan sistem
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cek status login dari Cookie
if (!isset($_COOKIE['admin_logged_in']) || $_COOKIE['admin_logged_in'] !== 'true') {
    header("Location: login.php");
    exit;
}

require 'koneksi.php';

$pesan = "";

// Proses Buat Pesanan Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pembeli = trim($_POST['nama_pembeli']);
    $produk_id    = $_POST['produk_id'];
    $jumlah       = (int) $_POST['jumlah'];

    try {
        // Ambil harga dan stok produk saat ini
        $stmt = $pdo->prepare("SELECT harga, stok FROM produk WHERE id = :id");
        $stmt->execute([':id' => $produk_id]);
        $produk = $stmt->fetch();

        if ($produk && $produk['stok'] >= $jumlah && $jumlah > 0) {
            $total_harga = $produk['harga'] * $jumlah;

            // 1. Simpan ke tabel pesanan
            $ins = $pdo->prepare("INSERT INTO pesanan (nama_pembeli, produk_id, jumlah, total_harga) VALUES (:pembeli, :pid, :jml, :total)");
            $ins->execute([
                ':pembeli' => $nama_pembeli,
                ':pid'     => $produk_id,
                ':jml'     => $jumlah,
                ':total'   => $total_harga
            ]);

            // 2. Kurangi stok di tabel produk
            $upd = $pdo->prepare("UPDATE produk SET stok = stok - :jml WHERE id = :pid");
            $upd->execute([
                ':jml' => $jumlah,
                ':pid' => $produk_id
            ]);

            header("Location: pesanan.php?status=sukses");
            exit;
        } else {
            $pesan = "Stok tidak mencukupi atau jumlah tidak valid!";
        }
    } catch (PDOException $e) {
        $pesan = "Gagal menyimpan pesanan: " . $e->getMessage();
    }
}

// Ambil daftar produk yang stoknya tersedia & riwayat pesanan
try {
    $produk_tersedia = $pdo->query("SELECT * FROM produk WHERE stok > 0 ORDER BY nama_produk ASC")->fetchAll();
    
    $sql_pesanan = "SELECT pesanan.id, pesanan.nama_pembeli, produk.nama_produk, pesanan.jumlah, pesanan.total_harga, pesanan.tanggal 
                    FROM pesanan 
                    JOIN produk ON pesanan.produk_id = produk.id 
                    ORDER BY pesanan.id DESC";
    $pesanan_list = $pdo->query($sql_pesanan)->fetchAll();
} catch (PDOException $e) {
    die("Error Query Database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Pesanan - Toko Gilang</title>
    <style>
        body { font-family: sans-serif; margin: 20px 40px; }
        nav a { margin-right: 15px; text-decoration: none; font-weight: bold; color: #24b47e; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #24b47e; color: white; }
        form input, select, button { padding: 8px; margin-right: 5px; }
        button { background: #24b47e; color: white; border: none; cursor: pointer; font-weight: bold; }
        .alert { padding: 10px; margin: 15px 0; border-radius: 5px; }
        .alert-ok { background: #e6f7f0; color: #24b47e; }
        .alert-err { background: #ffe6e6; color: #d93025; }
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

    <?php if (isset($_GET['status']) && $_GET['status'] === 'sukses'): ?>
        <div class="alert alert-ok">Pesanan berhasil dicatat dan stok produk otomatis berkurang!</div>
    <?php endif; ?>

    <?php if (!empty($pesan)): ?>
        <div class="alert alert-err"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="nama_pembeli" placeholder="Nama Pembeli" required>
        <select name="produk_id" required>
            <option value="">-- Pilih Produk --</option>
            <?php foreach ($produk_tersedia as $pt): ?>
                <option value="<?= $pt['id'] ?>">
                    <?= htmlspecialchars($pt['nama_produk']) ?> - Rp <?= number_format($pt['harga'], 0, ',', '.') ?> (Stok: <?= $pt['stok'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="jumlah" placeholder="Jumlah" min="1" required style="width: 100px;">
        <button type="submit">Simpan Pesanan</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pembeli</th>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Total Harga</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($pesanan_list) > 0): ?>
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
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">Belum ada riwayat pesanan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
