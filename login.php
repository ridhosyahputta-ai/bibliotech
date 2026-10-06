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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bibliotech</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-panel" aria-labelledby="auth-title">
            <div class="auth-brand" aria-label="Bibliotech, Perpustakaan">
                <span class="auth-brand-mark" aria-hidden="true">B</span>
                <span class="auth-brand-copy">
                    <span class="auth-brand-name">Bibliotech</span>
                    <span class="auth-brand-caption">Perpustakaan</span>
                </span>
            </div>

            <header class="auth-heading">
                <h1 id="auth-title">Masuk ke Bibliotech</h1>
                <p>Gunakan akun perpustakaan Anda untuk melanjutkan.</p>
            </header>

            <?php if ($error): ?>
                <div class="auth-error" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST">
                <div class="auth-field">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" autocomplete="username" required>
                </div>
                <div class="auth-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" required>
                </div>
                <button class="auth-submit" type="submit" name="login">Masuk</button>
            </form>
        </section>

        <footer class="auth-footer">Portal internal perpustakaan</footer>
    </main>
</body>
</html>

