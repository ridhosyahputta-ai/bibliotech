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

// Proses tambah user baru
if (isset($_POST['tambah'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
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
<!DOCTYPE html>
<html>
<head><title>Kelola User - Bibliotech</title></head>
<body>
    <a href="../../index.php">&larr; Dashboard</a>
    <h2>Kelola User</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($sukses): ?>
        <p style="color:green;"><?= htmlspecialchars($sukses) ?></p>
    <?php endif; ?>

    <h3>Tambah User</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label>Username</label><br>
        <input type="text" name="username" required><br><br>
        <label>Password</label><br>
        <input type="text" name="password" required><br><br>
        <label>Nama</label><br>
        <input type="text" name="nama" required><br><br>
        <label>Role</label><br>
        <select name="role">
            <option value="anggota">Anggota</option>
            <option value="petugas">Petugas</option>
            <option value="admin">Admin</option>
        </select><br><br>
        <button type="submit" name="tambah">Tambah User</button>
    </form>

    <h3>Daftar User</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>No</th>
            <th>Username</th>
            <th>Nama</th>
            <th>Role</th>
            <th>Status</th>
        </tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>