<?php
// 1. Memulai sesi untuk menyimpan status login
session_start();

// 2. Menghubungkan ke database PostgreSQL Aiven
require 'koneksi.php';

// 3. Jika admin sudah login, langsung arahkan ke halaman laporan
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: laporan.php");
    exit;
}

$error = "";

// 4. Memproses form saat tombol "Masuk" ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        // Mencari data admin berdasarkan username
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :user");
        $stmt->execute([':user' => $username]);
        $admin = $stmt->fetch();

        // Memverifikasi kecocokan password yang sudah di-hash
        if ($admin && password_verify($password, $admin['password'])) {
            // Simpan data ke dalam sesi
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin['id'];
            $_SESSION['username']        = $admin['username'];

            // Arahkan ke halaman laporan real-time
            header("Location: laporan.php");
            exit;
        } else {
            $error = "Username atau password yang Anda masukkan salah!";
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan sistem: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Toko Gilang</title>
    <style>
        body { 
            font-family: sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0;
            background-color: #f4f7f6; 
        }
        .login-box { 
            background: white; 
            padding: 35px 30px; 
            border-radius: 8px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
            width: 100%;
            max-width: 340px; 
            border-top: 4px solid #24b47e;
        }
        .login-box h2 {
            margin: 0 0 20px;
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #555;
            font-weight: bold;
        }
        .form-group input { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; 
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #24b47e;
        }
        button { 
            width: 100%; 
            padding: 11px; 
            background-color: #24b47e; 
            color: white; 
            border: none; 
            border-radius: 5px;
            cursor: pointer; 
            font-size: 15px;
            font-weight: bold; 
            margin-top: 5px;
        }
        button:hover {
            background-color: #1c8c61;
        }
        .error-msg { 
            background-color: #ffe6e6;
            color: #d93025; 
            padding: 10px;
            border-radius: 5px;
            font-size: 13px; 
            margin-bottom: 15px;
            text-align: center;
        }
        .back-link {
            text-align: center; 
            margin-top: 20px; 
            font-size: 13px;
        }
        .back-link a {
            color: #555; 
            text-decoration: none;
        }
        .back-link a:hover {
            color: #24b47e;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <h2>Login Admin</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit">Masuk</button>
        </form>

        <div class="back-link">
            <a href="index.php">&laquo; Kembali ke Katalog Produk</a>
        </div>
    </div>

</body>
</html>
