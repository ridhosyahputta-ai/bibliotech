<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die("Token keamanan tidak valid.");
}

$id_kategori = (int) $_POST['id_kategori'];

$stmt = mysqli_prepare($koneksi, "DELETE FROM kategori WHERE id_kategori = ?");
mysqli_stmt_bind_param($stmt, "i", $id_kategori);
mysqli_stmt_execute($stmt);

header("Location: index.php");
exit();
?>