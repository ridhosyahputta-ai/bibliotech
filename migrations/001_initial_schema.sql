-- Tabel users: menyimpan semua akun (admin, petugas, anggota) dalam 1 tabel
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'petugas', 'anggota') NOT NULL,
    status ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel kategori: kategori buku (Fiksi, Sains, dll)
CREATE TABLE kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel buku: data induk/master per judul buku
CREATE TABLE buku (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20),
    judul VARCHAR(150) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100),
    tahun_terbit INT,
    kategori_id INT NULL,
    deskripsi TEXT,
    cover VARCHAR(255),
    CONSTRAINT fk_buku_kategori
        FOREIGN KEY (kategori_id) REFERENCES kategori(id_kategori)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel eksemplar: tiap kopi fisik dari sebuah buku, dilacak per-unit
CREATE TABLE eksemplar (
    id_eksemplar INT AUTO_INCREMENT PRIMARY KEY,
    buku_id INT NOT NULL,
    kode_eksemplar VARCHAR(20) NOT NULL UNIQUE,
    status ENUM('tersedia', 'dipinjam', 'rusak', 'hilang') NOT NULL DEFAULT 'tersedia',
    CONSTRAINT fk_eksemplar_buku
        FOREIGN KEY (buku_id) REFERENCES buku(id_buku)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel peminjaman: transaksi pinjam & kembali, terikat ke eksemplar spesifik
CREATE TABLE peminjaman (
    id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
    eksemplar_id INT NOT NULL,
    anggota_id INT NOT NULL,
    petugas_id INT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    tanggal_dikembalikan DATE NULL,
    status ENUM('dipinjam', 'dikembalikan', 'terlambat') NOT NULL DEFAULT 'dipinjam',
    CONSTRAINT fk_peminjaman_eksemplar
        FOREIGN KEY (eksemplar_id) REFERENCES eksemplar(id_eksemplar)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_peminjaman_anggota
        FOREIGN KEY (anggota_id) REFERENCES users(id_user)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_peminjaman_petugas
        FOREIGN KEY (petugas_id) REFERENCES users(id_user)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;