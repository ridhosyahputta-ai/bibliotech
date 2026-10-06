<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$sukses = "";

$pesan_hapus = [
    'sukses' => ['type' => 'sukses', 'text' => 'Buku berhasil dihapus beserta eksemplarnya.'],
    'tidak_valid' => ['type' => 'error', 'text' => 'Data tidak ditemukan atau ID tidak valid.'],
    'terkait' => ['type' => 'error', 'text' => 'Buku gagal dihapus karena riwayat peminjaman masih terkait.'],
    'gagal' => ['type' => 'error', 'text' => 'Buku gagal dihapus. Silakan coba kembali.']
];
$hasil_hapus = $_GET['hapus'] ?? '';
if (is_string($hasil_hapus) && isset($pesan_hapus[$hasil_hapus])) {
    if ($pesan_hapus[$hasil_hapus]['type'] === 'sukses') {
        $sukses = $pesan_hapus[$hasil_hapus]['text'];
    } else {
        $error = $pesan_hapus[$hasil_hapus]['text'];
    }
}

// Proses tambah buku baru
if (isset($_POST['tambah'])) {
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
<?php
$pageTitle = 'Buku';
$activeMenu = 'buku';
require __DIR__ . '/../../includes/header.php';
?>
<main class="main-content" id="main-content">
    <header class="page-header">
        <div class="page-heading">
            <p class="eyebrow">Koleksi perpustakaan</p>
            <div class="page-title-row">
                <h1>Kelola Buku</h1>
            </div>
            <p class="page-description">Kelola informasi bibliografi dan akses eksemplar untuk setiap judul buku.</p>
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

    <section class="category-panel book-form-panel" aria-labelledby="add-book-title">
        <div class="section-heading">
            <h2 id="add-book-title">Tambah Buku</h2>
            <p>Masukkan informasi utama untuk menambahkan judul ke koleksi.</p>
        </div>
        <form class="category-form category-form--book" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="category-field">
                <label for="isbn">ISBN</label>
                <input id="isbn" type="text" name="isbn">
            </div>
            <div class="category-field">
                <label for="judul">Judul</label>
                <input id="judul" type="text" name="judul" required>
            </div>
            <div class="category-field">
                <label for="penulis">Penulis</label>
                <input id="penulis" type="text" name="penulis" required>
            </div>
            <div class="category-field">
                <label for="penerbit">Penerbit</label>
                <input id="penerbit" type="text" name="penerbit">
            </div>
            <div class="category-field">
                <label for="tahun-terbit">Tahun Terbit</label>
                <input id="tahun-terbit" type="number" name="tahun_terbit" min="1000" max="<?= date('Y') ?>">
            </div>
            <div class="category-field">
                <label for="kategori-id">Kategori</label>
                <select class="book-field-control" id="kategori-id" name="kategori_id">
                    <option value="">-- Tanpa Kategori --</option>
                    <?php while ($kat = mysqli_fetch_assoc($kategori_result)): ?>
                        <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="category-field book-field--wide">
                <label for="deskripsi">Deskripsi</label>
                <textarea class="book-field-control" id="deskripsi" name="deskripsi" rows="4"></textarea>
            </div>
            <button class="category-button category-button--primary book-form-submit" type="submit" name="tambah">Tambah Buku</button>
        </form>
    </section>

    <section class="category-panel book-list-panel" aria-labelledby="book-list-title">
        <div class="section-heading">
            <h2 id="book-list-title">Daftar Buku</h2>
            <p>Daftar judul buku dan kategori yang terkait.</p>
        </div>

        <?php if (mysqli_num_rows($buku_result) === 0): ?>
            <div class="category-empty-state">
                <h3>Belum ada buku</h3>
                <p>Buku yang ditambahkan akan muncul di sini.</p>
            </div>
        <?php else: ?>
            <div class="category-table-scroll" tabindex="0" role="region" aria-label="Daftar buku, dapat digulir secara horizontal">
                <table class="category-table book-table">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Penulis</th>
                            <th scope="col">Tahun</th>
                            <th scope="col">Kategori</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($buku_result)): ?>
                        <tr>
                            <td class="category-number"><?= $no++ ?></td>
                            <td class="category-name book-title"><?= htmlspecialchars($row['judul']) ?></td>
                            <td><?= htmlspecialchars($row['penulis']) ?></td>
                            <td><?= htmlspecialchars($row['tahun_terbit'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori') ?></td>
                            <td>
                                <div class="category-actions book-actions">
                                    <a class="category-button category-button--primary book-related-action" href="../eksemplar/index.php?buku_id=<?= $row['id_buku'] ?>">Kelola Eksemplar</a>
                                    <a class="category-button category-button--secondary" href="edit.php?id=<?= $row['id_buku'] ?>">Edit</a>
                                    <form class="category-delete-form" method="POST" action="delete.php" onsubmit="return confirm('Yakin hapus buku ini? Semua eksemplarnya juga akan terhapus.');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="id_buku" value="<?= $row['id_buku'] ?>">
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

