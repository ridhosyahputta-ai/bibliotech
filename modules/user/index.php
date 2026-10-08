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

// Proses tambah user baru
if (isset($_POST['tambah'])) {
    $csrf_token_session = $_SESSION['csrf_token'] ?? null;
    $csrf_token_request = $_POST['csrf_token'] ?? null;
    if (!is_string($csrf_token_session) || $csrf_token_session === '' || !is_string($csrf_token_request) || $csrf_token_request === '' || !hash_equals($csrf_token_session, $csrf_token_request)) {
        $error = "Token keamanan tidak valid.";
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $nama     = trim($_POST['nama']);
        $role     = $_POST['role'];

        if ($username === '' || $password === '' || $nama === '') {
            $error = "Semua field wajib diisi.";
        } elseif (!in_array($role, ['admin', 'petugas', 'anggota'])) {
            $error = "Role tidak valid.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, password_hash, nama, role, status) VALUES (?, ?, ?, ?, 'aktif')");
            mysqli_stmt_bind_param($stmt, "ssss", $username, $password_hash, $nama, $role);

            if (mysqli_stmt_execute($stmt)) {
                $sukses = "User '$username' berhasil ditambahkan.";
            } else {
                if (mysqli_errno($koneksi) == 1062) {
                    $error = "Username '$username' sudah dipakai.";
                } else {
                    $error = "Gagal menambah user.";
                }
            }
        }
    }
}

// Ambil semua user
$result = mysqli_query($koneksi, "SELECT id_user, username, nama, role, status FROM users ORDER BY role ASC, nama ASC");
?>
<?php
$pageTitle = 'Kelola Pengguna';
$activeMenu = 'user';
// Nilai tampilan saja: pertahankan input non-password ketika validasi gagal.
$userFormUsername = $error && is_string($_POST['username'] ?? null) ? $_POST['username'] : '';
$userFormNama = $error && is_string($_POST['nama'] ?? null) ? $_POST['nama'] : '';
$userFormRole = $error && is_string($_POST['role'] ?? null) && in_array($_POST['role'], ['admin', 'petugas', 'anggota'], true) ? $_POST['role'] : 'anggota';
require __DIR__ . '/../../includes/header.php';
?>
<main class="main-content user-management" id="main-content">
    <header class="page-header">
        <div class="page-heading">
            <p class="eyebrow">Administrasi perpustakaan</p>
            <div class="page-title-row">
                <h1>Kelola Pengguna</h1>
            </div>
            <p class="page-description">Tambahkan akun perpustakaan dan lihat role serta status pengguna yang terdaftar.</p>
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

    <section class="category-panel" aria-labelledby="add-user-title">
        <div class="section-heading">
            <h2 id="add-user-title">Tambah Pengguna</h2>
            <p>Masukkan identitas dan role untuk membuat akun perpustakaan.</p>
        </div>
        <form class="category-form user-form" method="POST" aria-describedby="user-status-note">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="category-field">
                <label for="user-username">Username</label>
                <input id="user-username" type="text" name="username" autocomplete="username" value="<?= htmlspecialchars($userFormUsername, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="category-field">
                <label for="user-password">Password</label>
                <input id="user-password" type="password" name="password" autocomplete="new-password" required>
            </div>
            <div class="category-field">
                <label for="user-nama">Nama</label>
                <input id="user-nama" type="text" name="nama" autocomplete="name" value="<?= htmlspecialchars($userFormNama, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="category-field">
                <label for="user-role">Role</label>
                <select class="book-field-control" id="user-role" name="role">
                    <option value="anggota"<?= $userFormRole === 'anggota' ? ' selected' : '' ?>>Anggota</option>
                    <option value="petugas"<?= $userFormRole === 'petugas' ? ' selected' : '' ?>>Petugas</option>
                    <option value="admin"<?= $userFormRole === 'admin' ? ' selected' : '' ?>>Admin</option>
                </select>
            </div>
            <p class="user-form-note" id="user-status-note">Akun baru otomatis berstatus <strong>Aktif</strong>.</p>
            <button class="category-button category-button--primary user-form-submit" type="submit" name="tambah">Tambah Pengguna</button>
        </form>
    </section>

    <section class="category-panel" aria-labelledby="user-list-title">
        <div class="section-heading">
            <h2 id="user-list-title">Daftar Pengguna</h2>
            <p>Akun perpustakaan beserta role dan statusnya.</p>
        </div>

        <?php if (mysqli_num_rows($result) === 0): ?>
            <div class="category-empty-state">
                <h3>Belum ada pengguna</h3>
                <p>Pengguna yang ditambahkan akan muncul di sini.</p>
            </div>
        <?php else: ?>
            <div class="category-table-scroll" tabindex="0" role="region" aria-label="Daftar pengguna, dapat digulir secara horizontal">
                <table class="category-table user-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Username</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="category-number"><?= $no++ ?></td>
                            <td class="category-name"><?= htmlspecialchars($row['username']) ?></td>
                            <td class="user-name"><?= htmlspecialchars($row['nama']) ?></td>
                            <td><span class="status-badge user-role-badge"><?= htmlspecialchars(ucfirst($row['role'])) ?></span></td>
                            <td><span class="status-badge user-status-badge<?= $row['status'] === 'aktif' ? ' user-status-badge--active' : '' ?>"><?= htmlspecialchars(ucfirst($row['status'])) ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
