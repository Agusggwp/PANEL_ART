<?php
require_once 'functions.php';
if(!isLoggedIn()) redirect('index.php');

$user_id = $_SESSION['user_id'];
$ftp_id = $_GET['ftp_id'] ?? null;
$file = $_GET['file'] ?? null;

if(!$ftp_id || !$file) die("File tidak ditemukan");

// --- Ambil akun FTP user ---
$stmt = $pdo->prepare("SELECT * FROM ftp_accounts WHERE id=? AND user_id=? LIMIT 1");
$stmt->execute([$ftp_id, $user_id]);
$ftp_account = $stmt->fetch();
if(!$ftp_account) die("Tidak ada akun FTP atau akses ditolak!");

// --- Koneksi FTP ---
$conn = ftp_connect($ftp_account['host'], $ftp_account['port']);
if(!$conn) die("Gagal koneksi FTP!");
if(!ftp_login($conn, $ftp_account['username'], $ftp_account['password'])){
    die("Login FTP gagal!");
}
ftp_pasv($conn, true);

// --- Unduh file ke temp ---
$tmp_file = tempnam(sys_get_temp_dir(), 'ftp_');
if(!ftp_get($conn, $tmp_file, $file, FTP_BINARY)){
    die("Gagal download file!");
}
logActivity($user_id, "Download file $file");

// --- Kirim ke browser ---
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($file).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($tmp_file));
readfile($tmp_file);
unlink($tmp_file);
ftp_close($conn);
exit;
