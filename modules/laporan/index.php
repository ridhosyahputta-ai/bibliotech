<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if (!in_array($_SESSION['role'], ['admin', 'petugas'])) {
    die("Akses ditolak.");
}

// Laporan 1: Buku paling sering dipinjam
$buku_terpopuler = mysqli_query($koneksi, "
    SELECT buku.judul, COUNT(*) AS total_dipinjam
    FROM peminjaman
    JOIN eksemplar ON peminjaman.eksemplar_id = eksemplar.id_eksemplar
    JOIN buku ON eksemplar.buku_id = buku.id_buku
    GROUP BY buku.id_buku
    ORDER BY total_dipinjam DESC
    LIMIT 10
");

// Laporan 2: Anggota paling aktif
$anggota_aktif = mysqli_query($koneksi, "
    SELECT users.nama, COUNT(*) AS total_pinjam
    FROM peminjaman
    JOIN users ON peminjaman.anggota_id = users.id_user
    GROUP BY users.id_user
    ORDER BY total_pinjam DESC
    LIMIT 10
");

// Laporan 3: Daftar peminjaman terlambat
$terlambat = mysqli_query($koneksi, "
    SELECT buku.judul, eksemplar.kode_eksemplar, users.nama AS nama_anggota, 
           peminjaman.tanggal_jatuh_tempo,
           DATEDIFF(CURDATE(), peminjaman.tanggal_jatuh_tempo) AS hari_terlambat
    FROM peminjaman
    JOIN eksemplar ON peminjaman.eksemplar_id = eksemplar.id_eksemplar
    JOIN buku ON eksemplar.buku_id = buku.id_buku
    JOIN users ON peminjaman.anggota_id = users.id_user
    WHERE peminjaman.status = 'dipinjam' AND peminjaman.tanggal_jatuh_tempo < CURDATE()
    ORDER BY hari_terlambat DESC
");
?>
<!DOCTYPE html>
<html>
<head><title>Laporan - Bibliotech</title></head>
<body>
    <a href="../../index.php">&larr; Dashboard</a>
    <h2>Laporan</h2>

    <h3>Buku Paling Sering Dipinjam</h3>
    <table border="1" cellpadding="8">
        <tr><th>No</th><th>Judul</th><th>Total Dipinjam</th></tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($buku_terpopuler)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['judul']) ?></td>
            <td><?= $row['total_dipinjam'] ?></td>
        </tr>
        <?php endwhile; ?>
        <?php if ($no === 1): ?>
        <tr><td colspan="3">Belum ada data peminjaman.</td></tr>
        <?php endif; ?>
    </table>

    <h3>Anggota Paling Aktif</h3>
    <table border="1" cellpadding="8">
        <tr><th>No</th><th>Nama</th><th>Total Peminjaman</th></tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($anggota_aktif)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= $row['total_pinjam'] ?></td>
        </tr>
        <?php endwhile; ?>
        <?php if ($no === 1): ?>
        <tr><td colspan="3">Belum ada data peminjaman.</td></tr>
        <?php endif; ?>
    </table>

    <h3>Peminjaman Terlambat</h3>
    <table border="1" cellpadding="8">
        <tr><th>No</th><th>Buku</th><th>Anggota</th><th>Jatuh Tempo</th><th>Terlambat</th></tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($terlambat)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['judul']) ?> (<?= htmlspecialchars($row['kode_eksemplar']) ?>)</td>
            <td><?= htmlspecialchars($row['nama_anggota']) ?></td>
            <td><?= htmlspecialchars($row['tanggal_jatuh_tempo']) ?></td>
            <td style="color:red;"><?= $row['hari_terlambat'] ?> hari</td>
        </tr>
        <?php endwhile; ?>
        <?php if ($no === 1): ?>
        <tr><td colspan="5">Tidak ada peminjaman yang terlambat.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>