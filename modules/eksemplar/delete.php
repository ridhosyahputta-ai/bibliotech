<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../buku/index.php");
    exit();
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die("Token keamanan tidak valid.");
}

$id_eksemplar = (int) $_POST['id_eksemplar'];
$buku_id = (int) $_POST['buku_id'];

$stmt = mysqli_prepare($koneksi, "DELETE FROM eksemplar WHERE id_eksemplar = ?");
mysqli_stmt_bind_param($stmt, "i", $id_eksemplar);
mysqli_stmt_execute($stmt);

header("Location: index.php?buku_id=$buku_id");
exit();
?>