<?php
require_once 'functions.php';
if (!isLoggedIn()) redirect('index.php');

global $pdo;
$action  = $_GET['action'] ?? '';
$id      = $_GET['id'] ?? null;
$errors  = [];
$success = '';

// --- PROSES TAMBAH / EDIT (Admin Only) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin()) {
    $account_name = trim($_POST['account_name'] ?? '');
    $host         = trim($_POST['host'] ?? '');
    $port         = (int)($_POST['port'] ?? 21);
    $username     = trim($_POST['username'] ?? '');
    $password     = trim($_POST['password'] ?? '');
    $user_id      = $_POST['user_id'] ?? null;

    // Validasi
    if (empty($account_name)) $errors[] = "Nama akun FTP wajib diisi!";
    if (empty($host))         $errors[] = "Host wajib diisi!";
    if (empty($username))     $errors[] = "Username wajib diisi!";
    if (empty($password))     $errors[] = "Password wajib diisi!";
    if (empty($user_id))      $errors[] = "Pilih user!";

    if (empty($errors)) {
        if ($action === 'edit' && $id) {
            $stmt = $pdo->prepare("UPDATE ftp_accounts SET account_name=?, host=?, port=?, username=?, password=?, user_id=? WHERE id=?");
            $stmt->execute([$account_name, $host, $port, $username, $password, $user_id, $id]);
            $success = "FTP Account berhasil diperbarui!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO ftp_accounts (user_id, account_name, host, port, username, password, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $account_name, $host, $port, $username, $password]);
            $success = "FTP Account berhasil ditambahkan!";
        }
        // Refresh halaman biar form ilang
        header("Location: ftp.php?success=1");
        exit;
    }
}

// --- HAPUS ---
if ($action === 'delete' && $id && isAdmin()) {
    $stmt = $pdo->prepare("DELETE FROM ftp_accounts WHERE id = ?");
    $stmt->execute([$id]);
    $success = "FTP Account berhasil dihapus!";
    header("Location: ftp.php");
    exit;
}

// --- EDIT: Ambil data ---
$edit_data = null;
if (isAdmin() && $action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM ftp_accounts WHERE id = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}

// --- Ambil daftar user ---
$users = isAdmin() ? $pdo->query("SELECT id, name, email FROM users ORDER BY name")->fetchAll() : [];

// --- Ambil daftar FTP ---
if (isAdmin()) {
    $stmt = $pdo->query("SELECT f.*, u.name as user_name FROM ftp_accounts f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM ftp_accounts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
}
$ftp_accounts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FTP Accounts — ARTDEVATA PANEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        @media (min-width: 768px) { body { padding-left: 16rem; } }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <?php include 'dashboard_sidebar.php'; ?>

    <main class="p-6 md:p-10">
        <div class="max-w-7xl mx-auto">

            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">FTP Accounts</h1>
                <?php if (isAdmin()): ?>
                    <a href="?action=add" class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg shadow-lg flex items-center gap-2">
                        <span class="material-icons">add</span> Tambah FTP Account
                    </a>
                <?php endif; ?>
            </div>

            <!-- Notifikasi -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded-lg mb-6">
                    <ul class="list-disc pl-5">
                        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 p-4 rounded-lg mb-6 flex items-center gap-2">
                    <span class="material-icons">check_circle</span> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- FORM TAMBAH / EDIT — INI YANG WAJIB ADA! -->
            <?php if (isAdmin() && in_array($action, ['add', 'edit'])): ?>
                <div class="bg-white rounded-2xl shadow-xl p-8 mb-10 border">
                    <h2 class="text-2xl font-bold mb-6"><?= $action === 'edit' ? 'Edit' : 'Tambah' ?> FTP Account</h2>
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-semibold mb-2">User</label>
                            <select name="user_id" class="w-full border rounded-lg px-4 py-3" required>
                                <option value="">-- Pilih User --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($edit_data && $edit_data['user_id'] == $u['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold mb-2">Nama Akun FTP</label>
                            <input type="text" name="account_name" class="w-full border rounded-lg px-4 py-3" 
                                   value="<?= htmlspecialchars($edit_data['account_name'] ?? '') ?>" placeholder="ex: Hosting Utama" required>
                        </div>

                        <div><label class="block font-semibold mb-2">Host</label>
                            <input type="text" name="host" class="w-full border rounded-lg px-4 py-3" 
                                   value="<?= htmlspecialchars($edit_data['host'] ?? '') ?>" required>
                        </div>

                        <div><label class="block font-semibold mb-2">Port</label>
                            <input type="number" name="port" class="w-full border rounded-lg px-4 py-3" 
                                   value="<?= $edit_data['port'] ?? 21 ?>" required>
                        </div>

                        <div><label class="block font-semibold mb-2">Username</label>
                            <input type="text" name="username" class="w-full border rounded-lg px-4 py-3" 
                                   value="<?= htmlspecialchars($edit_data['username'] ?? '') ?>" required>
                        </div>

                        <div><label class="block font-semibold mb-2">Password</label>
                            <input type="text" name="password" class="w-full border rounded-lg px-4 py-3 font-mono" 
                                   value="<?= htmlspecialchars($edit_data['password'] ?? '') ?>" required>
                        </div>

                        <div class="md:col-span-2 flex gap-4">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg">
                                <?= $action === 'edit' ? 'Update' : 'Tambah' ?> Account
                            </button>
                            <a href="ftp.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-8 rounded-lg">Batal</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Tabel FTP Accounts -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">
                        <tr>
                            <?php if (isAdmin()): ?><th class="px-6 py-4 text-left">User</th><?php endif; ?>
                            <th class="px-6 py-4 text-left">Nama Akun</th>
                            <th class="px-6 py-4 text-left">Host</th>
                            <th class="px-6 py-4 text-left">Port</th>
                            <th class="px-6 py-4 text-left">Username</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($ftp_accounts)): ?>
                            <tr><td colspan="10" class="text-center py-16 text-gray-400">Belum ada data</td></tr>
                        <?php else: foreach ($ftp_accounts as $ftp): ?>
                            <tr class="hover:bg-gray-50">
                                <?php if (isAdmin()): ?>
                                    <td class="px-6 py-4 text-sm"><?= htmlspecialchars($ftp['user_name'] ?? '-') ?></td>
                                <?php endif; ?>
                                <td class="px-6 py-4 font-semibold text-green-600"><?= htmlspecialchars($ftp['account_name']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($ftp['host']) ?></td>
                                <td class="px-6 py-4 text-center"><?= $ftp['port'] ?></td>
                                <td class="px-6 py-4 font-mono text-sm"><?= htmlspecialchars($ftp['username']) ?></td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <?php if (isAdmin()): ?>
                                        <a href="?action=edit&id=<?= $ftp['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">Edit</a>
                                        <a href="?action=delete&id=<?= $ftp['id'] ?>" onclick="return confirm('Hapus <?= addslashes($ftp['account_name']) ?>?')" 
                                           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">Hapus</a>
                                    <?php endif; ?>
                                    <a href="ftp_manager.php?ftp_id=<?= $ftp['id'] ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm inline-flex items-center gap-1">
                                        <span class="material-icons text-lg">folder_open</span> Manager
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</body>
</html>