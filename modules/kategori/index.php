<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Halaman ini khusus admin.");
}

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$sukses = "";

$pesan_hapus = [
    'sukses' => ['type' => 'sukses', 'text' => 'Kategori berhasil dihapus. Buku yang menggunakannya tetap ada tanpa kategori.'],
    'tidak_valid' => ['type' => 'error', 'text' => 'Kategori tidak ditemukan atau ID tidak valid.'],
    'terkait' => ['type' => 'error', 'text' => 'Kategori tidak dapat dihapus karena masih digunakan.'],
    'gagal' => ['type' => 'error', 'text' => 'Kategori gagal dihapus. Silakan coba kembali.']
];
$hasil_hapus = $_GET['hapus'] ?? '';
if (is_string($hasil_hapus) && isset($pesan_hapus[$hasil_hapus])) {
    if ($pesan_hapus[$hasil_hapus]['type'] === 'sukses') {
        $sukses = $pesan_hapus[$hasil_hapus]['text'];
    } else {
        $error = $pesan_hapus[$hasil_hapus]['text'];
    }
}

if (isset($_POST['tambah'])) {
    $csrf_token_session = $_SESSION['csrf_token'] ?? null;
    $csrf_token_request = $_POST['csrf_token'] ?? null;
    if (!is_string($csrf_token_session) || $csrf_token_session === '' || !is_string($csrf_token_request) || $csrf_token_request === '' || !hash_equals($csrf_token_session, $csrf_token_request)) {
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

$result = mysqli_query($koneksi, "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
?>
<?php
$pageTitle = 'Kategori';
$activeMenu = 'kategori';
require __DIR__ . '/../../includes/header.php';
?>
<main class="main-content" id="main-content">
    <header class="page-header">
        <div class="page-heading">
            <p class="eyebrow">Koleksi perpustakaan</p>
            <div class="page-title-row">
                <h1>Kategori Buku</h1>
            </div>
            <p class="page-description">Kelola pengelompokan buku agar koleksi lebih mudah ditelusuri.</p>
        </div>
    </header>

    <?php if ($error): ?>
        <div class="category-alert category-alert--error" role="alert">
            <strong>Terjadi masalah</strong>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($sukses): ?>
        <div class="category-alert category-alert--success" role="status">
            <strong>Berhasil</strong>
            <span><?= htmlspecialchars($sukses) ?></span>
        </div>
    <?php endif; ?>

    <section class="category-panel category-form-panel" aria-labelledby="add-category-title">
        <div class="section-heading">
            <h2 id="add-category-title">Tambah kategori</h2>
            <p>Tambahkan kategori untuk mengelompokkan koleksi buku.</p>
        </div>
        <form class="category-form" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="category-field">
                <label for="nama-kategori">Nama kategori</label>
                <input id="nama-kategori" type="text" name="nama_kategori" placeholder="Contoh: Sejarah" required>
            </div>
            <button class="category-button category-button--primary" type="submit" name="tambah">Tambah kategori</button>
        </form>
    </section>

    <section class="category-panel category-list-panel" aria-labelledby="category-list-title">
        <div class="section-heading category-list-heading">
            <div>
                <h2 id="category-list-title">Daftar kategori</h2>
                <p>Kategori yang tersedia di koleksi perpustakaan.</p>
            </div>
        </div>

        <?php if (mysqli_num_rows($result) === 0): ?>
            <div class="category-empty-state">
                <h3>Belum ada kategori</h3>
                <p>Kategori yang ditambahkan akan muncul di sini.</p>
            </div>
        <?php else: ?>
            <div class="category-table-scroll" tabindex="0" role="region" aria-label="Daftar kategori, dapat digulir secara horizontal">
                <table class="category-table">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Nama kategori</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="category-number"><?= $no++ ?></td>
                            <td class="category-name"><?= htmlspecialchars($row['nama_kategori']) ?></td>
                            <td>
                                <div class="category-actions">
                                    <a class="category-button category-button--secondary" href="edit.php?id=<?= $row['id_kategori'] ?>">Edit</a>
                                    <form class="category-delete-form" method="POST" action="delete.php" onsubmit="return confirm('Yakin hapus kategori ini?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="id_kategori" value="<?= $row['id_kategori'] ?>">
                                        <button class="category-button category-button--danger" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/../../includes/footer.php'; ?>

