<?php
/**
 * Starter dashboard for the store.
 * Configure DB_HOST, DB_PORT, DB_NAME, DB_USER, and DB_PASSWORD in the
 * application environment. Never commit database credentials to this file.
 */

$dbConfigured = true;
foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $name) {
    if (getenv($name) === false || getenv($name) === '') {
        $dbConfigured = false;
        break;
    }
}

$dbConnected = false;
$connectionMessage = $dbConfigured
    ? 'Koneksi database belum diperiksa.'
    : 'Atur variabel lingkungan database untuk menghubungkan aplikasi ke PostgreSQL.';

if ($dbConfigured) {
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: '5432';
    $database = getenv('DB_NAME');
    $username = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    try {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
            $host,
            $port,
            $database
        );
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $dbConnected = true;
        $connectionMessage = 'Terhubung ke PostgreSQL.';
    } catch (PDOException $exception) {
        // Do not expose connection details or credentials to visitors.
        error_log('PostgreSQL connection failed: ' . $exception->getMessage());
        $connectionMessage = 'Koneksi database gagal. Periksa konfigurasi dan log server.';
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Toko</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #17211b;
            --muted: #68756c;
            --green: #176b45;
            --green-soft: #e8f4ec;
            --line: #e5eae6;
            --surface: #ffffff;
            --background: #f5f7f5;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--ink);
            background: var(--background);
            font: 15px/1.5 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .layout { min-height: 100vh; display: grid; grid-template-columns: 240px 1fr; }
        aside { padding: 28px 20px; background: #123d2b; color: white; }
        .brand { margin: 0 0 36px; font-size: 20px; font-weight: 750; }
        .brand span { color: #a9dfba; }
        .nav-label { margin: 0 0 10px; color: #b7cfc0; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; }
        .nav-item { display: block; margin: 4px 0; padding: 10px 12px; border-radius: 9px; color: #e8f2eb; text-decoration: none; }
        .nav-item.active { background: #245c40; color: white; }
        main { min-width: 0; padding: 34px clamp(20px, 5vw, 64px); }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 30px; }
        h1 { margin: 0; font-size: clamp(24px, 3vw, 32px); letter-spacing: -.03em; }
        .date { color: var(--muted); }
        .welcome { margin: 8px 0 0; color: var(--muted); }
        .status { display: flex; align-items: flex-start; gap: 10px; margin: 24px 0; padding: 14px 16px; border: 1px solid var(--line); border-radius: 12px; background: var(--surface); }
        .dot { width: 10px; height: 10px; flex: 0 0 10px; margin-top: 5px; border-radius: 50%; background: <?= $dbConnected ? '#24834f' : '#d79523' ?>; }
        .status p { margin: 0; }
        .status strong { display: block; }
        .status small { color: var(--muted); }
        .cards { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .card, .panel { border: 1px solid var(--line); border-radius: 14px; background: var(--surface); }
        .card { padding: 20px; }
        .card-label { margin: 0 0 10px; color: var(--muted); }
        .card-value { margin: 0; font-size: 25px; font-weight: 700; }
        .card-note { margin: 8px 0 0; color: var(--muted); font-size: 13px; }
        .panel { margin-top: 20px; padding: 22px; }
        .panel h2 { margin: 0 0 8px; font-size: 18px; }
        .panel p { margin: 0; color: var(--muted); }
        @media (max-width: 760px) {
            .layout { grid-template-columns: 1fr; }
            aside { padding: 16px 20px; }
            .brand { margin: 0; }
            aside nav { display: none; }
            main { padding-top: 24px; }
            .cards { grid-template-columns: 1fr; }
            .topbar { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside>
        <p class="brand">Toko<span>Ku</span></p>
        <nav aria-label="Navigasi utama">
            <p class="nav-label">Menu</p>
            <a class="nav-item active" href="index.php" aria-current="page">Ringkasan</a>
            <a class="nav-item" href="#produk">Produk</a>
            <a class="nav-item" href="#pesanan">Pesanan</a>
            <a class="nav-item" href="#laporan">Laporan</a>
        </nav>
    </aside>

    <main>
        <header class="topbar">
            <div>
                <h1>Ringkasan toko</h1>
                <p class="welcome">Selamat datang di dashboard toko Anda.</p>
            </div>
            <time class="date" datetime="<?= e(date('Y-m-d')) ?>"><?= e(date('j F Y')) ?></time>
        </header>

        <section class="status" aria-live="polite">
            <span class="dot" aria-hidden="true"></span>
            <p>
                <strong><?= $dbConnected ? 'Database terhubung' : ($dbConfigured ? 'Database belum terhubung' : 'Database belum dikonfigurasi') ?></strong>
                <small><?= e($connectionMessage) ?></small>
            </p>
        </section>

        <section class="cards" aria-label="Statistik toko">
            <article class="card">
                <p class="card-label">Penjualan hari ini</p>
                <p class="card-value">—</p>
                <p class="card-note">Data akan tampil setelah tabel transaksi disiapkan.</p>
            </article>
            <article class="card">
                <p class="card-label">Pesanan baru</p>
                <p class="card-value">—</p>
                <p class="card-note">Menunggu data pesanan dari database.</p>
            </article>
            <article class="card">
                <p class="card-label">Jumlah produk</p>
                <p class="card-value">—</p>
                <p class="card-note">Menunggu data produk dari database.</p>
            </article>
        </section>

        <section class="panel" id="pesanan">
            <h2>Mulai dari sini</h2>
            <p>Dashboard dasar sudah siap. Berikutnya, siapkan struktur tabel produk dan pesanan agar ringkasan dapat menampilkan data toko.</p>
        </section>
    </main>
</div>
</body>
</html>
