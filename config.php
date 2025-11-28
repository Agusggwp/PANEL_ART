<?php
// config.php
$host = 'localhost';
$db   = 'u689664472_panel';
$user = 'u689664472_panel';
$pass = 'S!o6MSR3$z|c';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: ".$e->getMessage());
}

// HAPUS session_start() di sini
