<?php
require 'config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Dashboard - Bibliotech</title></head>
<body>
    <h2>Dashboard Bibliotech</h2>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>!</p>
    <p>Role Anda: <strong><?= htmlspecialchars($_SESSION['role']) ?></strong></p>
    <a href="logout.php">Logout</a>
</body>
</html>