<?php
require 'config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// Hitung semua statistik
$total_judul = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM buku"))['jumlah'];
$total_eksemplar = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM eksemplar"))['jumlah'];
$eksemplar_tersedia = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM eksemplar WHERE status = 'tersedia'"))['jumlah'];
$sedang_dipinjam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM peminjaman WHERE tanggal_dikembalikan IS NULL"))['jumlah'];
$jumlah_anggota = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM users WHERE role = 'anggota' AND status = 'aktif'"))['jumlah'];
$terlambat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM peminjaman WHERE tanggal_dikembalikan IS NULL AND tanggal_jatuh_tempo < CURDATE()"))['jumlah'];
?>
<?php
$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<main class="main-content" id="main-content">
    <header class="page-header">
        <div class="page-heading">
            <p class="eyebrow">Ringkasan perpustakaan</p>
            <div class="page-title-row">
                <h1>Dashboard</h1>
                <span class="role-indicator"><?= htmlspecialchars(ucfirst($_SESSION['role'])) ?></span>
            </div>
            <p class="page-description">Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>. Berikut ringkasan koleksi dan aktivitas perpustakaan.</p>
        </div>
    </header>

    <section class="statistics-section" aria-labelledby="statistics-title">
        <div class="section-heading">
            <h2 id="statistics-title">Statistik perpustakaan</h2>
            <p>Ringkasan koleksi dan peminjaman saat ini.</p>
        </div>
        <div class="statistics-grid">
            <article class="statistic">
                <span class="statistic-label">Total Judul Buku</span>
                <strong class="statistic-value"><?= $total_judul ?></strong>
            </article>
            <article class="statistic">
                <span class="statistic-label">Total Eksemplar</span>
                <strong class="statistic-value"><?= $total_eksemplar ?></strong>
            </article>
            <article class="statistic statistic--available">
                <span class="statistic-label">Eksemplar Tersedia</span>
                <strong class="statistic-value"><?= $eksemplar_tersedia ?></strong>
            </article>
            <article class="statistic statistic--borrowed">
                <span class="statistic-label">Sedang Dipinjam</span>
                <strong class="statistic-value"><?= $sedang_dipinjam ?></strong>
            </article>
            <article class="statistic">
                <span class="statistic-label">Jumlah Anggota Aktif</span>
                <strong class="statistic-value"><?= $jumlah_anggota ?></strong>
            </article>
            <article class="statistic statistic--overdue">
                <span class="statistic-label">Peminjaman Terlambat</span>
                <strong class="statistic-value"><?= $terlambat ?></strong>
            </article>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
