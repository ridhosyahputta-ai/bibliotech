<?php
require '../../config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

if (!in_array($_SESSION['role'], ['admin', 'petugas'])) {
    die("Akses ditolak.");
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = "";
$sukses = "";

// Proses tambah peminjaman baru
if (isset($_POST['pinjam'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Token keamanan tidak valid.";
    } else {
        $eksemplar_id = (int) $_POST['eksemplar_id'];
        $anggota_id   = (int) $_POST['anggota_id'];
        $lama_pinjam  = (int) $_POST['lama_pinjam'];
        $petugas_id   = $_SESSION['id_user'];

        if ($eksemplar_id <= 0 || $anggota_id <= 0 || $lama_pinjam <= 0) {
            $error = "Semua field wajib diisi dengan benar.";
        } else {
            // Mulai transaction
            mysqli_begin_transaction($koneksi);

            try {
                // Kunci baris eksemplar ini, cek statusnya
                $stmt = mysqli_prepare($koneksi, "SELECT status FROM eksemplar WHERE id_eksemplar = ? FOR UPDATE");
                mysqli_stmt_bind_param($stmt, "i", $eksemplar_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $eksemplar = mysqli_fetch_assoc($result);

                if (!$eksemplar) {
                    throw new Exception("Eksemplar tidak ditemukan.");
                }
                if ($eksemplar['status'] !== 'tersedia') {
                    throw new Exception("Eksemplar ini sudah tidak tersedia.");
                }

                // Insert peminjaman
                $tanggal_pinjam = date('Y-m-d');
                $tanggal_jatuh_tempo = date('Y-m-d', strtotime("+$lama_pinjam days"));

                $stmt = mysqli_prepare($koneksi, "INSERT INTO peminjaman (eksemplar_id, anggota_id, petugas_id, tanggal_pinjam, tanggal_jatuh_tempo, status) VALUES (?, ?, ?, ?, ?, 'dipinjam')");
                mysqli_stmt_bind_param($stmt, "iiiss", $eksemplar_id, $anggota_id, $petugas_id, $tanggal_pinjam, $tanggal_jatuh_tempo);
                mysqli_stmt_execute($stmt);

                // Update status eksemplar
                $stmt = mysqli_prepare($koneksi, "UPDATE eksemplar SET status = 'dipinjam' WHERE id_eksemplar = ?");
                mysqli_stmt_bind_param($stmt, "i", $eksemplar_id);
                mysqli_stmt_execute($stmt);

                // Semua berhasil, simpan permanen
                mysqli_commit($koneksi);
                $sukses = "Peminjaman berhasil dicatat.";

            } catch (Exception $e) {
                // Ada yang gagal, batalkan semua perubahan
                mysqli_rollback($koneksi);
                $error = $e->getMessage();
            }
        }
    }
}

// Ambil anggota (buat dropdown)
$anggota_result = mysqli_query($koneksi, "SELECT id_user, nama FROM users WHERE role = 'anggota' AND status = 'aktif' ORDER BY nama ASC");

// Ambil eksemplar yang statusnya tersedia (buat dropdown)
$eksemplar_result = mysqli_query($koneksi, "
    SELECT eksemplar.id_eksemplar, eksemplar.kode_eksemplar, buku.judul
    FROM eksemplar
    JOIN buku ON eksemplar.buku_id = buku.id_buku
    WHERE eksemplar.status = 'tersedia'
    ORDER BY buku.judul ASC
");

// Ambil semua riwayat peminjaman
$peminjaman_result = mysqli_query($koneksi, "
    SELECT peminjaman.id_peminjaman, peminjaman.tanggal_pinjam, peminjaman.tanggal_jatuh_tempo, 
           peminjaman.tanggal_dikembalikan, peminjaman.status,
           buku.judul, eksemplar.kode_eksemplar, users.nama AS nama_anggota
    FROM peminjaman
    JOIN eksemplar ON peminjaman.eksemplar_id = eksemplar.id_eksemplar
    JOIN buku ON eksemplar.buku_id = buku.id_buku
    JOIN users ON peminjaman.anggota_id = users.id_user
    ORDER BY peminjaman.tanggal_pinjam DESC
");
?>
<!DOCTYPE html>
<html>
<head><title>Peminjaman - Bibliotech</title></head>
<body>
    <a href="../../index.php">&larr; Dashboard</a>
    <h2>Peminjaman Buku</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($sukses): ?>
        <p style="color:green;"><?= htmlspecialchars($sukses) ?></p>
    <?php endif; ?>

    <h3>Pinjamkan Buku</h3>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label>Anggota</label><br>
        <select name="anggota_id" required>
            <option value="">-- Pilih Anggota --</option>
            <?php while ($a = mysqli_fetch_assoc($anggota_result)): ?>
                <option value="<?= $a['id_user'] ?>"><?= htmlspecialchars($a['nama']) ?></option>
            <?php endwhile; ?>
        </select><br><br>
        <label>Eksemplar</label><br>
        <select name="eksemplar_id" required>
            <option value="">-- Pilih Eksemplar --</option>
            <?php while ($e = mysqli_fetch_assoc($eksemplar_result)): ?>
                <option value="<?= $e['id_eksemplar'] ?>"><?= htmlspecialchars($e['judul']) ?> (<?= htmlspecialchars($e['kode_eksemplar']) ?>)</option>
            <?php endwhile; ?>
        </select><br><br>
        <label>Lama Pinjam (hari)</label><br>
        <input type="number" name="lama_pinjam" value="7" required><br><br>
        <button type="submit" name="pinjam">Pinjamkan</button>
    </form>

    <h3>Riwayat Peminjaman</h3>
    <table border="1" cellpadding="8">
        <tr>
            <th>No</th>
            <th>Buku</th>
            <th>Anggota</th>
            <th>Tgl Pinjam</th>
            <th>Jatuh Tempo</th>
            <th>Tgl Kembali</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($peminjaman_result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['judul']) ?> (<?= htmlspecialchars($row['kode_eksemplar']) ?>)</td>
            <td><?= htmlspecialchars($row['nama_anggota']) ?></td>
            <td><?= htmlspecialchars($row['tanggal_pinjam']) ?></td>
            <td><?= htmlspecialchars($row['tanggal_jatuh_tempo']) ?></td>
            <td><?= htmlspecialchars($row['tanggal_dikembalikan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td>
                <?php if ($row['status'] === 'dipinjam'): ?>
                    <form method="POST" action="kembalikan.php" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="id_peminjaman" value="<?= $row['id_peminjaman'] ?>">
                        <button type="submit">Kembalikan</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>