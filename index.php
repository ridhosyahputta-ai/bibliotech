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
$sedang_dipinjam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM peminjaman WHERE status = 'dipinjam'"))['jumlah'];
$jumlah_anggota = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM users WHERE role = 'anggota' AND status = 'aktif'"))['jumlah'];
$terlambat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM peminjaman WHERE status = 'dipinjam' AND tanggal_jatuh_tempo < CURDATE()"))['jumlah'];
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard - Bibliotech</title></head>
<body>
    <h2>Dashboard Bibliotech</h2>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>! (<?= htmlspecialchars($_SESSION['role']) ?>)</p>
    <a href="logout.php">Logout</a>

    <h3>Statistik</h3>
    <table border="1" cellpadding="8">
        <tr><td>Total Judul Buku</td><td><?= $total_judul ?></td></tr>
        <tr><td>Total Eksemplar</td><td><?= $total_eksemplar ?></td></tr>
        <tr><td>Eksemplar Tersedia</td><td><?= $eksemplar_tersedia ?></td></tr>
        <tr><td>Sedang Dipinjam</td><td><?= $sedang_dipinjam ?></td></tr>
        <tr><td>Jumlah Anggota Aktif</td><td><?= $jumlah_anggota ?></td></tr>
        <tr><td>Peminjaman Terlambat</td><td style="color:<?= $terlambat > 0 ? 'red' : 'inherit' ?>;"><?= $terlambat ?></td></tr>
    </table>

    <h3>Menu</h3>
    <ul>
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="modules/kategori/index.php">Kelola Kategori</a></li>
        <li><a href="modules/buku/index.php">Kelola Buku</a></li>
        <li><a href="modules/user/index.php">Kelola User</a></li>
        <?php endif; ?>
        <?php if (in_array($_SESSION['role'], ['admin', 'petugas'])): ?>
        <li><a href="modules/peminjaman/index.php">Peminjaman & Pengembalian</a></li>
        <li><a href="modules/laporan/index.php">Laporan</a></li>
        <?php endif; ?>
    </ul>
</body>
</html>