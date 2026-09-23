<?php
require 'config/koneksi.php';

$error = "";

if (isset($_POST['login'])) {
    $username =trim($_POST['username']);
    $password =trim($_POST['password']);

   $stmt = mysqli_prepare($koneksi, "SELECT id_user, username, password_hash, nama, role, status FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

     if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['status'] === 'nonaktif') {
            $error = "Akun Anda sudah dinonaktifkan. Hubungi admin.";
        } else {
            session_regenerate_id(true);
            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['role']     = $user['role'];

            header("Location: index.php");
            exit();
        }
    } else {
        $error = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Login - Bibliotech</title></head>
<body>
    <h2>Login Bibliotech</h2>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label>Username</label><br>
        <input type="text" name="username" required><br><br>
        <label>Password</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit" name="login">Login</button>
    </form>
</body>
</html>