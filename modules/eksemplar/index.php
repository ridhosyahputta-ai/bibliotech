<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

if (!isset($_GET['buku_id'])) {
    header("Location: ../buku/index.php");
    exit();
}

$buku_id = (int) $_GET['buku_id'];

// Ambil data buku induknya dulu, buat ditampilkan sebagai konteks
$stmt = mysqli_prepare($koneksi, "SELECT judul, penulis FROM buku WHERE id_buku = ?");
mysqli_stmt_bind_param($stmt, "i", $buku_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$buku = mysqli_fetch_assoc($result);

if (!$buku) {
    header("Location: ../buku/index.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$sukses = "";

// Proses tambah eksemplar baru
if (isset($_POST['tambah'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Token keamanan tidak valid.";
    } else {
        $kode_eksemplar = trim($_POST['kode_eksemplar']);

        if ($kode_eksemplar === '') {
            $error = "Kode eksemplar tidak boleh kosong.";
        } else {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO eksemplar (buku_id, kode_eksemplar, status) VALUES (?, ?, 'tersedia')");
            mysqli_stmt_bind_param($stmt, "is", $buku_id, $kode_eksemplar);

            if (mysqli_stmt_execute($stmt)) {
                $sukses = "Eksemplar '$kode_eksemplar' berhasil ditambahkan.";
            } else {
                if (mysqli_errno($koneksi) == 1062) {
                    $error = "Kode eksemplar '$kode_eksemplar' sudah dipakai.";
                } else {
                    $error = "Gagal menambah eksemplar.";
                }
            }
        }
    }
}

// Ambil semua eksemplar dari buku ini
$stmt = mysqli_prepare($koneksi, "SELECT id_eksemplar, kode_eksemplar, status FROM eksemplar WHERE buku_id = ? ORDER BY kode_eksemplar ASC");
mysqli_stmt_bind_param($stmt, "i", $buku_id);
mysqli_stmt_execute($stmt);
$eksemplar_result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html>
<head><title>Kelola Eksemplar - Bibliotech</title></head>
<body>
    <a href="../buku/index.php">&larr; Daftar Buku</a>
    <h2>Kelola Eksemplar</h2>
    <p>Buku: <strong><?= htmlspecialchars($buku['judul']) ?></strong> oleh <?= htmlspecialchars($buku['penulis']) ?></p>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($sukses): ?>
        <p style="color:green;"><?= htmlspecialchars($sukses) ?></p>
    <?php endif; ?>

    <h3>Tambah Eksemplar</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="text" name="kode_eksemplar" placeholder="Contoh: LP-001" required>
        <button type="submit" name="tambah">Tambah</button>
    </form>

    <h3>Daftar Eksemplar</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($eksemplar_result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['kode_eksemplar']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <a href="edit.php?id=<?= $row['id_eksemplar'] ?>&buku_id=<?= $buku_id ?>">Edit</a>
                |
                <form method="POST" action="delete.php" style="display:inline;" onsubmit="return confirm('Yakin hapus eksemplar ini?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="id_eksemplar" value="<?= $row['id_eksemplar'] ?>">
                    <input type="hidden" name="buku_id" value="<?= $buku_id ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>