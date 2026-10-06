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
<?php
$pageTitle = 'Edit Buku';
$activeMenu = 'buku';
require __DIR__ . '/../../includes/header.php';
?>
<main class="main-content" id="main-content">
    <header class="page-header">
        <div class="page-heading">
            <p class="eyebrow">Koleksi perpustakaan</p>
            <div class="page-title-row">
                <h1>Edit Buku</h1>
            </div>
            <p class="page-description">Perbarui informasi bibliografi buku dalam koleksi.</p>
        </div>
    </header>

    <?php if ($error): ?>
        <div class="category-alert category-alert--error" role="alert">
            <strong>Perubahan belum disimpan</strong>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <section class="category-panel book-form-panel" aria-labelledby="edit-book-title">
        <div class="section-heading">
            <h2 id="edit-book-title">Informasi Buku</h2>
            <p>Periksa dan perbarui field yang diperlukan.</p>
        </div>
        <form class="category-form category-form--book" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="category-field">
                <label for="isbn">ISBN</label>
                <input id="isbn" type="text" name="isbn" value="<?= htmlspecialchars($data['isbn'] ?? '') ?>">
            </div>
            <div class="category-field">
                <label for="judul">Judul</label>
                <input id="judul" type="text" name="judul" value="<?= htmlspecialchars($data['judul']) ?>" required>
            </div>
            <div class="category-field">
                <label for="penulis">Penulis</label>
                <input id="penulis" type="text" name="penulis" value="<?= htmlspecialchars($data['penulis']) ?>" required>
            </div>
            <div class="category-field">
                <label for="penerbit">Penerbit</label>
                <input id="penerbit" type="text" name="penerbit" value="<?= htmlspecialchars($data['penerbit'] ?? '') ?>">
            </div>
            <div class="category-field">
                <label for="tahun-terbit">Tahun Terbit</label>
                <input id="tahun-terbit" type="number" name="tahun_terbit" min="1000" max="<?= date('Y') ?>" value="<?= htmlspecialchars($data['tahun_terbit'] ?? '') ?>">
            </div>
            <div class="category-field">
                <label for="kategori-id">Kategori</label>
                <select class="book-field-control" id="kategori-id" name="kategori_id">
                    <option value="">-- Tanpa Kategori --</option>
                    <?php while ($kat = mysqli_fetch_assoc($kategori_result)): ?>
                        <option value="<?= $kat['id_kategori'] ?>" <?= $data['kategori_id'] == $kat['id_kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="category-field book-field--wide">
                <label for="deskripsi">Deskripsi</label>
                <textarea class="book-field-control" id="deskripsi" name="deskripsi" rows="4"><?= htmlspecialchars($data['deskripsi'] ?? '') ?></textarea>
            </div>
            <div class="category-form-actions book-form-actions">
                <a class="category-button category-button--secondary" href="index.php">Kembali ke daftar buku</a>
                <button class="category-button category-button--primary" type="submit" name="update">Simpan Perubahan</button>
            </div>
        </form>
    </section>
</main>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

