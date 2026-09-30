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
    header("Location: index.php");
    exit();
}

$csrf_token_session = $_SESSION['csrf_token'] ?? null;
$csrf_token_request = $_POST['csrf_token'] ?? null;
if (!is_string($csrf_token_session) || $csrf_token_session === '' || !is_string($csrf_token_request) || $csrf_token_request === '' || !hash_equals($csrf_token_session, $csrf_token_request)) {
    die("Token keamanan tidak valid.");
}

$id_buku = filter_var($_POST['id_buku'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$hasil_hapus = 'gagal';

if (!$id_buku) {
    $hasil_hapus = 'tidak_valid';
} else {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM buku WHERE id_buku = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_buku);
        mysqli_stmt_execute($stmt);
        $hasil_hapus = mysqli_stmt_affected_rows($stmt) > 0 ? 'sukses' : 'tidak_valid';
    } catch (mysqli_sql_exception $e) {
        $hasil_hapus = $e->getCode() === 1451 ? 'terkait' : 'gagal';
    } catch (Throwable $e) {
        $hasil_hapus = 'gagal';
    }
}

header("Location: index.php?hapus=" . $hasil_hapus);
exit();
?>
