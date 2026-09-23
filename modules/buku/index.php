<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$sukses = "";

// Proses tambah buku baru
if (isset($_POST['tambah'])) {
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
            $stmt = mysqli_prepare($koneksi, "INSERT INTO buku (isbn, judul, penulis, penerbit, tahun_terbit, kategori_id, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssiis", $isbn, $judul, $penulis, $penerbit, $tahun_terbit, $kategori_id, $deskripsi);

            if (mysqli_stmt_execute($stmt)) {
                $sukses = "Buku '$judul' berhasil ditambahkan.";
            } else {
                $error = "Gagal menambah buku: " . mysqli_error($koneksi);
            }
        }
    }
}

// Ambil semua kategori buat dropdown
$kategori_result = mysqli_query($koneksi, "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");

// Ambil semua buku + nama kategorinya (LEFT JOIN biar buku tanpa kategori tetap muncul)
$buku_result = mysqli_query($koneksi, "
    SELECT buku.id_buku, buku.judul, buku.penulis, buku.tahun_terbit, kategori.nama_kategori
    FROM buku
    LEFT JOIN kategori ON buku.kategori_id = kategori.id_kategori
    ORDER BY buku.judul ASC
");
?>
<!DOCTYPE html>
<html>
<head><title>Kelola Buku - Bibliotech</title></head>
<body>
    <a href="../../index.php">&larr; Dashboard</a>
    <h2>Kelola Buku</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($sukses): ?>
        <p style="color:green;"><?= htmlspecialchars($sukses) ?></p>
    <?php endif; ?>

    <h3>Tambah Buku</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label>ISBN</label><br>
        <input type="text" name="isbn"><br><br>
        <label>Judul</label><br>
        <input type="text" name="judul" required><br><br>
        <label>Penulis</label><br>
        <input type="text" name="penulis" required><br><br>
        <label>Penerbit</label><br>
        <input type="text" name="penerbit"><br><br>
        <label>Tahun Terbit</label><br>
        <input type="number" name="tahun_terbit"><br><br>
        <label>Kategori</label><br>
        <select name="kategori_id">
            <option value="">-- Tanpa Kategori --</option>
            <?php while ($kat = mysqli_fetch_assoc($kategori_result)): ?>
                <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
            <?php endwhile; ?>
        </select><br><br>
        <label>Deskripsi</label><br>
        <textarea name="deskripsi"></textarea><br><br>
        <button type="submit" name="tambah">Tambah Buku</button>
    </form>

    <h3>Daftar Buku</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Penulis</th>
            <th>Tahun</th>
            <th>Kategori</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($buku_result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['judul']) ?></td>
            <td><?= htmlspecialchars($row['penulis']) ?></td>
            <td><?= htmlspecialchars($row['tahun_terbit'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori') ?></td>
            <td>
                <a href="edit.php?id=<?= $row['id_buku'] ?>">Edit</a>
                |
                <a href="../eksemplar/index.php?buku_id=<?= $row['id_buku'] ?>">Kelola Eksemplar</a>
                |
                <form method="POST" action="delete.php" style="display:inline;" onsubmit="return confirm('Yakin hapus buku ini? Semua eksemplarnya juga akan terhapus.');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="id_buku" value="<?= $row['id_buku'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>