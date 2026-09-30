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
        $isbn         = trim($_POST['isbn']);
        $judul        = trim($_POST['judul']);
        $penulis      = trim($_POST['penulis']);
        $penerbit     = trim($_POST['penerbit']);
        $tahun_input = $_POST['tahun_terbit'] ?? '';
        $tahun_maksimum = (int) date('Y');
        $tahun_terbit = null;
        $tahun_valid = true;

        if (!is_string($tahun_input)) {
            $tahun_valid = false;
        } else {
            $tahun_input = trim($tahun_input);
            if ($tahun_input !== '') {
                if (!ctype_digit($tahun_input)) {
                    $tahun_valid = false;
                } else {
                    $tahun_terbit = (int) $tahun_input;
                    if ($tahun_terbit < 1000 || $tahun_terbit > $tahun_maksimum) {
                        $tahun_valid = false;
                    }
                }
            }
        }
        $kategori_id  = $_POST['kategori_id'] !== '' ? (int) $_POST['kategori_id'] : null;
        $deskripsi    = trim($_POST['deskripsi']);

        if (!$tahun_valid) {
            $error = "Tahun terbit harus berupa tahun utuh antara 1000 dan $tahun_maksimum, atau dikosongkan.";
        } elseif ($judul === '' || $penulis === '') {
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
        <input type="number" name="tahun_terbit" min="1000" max="<?= date('Y') ?>" value="<?= htmlspecialchars($data['tahun_terbit'] ?? '') ?>"><br><br>
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
