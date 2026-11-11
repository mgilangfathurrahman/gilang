<?php
require 'koneksi.php';

try {
    // 1. Tabel Admin
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL
    )");

    // 2. Tabel Produk
    $pdo->exec("CREATE TABLE IF NOT EXISTS produk (
        id SERIAL PRIMARY KEY,
        nama_produk VARCHAR(100) NOT NULL,
        harga INT NOT NULL,
        stok INT DEFAULT 0
    )");

    // 3. Tabel Pesanan
    $pdo->exec("CREATE TABLE IF NOT EXISTS pesanan (
        id SERIAL PRIMARY KEY,
        nama_pembeli VARCHAR(100) NOT NULL,
        produk_id INT REFERENCES produk(id),
        jumlah INT NOT NULL,
        total_harga INT NOT NULL,
        status VARCHAR(30) DEFAULT 'Selesai',
        tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Buat akun admin default (user: admin | pass: admin123)
    $pass_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES ('admin', :pass) ON CONFLICT (username) DO NOTHING");
    $stmt->execute([':pass' => $pass_hash]);

    echo "<h1>Setup Database Berhasil!</h1>";
    echo "<p>Tabel Admin, Produk, dan Pesanan siap. Login menggunakan user: <b>admin</b>, pass: <b>admin123</b></p>";
    echo "<a href='login.php'>Ke Halaman Login</a>";
} catch (PDOException $e) {
    echo "Error Setup: " . $e->getMessage();
}
?>
