<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_kategori = (int) $_GET['id'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

// Proses update kalau form di-submit
if (isset($_POST['update'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Token keamanan tidak valid.";
    } else {
        $nama_kategori = trim($_POST['nama_kategori']);

        if ($nama_kategori === '') {
            $error = "Nama kategori tidak boleh kosong.";
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?");
            mysqli_stmt_bind_param($stmt, "si", $nama_kategori, $id_kategori);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php");
                exit();
            } else {
                if (mysqli_errno($koneksi) == 1062) {
                    $error = "Nama kategori '$nama_kategori' sudah dipakai.";
                } else {
                    $error = "Gagal mengubah kategori.";
                }
            }
        }
    }
}

// Ambil data kategori yang mau diedit (buat ditampilkan di form)
$stmt = mysqli_prepare($koneksi, "SELECT nama_kategori FROM kategori WHERE id_kategori = ?");
mysqli_stmt_bind_param($stmt, "i", $id_kategori);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Kategori - Bibliotech</title></head>
<body>
    <a href="index.php">&larr; Kembali</a>
    <h2>Edit Kategori</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="text" name="nama_kategori" value="<?= htmlspecialchars($data['nama_kategori']) ?>" required>
        <button type="submit" name="update">Simpan Perubahan</button>
    </form>
</body>
</html>