<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Halaman ini khusus admin.");
}

// Generate CSRF token kalau belum ada di session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$sukses = "";

// Proses tambah kategori baru
if (isset($_POST['tambah'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Token keamanan tidak valid.";
    } else {
        $nama_kategori = trim($_POST['nama_kategori']);

        if ($nama_kategori === '') {
            $error = "Nama kategori tidak boleh kosong.";
        } else {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO kategori (nama_kategori) VALUES (?)");
            mysqli_stmt_bind_param($stmt, "s", $nama_kategori);

            if (mysqli_stmt_execute($stmt)) {
                $sukses = "Kategori '$nama_kategori' berhasil ditambahkan.";
            } else {
                if (mysqli_errno($koneksi) == 1062) {
                    $error = "Kategori '$nama_kategori' sudah ada.";
                } else {
                    $error = "Gagal menambah kategori.";
                }
            }
        }
    }
}

// Ambil semua kategori buat ditampilkan
$result = mysqli_query($koneksi, "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
?>
<!DOCTYPE html>
<html>
<head><title>Kelola Kategori - Bibliotech</title></head>
<body>
    <a href="../../index.php">&larr; Dashboard</a>
    <h2>Kelola Kategori Buku</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($sukses): ?>
        <p style="color:green;"><?= htmlspecialchars($sukses) ?></p>
    <?php endif; ?>

    <h3>Tambah Kategori</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="text" name="nama_kategori" placeholder="Nama kategori" required>
        <button type="submit" name="tambah">Tambah</button>
    </form>

    <h3>Daftar Kategori</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>No</th>
            <th>Nama Kategori</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
            <td>
                <a href="edit.php?id=<?= $row['id_kategori'] ?>">Edit</a>
                |
                <form method="POST" action="delete.php" style="display:inline;" onsubmit="return confirm('Yakin hapus kategori ini?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="id_kategori" value="<?= $row['id_kategori'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>