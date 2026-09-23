<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if (!in_array($_SESSION['role'], ['admin', 'petugas'])) {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die("Token keamanan tidak valid.");
}

$id_peminjaman = (int) $_POST['id_peminjaman'];

mysqli_begin_transaction($koneksi);

try {
    // Kunci baris peminjaman ini, ambil eksemplar_id-nya, cek status masih aktif
    $stmt = mysqli_prepare($koneksi, "SELECT eksemplar_id, status FROM peminjaman WHERE id_peminjaman = ? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "i", $id_peminjaman);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $peminjaman = mysqli_fetch_assoc($result);

    if (!$peminjaman) {
        throw new Exception("Data peminjaman tidak ditemukan.");
    }
    if ($peminjaman['status'] === 'dikembalikan') {
        throw new Exception("Buku ini sudah dikembalikan sebelumnya.");
    }

    $eksemplar_id = $peminjaman['eksemplar_id'];
    $tanggal_dikembalikan = date('Y-m-d');

    // Update status peminjaman jadi dikembalikan
    $stmt = mysqli_prepare($koneksi, "UPDATE peminjaman SET status = 'dikembalikan', tanggal_dikembalikan = ? WHERE id_peminjaman = ?");
    mysqli_stmt_bind_param($stmt, "si", $tanggal_dikembalikan, $id_peminjaman);
    mysqli_stmt_execute($stmt);

    // Update status eksemplar balik jadi tersedia
    $stmt = mysqli_prepare($koneksi, "UPDATE eksemplar SET status = 'tersedia' WHERE id_eksemplar = ?");
    mysqli_stmt_bind_param($stmt, "i", $eksemplar_id);
    mysqli_stmt_execute($stmt);

    mysqli_commit($koneksi);

} catch (Exception $e) {
    mysqli_rollback($koneksi);
}

header("Location: index.php");
exit();
?>