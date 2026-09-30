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

- Prepared statements (`mysqli`) digunakan untuk query yang menerima input pengguna guna mengurangi risiko SQL injection
- CSRF token di semua form yang mengubah data
- Password di-hash, tidak pernah disimpan sebagai teks biasa
- Validasi role di setiap halaman (bukan hanya status login)
- Validasi input pada tahun terbit dan durasi peminjaman
- Database transaction dan row locking untuk menjaga konsistensi transaksi peminjaman

### Tech Stack

PHP native, MySQL (InnoDB, utf8mb4), HTML, dan CSS tanpa framework — dikerjakan manual untuk memahami setiap konsep dari dasar.

### Struktur Database

5 tabel utama: `users`, `kategori`, `buku`, `eksemplar`, `peminjaman` — lihat folder `migrations/` untuk skema lengkap beserta foreign key dan aturan `ON DELETE`.

### Status

🚧 **Bibliotech v1.5 sedang dalam pengembangan.**

#### v1.0 — Core System

- ✅ Autentikasi multi-role
- ✅ CRUD kategori, buku, dan eksemplar
- ✅ Manajemen user
- ✅ Peminjaman & pengembalian
- ✅ Dashboard statistik
- ✅ Laporan perpustakaan

#### v1.5A — Backend Hardening

- ✅ Pengamanan integritas status eksemplar
- ✅ Feedback transaksi pengembalian
- ✅ Penanganan DELETE dan database error yang lebih aman
- ✅ CSRF hardening
- ✅ Validasi tahun terbit
- ✅ Validasi durasi peminjaman 1–30 hari
- ✅ Perhitungan keterlambatan secara dinamis
- ✅ Peningkatan validasi dan keamanan form

#### v1.5B — UI/UX

- ✅ Design system dan shared layout
- ✅ Responsive sidebar/navigation
- ✅ Dashboard UI
- 🚧 Kategori CRUD UI
- ⬜ Buku & Eksemplar UI
- ⬜ User UI
- ⬜ Peminjaman UI
- ⬜ Laporan UI
- ⬜ Login UI

UI Bibliotech menggunakan desain **modern academic/library** dengan palet warm neutral, forest green, dan teal. Dibangun menggunakan CSS sendiri tanpa Bootstrap, Tailwind, atau framework UI.

### Instalasi

1. Clone repo ini ke folder `htdocs` XAMPP
2. Buat database `db_bibliotech` di phpMyAdmin
3. Jalankan `migrations/001_initial_schema.sql`
4. Sesuaikan kredensial di `config/koneksi.php` bila perlu
5. Akses lewat `localhost/bibliotech/login.php`

---

## English

### About

Bibliotech is a web-based library management system with 3 user roles (Admin, Staff, Member) and a per-copy tracking system — each physical copy of a book is tracked individually (available, borrowed, damaged, or lost), rather than using a simple stock count.

### Features

- 🔐 **Multi-role authentication** with PHP sessions and hashed passwords (`password_hash`/`password_verify`)
- 📚 **Book Catalog & Categories** — book master data is kept separate from physical copies
- 📖 **Borrowing & Returns** — each transaction is tied to a specific copy, using **database transactions** and **row locking** (`FOR UPDATE`) to prevent race conditions
- 👥 **User Management** — admins can create accounts with different roles
- 📊 **Dashboard** — summary statistics for books, available copies, active loans, and overdue loans
- 📈 **Reports** — most-borrowed books, most active members, and overdue loans

### Security

- Prepared statements (`mysqli`) are used for queries that accept user input to reduce SQL injection risk
- CSRF tokens on state-changing forms
- Passwords are hashed and never stored in plain text
- Role validation on protected pages
- Input validation for publication year and loan duration
- Database transactions and row locking for transaction consistency

### Tech Stack

Native PHP, MySQL (InnoDB, utf8mb4), HTML, and CSS without a framework — built manually to understand the underlying concepts from the ground up.

### Database Structure

5 core tables: `users`, `kategori` (categories), `buku` (books), `eksemplar` (copies), and `peminjaman` (loans) — see the `migrations/` folder for the full schema, foreign keys, and `ON DELETE` rules.

### Status

🚧 **Bibliotech v1.5 is currently in development.**

#### v1.0 — Core System

- ✅ Multi-role authentication
- ✅ Category, book, and copy management
- ✅ User management
- ✅ Borrowing & returns
- ✅ Statistics dashboard
- ✅ Library reports

#### v1.5A — Backend Hardening

- ✅ Copy status integrity protection
- ✅ Improved return transaction feedback
- ✅ Safer DELETE and database error handling
- ✅ CSRF hardening
- ✅ Publication year validation
- ✅ 1–30 day loan duration validation
- ✅ Dynamic overdue calculation
- ✅ Improved form validation and security

#### v1.5B — UI/UX

- ✅ Design system and shared layout
- ✅ Responsive sidebar/navigation
- ✅ Dashboard UI
- 🚧 Category CRUD UI
- ⬜ Book & Copy UI
- ⬜ User UI
- ⬜ Borrowing UI
- ⬜ Reports UI
- ⬜ Login UI

Bibliotech's interface follows a **modern academic/library** design direction using warm neutrals, forest green, and teal. The UI is built with custom CSS without Bootstrap, Tailwind, or other UI frameworks.

### Installation

1. Clone this repository into your XAMPP `htdocs` folder
2. Create a `db_bibliotech` database in phpMyAdmin
3. Run `migrations/001_initial_schema.sql`
4. Adjust credentials in `config/koneksi.php` if needed
5. Access via `localhost/bibliotech/login.php`

---

## Deutsch

### Über das Projekt

Bibliotech ist ein webbasiertes Bibliotheksverwaltungssystem mit drei Benutzerrollen (Admin, Mitarbeiter, Mitglied) und einer Exemplar-genauen Nachverfolgung — jedes physische Buchexemplar wird einzeln erfasst (verfügbar, ausgeliehen, beschädigt oder verloren), anstatt nur eine einfache Bestandszahl zu verwenden.

### Funktionen

- 🔐 **Mehrrollen-Authentifizierung** mit PHP-Sessions und gehashten Passwörtern (`password_hash`/`password_verify`)
- 📚 **Buchkatalog & Kategorien** — Stammdaten der Bücher sind von den physischen Exemplaren getrennt
- 📖 **Ausleihe & Rückgabe** — jede Transaktion ist an ein bestimmtes Exemplar gebunden und verwendet **Datenbanktransaktionen** sowie **Row-Locking** (`FOR UPDATE`)
- 👥 **Benutzerverwaltung** — Admins können Konten mit unterschiedlichen Rollen anlegen
- 📊 **Dashboard** — Statistiken zu Büchern, verfügbaren Exemplaren, aktiven und überfälligen Ausleihen
- 📈 **Berichte** — meistausgeliehene Bücher, aktivste Mitglieder und überfällige Ausleihen

### Sicherheit

- Prepared Statements (`mysqli`) werden für Abfragen mit Benutzereingaben verwendet, um das Risiko von SQL-Injection zu reduzieren
- CSRF-Token bei datenverändernden Formularen
- Passwörter werden gehasht und niemals im Klartext gespeichert
- Rollenprüfung auf geschützten Seiten
- Eingabevalidierung für Erscheinungsjahr und Ausleihdauer
- Datenbanktransaktionen und Row-Locking für konsistente Transaktionen

### Tech-Stack

Natives PHP, MySQL (InnoDB, utf8mb4), HTML und CSS ohne Framework — manuell entwickelt, um die zugrunde liegenden Konzepte von Grund auf zu verstehen.

### Datenbankstruktur

5 Kerntabellen: `users`, `kategori` (Kategorien), `buku` (Bücher), `eksemplar` (Exemplare) und `peminjaman` (Ausleihen) — das vollständige Schema mit Fremdschlüsseln und `ON DELETE`-Regeln befindet sich im Ordner `migrations/`.

### Status

🚧 **Bibliotech v1.5 befindet sich derzeit in Entwicklung.**

#### v1.0 — Kernsystem

- ✅ Mehrrollen-Authentifizierung
- ✅ Verwaltung von Kategorien, Büchern und Exemplaren
- ✅ Benutzerverwaltung
- ✅ Ausleihe & Rückgabe
- ✅ Statistik-Dashboard
- ✅ Bibliotheksberichte

#### v1.5A — Backend-Härtung

- ✅ Schutz der Integrität des Exemplarstatus
- ✅ Verbessertes Feedback bei Rückgaben
- ✅ Sicherere DELETE- und Datenbankfehlerbehandlung
- ✅ CSRF-Härtung
- ✅ Validierung des Erscheinungsjahres
- ✅ Validierung der Ausleihdauer von 1–30 Tagen
- ✅ Dynamische Berechnung überfälliger Ausleihen
- ✅ Verbesserte Formularvalidierung und Sicherheit

#### v1.5B — UI/UX

- ✅ Design-System und gemeinsames Layout
- ✅ Responsive Sidebar/Navigation
- ✅ Dashboard-UI
- 🚧 Kategorien-CRUD-UI
- ⬜ Bücher- & Exemplare-UI
- ⬜ Benutzer-UI
- ⬜ Ausleih-UI
- ⬜ Berichte-UI
- ⬜ Login-UI

Die Benutzeroberfläche von Bibliotech folgt einem **modernen akademischen Bibliotheksdesign** mit warmen neutralen Farben, Waldgrün und Petrol. Die Oberfläche wird mit eigenem CSS ohne Bootstrap, Tailwind oder andere UI-Frameworks entwickelt.

### Installation

1. Dieses Repository in den `htdocs`-Ordner von XAMPP klonen
2. Datenbank `db_bibliotech` in phpMyAdmin anlegen
3. `migrations/001_initial_schema.sql` ausführen
4. Zugangsdaten in `config/koneksi.php` bei Bedarf anpassen
5. Zugriff über `localhost/bibliotech/login.php`

---

**Dibuat oleh / Made by / Erstellt von:** Ridho Syach Putra — SMK TI Pembangunan Cimahi, RPL