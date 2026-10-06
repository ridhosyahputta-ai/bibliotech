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

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";

if (isset($_POST['update'])) {
    $csrf_token_session = $_SESSION['csrf_token'] ?? null;
    $csrf_token_request = $_POST['csrf_token'] ?? null;
    if (!is_string($csrf_token_session) || $csrf_token_session === '' || !is_string($csrf_token_request) || $csrf_token_request === '' || !hash_equals($csrf_token_session, $csrf_token_request)) {
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
<?php
$pageTitle = 'Edit Kategori';
$activeMenu = 'kategori';
require __DIR__ . '/../../includes/header.php';
?>
<main class="main-content" id="main-content">
    <header class="page-header">
        <div class="page-heading">
            <p class="eyebrow">Koleksi perpustakaan</p>
            <div class="page-title-row">
                <h1>Edit Kategori</h1>
            </div>
            <p class="page-description">Perbarui nama kategori untuk menjaga pengelompokan koleksi tetap akurat.</p>
        </div>
    </header>

    <?php if ($error): ?>
        <div class="category-alert category-alert--error" role="alert">
            <strong>Perubahan belum disimpan</strong>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <section class="category-panel category-edit-panel" aria-labelledby="edit-category-title">
        <div class="section-heading">
            <h2 id="edit-category-title">Informasi kategori</h2>
            <p>Ubah nama kategori di bawah ini.</p>
        </div>
        <form class="category-form category-form--edit" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="category-field">
                <label for="nama-kategori">Nama kategori</label>
                <input id="nama-kategori" type="text" name="nama_kategori" value="<?= htmlspecialchars($data['nama_kategori']) ?>" required>
            </div>
            <div class="category-form-actions">
                <a class="category-button category-button--secondary" href="index.php">Kembali ke kategori</a>
                <button class="category-button category-button--primary" type="submit" name="update">Simpan perubahan</button>
            </div>
        </form>
    </section>
</main>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

