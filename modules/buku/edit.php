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

$id_buku = (int) $_GET['id'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

// Proses update kalau form di-submit
if (isset($_POST['update'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Token keamanan tidak valid.";
    } else {
        $isbn         = trim($_POST['isbn']);
        $judul        = trim($_POST['judul']);
        $penulis      = trim($_POST['penulis']);
        $penerbit     = trim($_POST['penerbit']);
        $tahun_terbit = $_POST['tahun_terbit'] !== '' ? (int) $_POST['tahun_terbit'] : null;
        $kategori_id  = $_POST['kategori_id'] !== '' ? (int) $_POST['kategori_id'] : null;
        $deskripsi    = trim($_POST['deskripsi']);

        if ($judul === '' || $penulis === '') {
            $error = "Judul dan penulis wajib diisi.";
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE buku SET isbn = ?, judul = ?, penulis = ?, penerbit = ?, tahun_terbit = ?, kategori_id = ?, deskripsi = ? WHERE id_buku = ?");
            mysqli_stmt_bind_param($stmt, "ssssiisi", $isbn, $judul, $penulis, $penerbit, $tahun_terbit, $kategori_id, $deskripsi, $id_buku);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php");
                exit();
            } else {
                $error = "Gagal mengubah buku: " . mysqli_error($koneksi);
            }
        }
    }
}

// Ambil data buku yang mau diedit
$stmt = mysqli_prepare($koneksi, "SELECT * FROM buku WHERE id_buku = ?");
mysqli_stmt_bind_param($stmt, "i", $id_buku);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: index.php");
    exit();
}

// Ambil semua kategori buat dropdown
$kategori_result = mysqli_query($koneksi, "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
?>
<!DOCTYPE html>
<html>
<head><title>Edit Buku - Bibliotech</title></head>
<body>
    <a href="index.php">&larr; Kembali</a>
    <h2>Edit Buku</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label>ISBN</label><br>
        <input type="text" name="isbn" value="<?= htmlspecialchars($data['isbn'] ?? '') ?>"><br><br>
        <label>Judul</label><br>
        <input type="text" name="judul" value="<?= htmlspecialchars($data['judul']) ?>" required><br><br>
        <label>Penulis</label><br>
        <input type="text" name="penulis" value="<?= htmlspecialchars($data['penulis']) ?>" required><br><br>
        <label>Penerbit</label><br>
        <input type="text" name="penerbit" value="<?= htmlspecialchars($data['penerbit'] ?? '') ?>"><br><br>
        <label>Tahun Terbit</label><br>
        <input type="number" name="tahun_terbit" value="<?= htmlspecialchars($data['tahun_terbit'] ?? '') ?>"><br><br>
        <label>Kategori</label><br>
        <select name="kategori_id">
            <option value="">-- Tanpa Kategori --</option>
            <?php while ($kat = mysqli_fetch_assoc($kategori_result)): ?>
                <option value="<?= $kat['id_kategori'] ?>" <?= $data['kategori_id'] == $kat['id_kategori'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>
        <label>Deskripsi</label><br>
        <textarea name="deskripsi"><?= htmlspecialchars($data['deskripsi'] ?? '') ?></textarea><br><br>
        <button type="submit" name="update">Simpan Perubahan</button>
    </form>
</body>
</html>