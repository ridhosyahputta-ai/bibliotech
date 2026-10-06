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

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$sukses = "";

$pesan_hapus = [
    'sukses' => ['type' => 'sukses', 'text' => 'Eksemplar berhasil dihapus.'],
    'tidak_valid' => ['type' => 'error', 'text' => 'Eksemplar tidak ditemukan atau ID tidak valid.'],
    'terkait' => ['type' => 'error', 'text' => 'Eksemplar tidak dapat dihapus karena memiliki riwayat peminjaman.'],
    'gagal' => ['type' => 'error', 'text' => 'Eksemplar gagal dihapus. Silakan coba kembali.']
];
$hasil_hapus = $_GET['hapus'] ?? '';
if (is_string($hasil_hapus) && isset($pesan_hapus[$hasil_hapus])) {
    if ($pesan_hapus[$hasil_hapus]['type'] === 'sukses') {
        $sukses = $pesan_hapus[$hasil_hapus]['text'];
    } else {
        $error = $pesan_hapus[$hasil_hapus]['text'];
    }
}

// Proses tambah eksemplar baru
if (isset($_POST['tambah'])) {
    $csrf_token_session = $_SESSION['csrf_token'] ?? null;
    $csrf_token_request = $_POST['csrf_token'] ?? null;
    if (!is_string($csrf_token_session) || $csrf_token_session === '' || !is_string($csrf_token_request) || $csrf_token_request === '' || !hash_equals($csrf_token_session, $csrf_token_request)) {
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
<?php
$pageTitle = 'Kelola Eksemplar';
$activeMenu = 'buku';
require __DIR__ . '/../../includes/header.php';
?>
<main class="main-content" id="main-content">
    <header class="page-header">
        <div class="page-heading">
            <p class="eyebrow">Koleksi perpustakaan</p>
            <div class="page-title-row">
                <h1>Kelola Eksemplar</h1>
                <a class="category-button category-button--secondary" href="../buku/index.php">Kembali ke Daftar Buku</a>
            </div>
            <p class="page-description">Buku: <strong><?= htmlspecialchars($buku['judul']) ?></strong> oleh <?= htmlspecialchars($buku['penulis']) ?></p>
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

    <section class="category-panel exemplar-form-panel" aria-labelledby="add-exemplar-title">
        <div class="section-heading">
            <h2 id="add-exemplar-title">Tambah Eksemplar</h2>
            <p>Tambahkan satu eksemplar untuk buku ini.</p>
        </div>
        <form class="category-form" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="category-field">
                <label for="kode-eksemplar">Kode eksemplar</label>
                <input id="kode-eksemplar" type="text" name="kode_eksemplar" placeholder="Contoh: LP-001" required>
            </div>
            <button class="category-button category-button--primary" type="submit" name="tambah">Tambah</button>
        </form>
    </section>

    <section class="category-panel exemplar-list-panel" aria-labelledby="exemplar-list-title">
        <div class="section-heading">
            <h2 id="exemplar-list-title">Daftar Eksemplar</h2>
            <p>Eksemplar yang tercatat untuk buku ini.</p>
        </div>

        <?php if (mysqli_num_rows($eksemplar_result) === 0): ?>
            <div class="category-empty-state">
                <h3>Belum ada eksemplar</h3>
                <p>Eksemplar yang ditambahkan akan muncul di sini.</p>
            </div>
        <?php else: ?>
            <div class="category-table-scroll" tabindex="0" role="region" aria-label="Daftar eksemplar, dapat digulir secara horizontal">
                <table class="category-table exemplar-table">
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Kode eksemplar</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($eksemplar_result)): ?>
                        <tr>
                            <td class="category-number"><?= $no++ ?></td>
                            <td class="category-name"><?= htmlspecialchars($row['kode_eksemplar']) ?></td>
                            <td><span class="status-badge status-badge--<?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                            <td>
                                <div class="category-actions exemplar-actions">
                                    <a class="category-button category-button--secondary" href="edit.php?id=<?= $row['id_eksemplar'] ?>&buku_id=<?= $buku_id ?>">Edit</a>
                                    <form class="category-delete-form" method="POST" action="delete.php" onsubmit="return confirm('Yakin hapus eksemplar ini?');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="id_eksemplar" value="<?= $row['id_eksemplar'] ?>">
                                        <input type="hidden" name="buku_id" value="<?= $buku_id ?>">
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

