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

$csrf_token_session = $_SESSION['csrf_token'] ?? null;
$csrf_token_request = $_POST['csrf_token'] ?? null;
if (!is_string($csrf_token_session) || $csrf_token_session === '' || !is_string($csrf_token_request) || $csrf_token_request === '' || !hash_equals($csrf_token_session, $csrf_token_request)) {
    die("Token keamanan tidak valid.");
}

$id_peminjaman = filter_var($_POST['id_peminjaman'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if (!$id_peminjaman) {
    header("Location: index.php?pengembalian=tidak_valid");
    exit();
}

// Pastikan kegagalan query masuk ke penanganan rollback, bukan dianggap sukses.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$hasil_pengembalian = 'gagal';
$transaksi_aktif = false;

try {
    mysqli_begin_transaction($koneksi);
    $transaksi_aktif = true;

    // Kunci baris peminjaman ini dan cek apakah tanggal pengembaliannya sudah terisi.
    $stmt = mysqli_prepare($koneksi, "SELECT eksemplar_id, tanggal_dikembalikan FROM peminjaman WHERE id_peminjaman = ? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "i", $id_peminjaman);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $peminjaman = mysqli_fetch_assoc($result);

    if (!$peminjaman) {
        $hasil_pengembalian = 'tidak_valid';
        throw new Exception("Data peminjaman tidak ditemukan.");
    }
    if ($peminjaman['tanggal_dikembalikan'] !== null) {
        $hasil_pengembalian = 'sudah_dikembalikan';
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
    $transaksi_aktif = false;
    $hasil_pengembalian = 'sukses';

} catch (Throwable $e) {
    if ($transaksi_aktif) {
        try {
            mysqli_rollback($koneksi);
        } catch (Throwable $rollback_error) {
            $hasil_pengembalian = 'gagal';
        }
    }
}

header("Location: index.php?pengembalian=" . $hasil_pengembalian);
exit();
?>
