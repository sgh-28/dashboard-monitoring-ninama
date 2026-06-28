<?php
// test_koneksi.php

$host = '127.0.0.1';
$port = '5432';
$dbname = 'db_skripsi';
$user = 'postgres';
$pass = 'postgres'; // Sesuaikan dengan password PostgreSQL Anda

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";

try {
    $pdo = new PDO($dsn, $user, $pass);
    echo "✅ SUKSES! PHP bisa konek ke database 'db_skripsi' di PostgreSQL.";
    echo "<br>Server Info: " . $pdo->getAttribute(PDO::ATTR_SERVER_INFO);
} catch (PDOException $e) {
    echo "❌ GAGAL! PHP tidak bisa konek.";
    echo "<br>Error: " . $e->getMessage();
}
?>