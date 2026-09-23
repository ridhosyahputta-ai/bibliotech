<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_bibliotech";

$koneksi = mysqli_connect($host,$user,$pass,$db);

if (!$koneksi) {
    die("Koneksi ke database gagal: ". mysqli_connect_error());
}

mysqli_set_charset($koneksi,"utf8mb4");

session_start();
?>