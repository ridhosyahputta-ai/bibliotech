<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

if (!isset($_GET['id']) || !isset($_GET['buku_id'])) {
    header("Location: ../buku/index.php");
    exit();
}

$id_eksemplar = (int) $_GET['id'];
$buku_id = (int) $_GET['buku_id'];

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

// Proses update kalau form di-submit
if (isset($_POST['update'])) {
    $csrf_token_session = $_SESSION['csrf_token'] ?? null;
    $csrf_token_request = $_POST['csrf_token'] ?? null;
    if (!is_string($csrf_token_session) || $csrf_token_session === '' || !is_string($csrf_token_request) || $csrf_token_request === '' || !hash_equals($csrf_token_session, $csrf_token_request)) {
        $error = "Token keamanan tidak valid.";
    } else {
        $kode_eksemplar = trim($_POST['kode_eksemplar']);
        $status = $_POST['status'] ?? '';
        $pilihan_status = ['tersedia', 'dipinjam', 'rusak', 'hilang'];

        if (!in_array($status, $pilihan_status, true)) {
            $error = "Status eksemplar tidak valid.";
        } else {
            mysqli_begin_transaction($koneksi);

            try {
                // Kunci eksemplar agar pemeriksaan pinjaman aktif tidak berlomba dengan proses pinjam/kembali.
                $stmt = mysqli_prepare($koneksi, "SELECT status FROM eksemplar WHERE id_eksemplar = ? FOR UPDATE");
                mysqli_stmt_bind_param($stmt, "i", $id_eksemplar);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $eksemplar_terkunci = mysqli_fetch_assoc($result);

                if (!$eksemplar_terkunci) {
                    throw new Exception("Eksemplar tidak ditemukan.");
                }

                $stmt = mysqli_prepare($koneksi, "SELECT id_peminjaman FROM peminjaman WHERE eksemplar_id = ? AND tanggal_dikembalikan IS NULL LIMIT 1");
                mysqli_stmt_bind_param($stmt, "i", $id_eksemplar);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $peminjaman_aktif = mysqli_fetch_assoc($result);

                if ($peminjaman_aktif && $status !== $eksemplar_terkunci['status']) {
                    throw new Exception("Status tidak dapat diubah karena eksemplar masih memiliki peminjaman aktif.");
                }

                $stmt = mysqli_prepare($koneksi, "UPDATE eksemplar SET kode_eksemplar = ?, status = ? WHERE id_eksemplar = ?");
                mysqli_stmt_bind_param($stmt, "ssi", $kode_eksemplar, $status, $id_eksemplar);

                if (!mysqli_stmt_execute($stmt)) {
                    if (mysqli_errno($koneksi) == 1062) {
                        throw new Exception("Kode eksemplar '$kode_eksemplar' sudah dipakai.");
                    }
                    throw new Exception("Gagal mengubah eksemplar.");
                }

                mysqli_commit($koneksi);
                header("Location: index.php?buku_id=$buku_id");
                exit();
            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $error = $e->getMessage();
            }
        }
    }
}

// Ambil data eksemplar yang mau diedit
$stmt = mysqli_prepare($koneksi, "SELECT kode_eksemplar, status FROM eksemplar WHERE id_eksemplar = ?");
mysqli_stmt_bind_param($stmt, "i", $id_eksemplar);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: index.php?buku_id=$buku_id");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Eksemplar - Bibliotech</title></head>
<body>
    <a href="index.php?buku_id=<?= $buku_id ?>">&larr; Kembali</a>
    <h2>Edit Eksemplar</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label>Kode Eksemplar</label><br>
        <input type="text" name="kode_eksemplar" value="<?= htmlspecialchars($data['kode_eksemplar']) ?>" required><br><br>
        <label>Status</label><br>
        <select name="status">
            <?php
            $pilihan_status = ['tersedia', 'dipinjam', 'rusak', 'hilang'];
            foreach ($pilihan_status as $opsi):
            ?>
                <option value="<?= $opsi ?>" <?= $data['status'] === $opsi ? 'selected' : '' ?>>
                    <?= ucfirst($opsi) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>
        <button type="submit" name="update">Simpan Perubahan</button>
    </form>
</body>
</html>
