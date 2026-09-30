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

$csrf_token_session = $_SESSION['csrf_token'] ?? null;
$csrf_token_request = $_POST['csrf_token'] ?? null;
if (!is_string($csrf_token_session) || $csrf_token_session === '' || !is_string($csrf_token_request) || $csrf_token_request === '' || !hash_equals($csrf_token_session, $csrf_token_request)) {
    die("Token keamanan tidak valid.");
}

$id_eksemplar = filter_var($_POST['id_eksemplar'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$buku_id = filter_var($_POST['buku_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$buku_id) {
    header("Location: ../buku/index.php?hapus=tidak_valid");
    exit();
}

$hasil_hapus = 'gagal';
if (!$id_eksemplar) {
    $hasil_hapus = 'tidak_valid';
} else {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM eksemplar WHERE id_eksemplar = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_eksemplar);
        mysqli_stmt_execute($stmt);
        $hasil_hapus = mysqli_stmt_affected_rows($stmt) > 0 ? 'sukses' : 'tidak_valid';
    } catch (mysqli_sql_exception $e) {
        $hasil_hapus = $e->getCode() === 1451 ? 'terkait' : 'gagal';
    } catch (Throwable $e) {
        $hasil_hapus = 'gagal';
    }
}

header("Location: index.php?buku_id=" . $buku_id . "&hapus=" . $hasil_hapus);
exit();
?>
