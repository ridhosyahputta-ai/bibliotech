# Bibliotech 📚

Sistem manajemen perpustakaan multi-role dengan pelacakan eksemplar per-buku, dibangun dengan PHP native & MySQL.

**Bahasa:** [Bahasa Indonesia](#bahasa-indonesia) | [English](#english) | [Deutsch](#deutsch)

---

## Bahasa Indonesia

### Tentang

Bibliotech adalah sistem manajemen perpustakaan berbasis web dengan 3 role pengguna (Admin, Petugas, Anggota) dan sistem pelacakan eksemplar per-buku — setiap kopi fisik buku dilacak secara individual (tersedia, dipinjam, rusak, atau hilang), bukan sekadar angka stok.

### Fitur

- 🔐 **Autentikasi multi-role** dengan session PHP dan password ter-hash (`password_hash`/`password_verify`)
- 📚 **Katalog & Kategori Buku** — data induk buku terpisah dari eksemplar fisiknya
- 📖 **Peminjaman & Pengembalian** — setiap transaksi terikat ke eksemplar spesifik, menggunakan **database transaction** dan **row locking** (`FOR UPDATE`) untuk mencegah race condition
- 👥 **Kelola User** — admin dapat menambah akun dengan role berbeda
- 📊 **Dashboard** — statistik ringkasan (total buku, eksemplar tersedia, sedang dipinjam, keterlambatan)
- 📈 **Laporan** — buku terpopuler, anggota teraktif, daftar keterlambatan

### Keamanan

- Prepared statement (`mysqli`) di seluruh query — bebas SQL injection
- CSRF token di semua form yang mengubah data
- Password di-hash, tidak pernah disimpan sebagai teks biasa
- Validasi role di setiap halaman (bukan hanya status login)

### Tech Stack

PHP native, MySQL (InnoDB, utf8mb4), tanpa framework — dikerjakan manual untuk memahami setiap konsep dari dasar (bukan hasil generate otomatis).

### Struktur Database

5 tabel utama: `users`, `kategori`, `buku`, `eksemplar`, `peminjaman` — lihat folder `migrations/` untuk skema lengkap beserta foreign key dan aturan `ON DELETE`.

### Status

✅ Semua fitur inti (v1) selesai dan teruji. ⬜ Styling/UI sedang dalam pengerjaan (sengaja dikerjakan setelah seluruh fungsionalitas backend stabil).

### Instalasi

1. Clone repo ini ke folder `htdocs` XAMPP
2. Buat database `db_bibliotech` di phpMyAdmin
3. Jalankan `migrations/001_initial_schema.sql`
4. Sesuaikan kredensial di `config/koneksi.php` bila perlu
5. Akses lewat `localhost/bibliotech/login.php`

---

## English

### About

Bibliotech is a web-based library management system with 3 user roles (Admin, Staff, Member) and a per-copy tracking system — each physical copy of a book is tracked individually (available, borrowed, damaged, or lost), rather than a simple stock count.

### Features

- 🔐 **Multi-role authentication** with PHP sessions and hashed passwords (`password_hash`/`password_verify`)
- 📚 **Book Catalog & Categories** — book master data kept separate from its physical copies
- 📖 **Borrowing & Returns** — each transaction is tied to a specific copy, using **database transactions** and **row locking** (`FOR UPDATE`) to prevent race conditions
- 👥 **User Management** — admins can create accounts with different roles
- 📊 **Dashboard** — summary statistics (total books, available copies, currently borrowed, overdue count)
- 📈 **Reports** — most-borrowed books, most active members, overdue list

### Security

- Prepared statements (`mysqli`) throughout — no SQL injection surface
- CSRF tokens on every state-changing form
- Passwords are hashed, never stored in plain text
- Role checks on every page (not just login status)

### Tech Stack

Native PHP, MySQL (InnoDB, utf8mb4), no framework — built by hand to understand every concept from the ground up rather than relying on generated output.

### Database Structure

5 core tables: `users`, `kategori` (categories), `buku` (books), `eksemplar` (copies), `peminjaman` (loans) — see the `migrations/` folder for the full schema, foreign keys, and `ON DELETE` rules.

### Status

✅ All core (v1) features complete and tested. ⬜ Styling/UI work in progress (intentionally deferred until backend functionality was fully stable).

### Installation

1. Clone this repo into your XAMPP `htdocs` folder
2. Create a `db_bibliotech` database in phpMyAdmin
3. Run `migrations/001_initial_schema.sql`
4. Adjust credentials in `config/koneksi.php` if needed
5. Access via `localhost/bibliotech/login.php`

---

## Deutsch

### Über das Projekt

Bibliotech ist ein webbasiertes Bibliotheksverwaltungssystem mit drei Benutzerrollen (Admin, Mitarbeiter, Mitglied) und einer Exemplar-genauen Nachverfolgung — jedes physische Buchexemplar wird einzeln erfasst (verfügbar, ausgeliehen, beschädigt oder verloren), anstatt nur als einfache Bestandszahl.

### Funktionen

- 🔐 **Mehrrollen-Authentifizierung** mit PHP-Sessions und gehashten Passwörtern (`password_hash`/`password_verify`)
- 📚 **Buchkatalog & Kategorien** — Stammdaten der Bücher getrennt von den physischen Exemplaren
- 📖 **Ausleihe & Rückgabe** — jede Transaktion ist an ein bestimmtes Exemplar gebunden, mit **Datenbanktransaktionen** und **Row-Locking** (`FOR UPDATE`), um Race Conditions zu vermeiden
- 👥 **Benutzerverwaltung** — Admins können Konten mit unterschiedlichen Rollen anlegen
- 📊 **Dashboard** — zusammenfassende Statistiken (Gesamtzahl Bücher, verfügbare Exemplare, aktuell ausgeliehen, überfällig)
- 📈 **Berichte** — meistausgeliehene Bücher, aktivste Mitglieder, Liste überfälliger Ausleihen

### Sicherheit

- Durchgängig Prepared Statements (`mysqli`) — keine SQL-Injection-Angriffsfläche
- CSRF-Token bei jedem datenverändernden Formular
- Passwörter werden gehasht, niemals im Klartext gespeichert
- Rollenprüfung auf jeder Seite (nicht nur Login-Status)

### Tech-Stack

Natives PHP, MySQL (InnoDB, utf8mb4), ohne Framework — von Hand entwickelt, um jedes Konzept von Grund auf zu verstehen, statt auf generierten Code zu setzen.

### Datenbankstruktur

5 Kerntabellen: `users`, `kategori` (Kategorien), `buku` (Bücher), `eksemplar` (Exemplare), `peminjaman` (Ausleihen) — das vollständige Schema mit Fremdschlüsseln und `ON DELETE`-Regeln befindet sich im Ordner `migrations/`.

### Status

✅ Alle Kernfunktionen (v1) fertiggestellt und getestet. ⬜ Styling/UI in Arbeit (bewusst erst nach vollständiger Stabilität des Backends begonnen).

### Installation

1. Dieses Repo in den `htdocs`-Ordner von XAMPP klonen
2. Datenbank `db_bibliotech` in phpMyAdmin anlegen
3. `migrations/001_initial_schema.sql` ausführen
4. Zugangsdaten in `config/koneksi.php` bei Bedarf anpassen
5. Zugriff über `localhost/bibliotech/login.php`

---

**Dibuat oleh / Made by / Erstellt von:** Ridho Syach Putra — SMK TI Pembangunan Cimahi, RPL
