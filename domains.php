<?php
require_once 'functions.php';
if (!isLoggedIn()) redirect('index.php');

global $pdo;
$action  = $_GET['action'] ?? '';
$id      = $_GET['id'] ?? null;
$errors  = [];
$success = '';

// === TAMBAH / EDIT DOMAIN (Admin Only) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAdmin()) {
    $domain_name = trim($_POST['domain_name'] ?? '');
    $root_path   = trim($_POST['root_path'] ?? '');
    $user_id     = $_POST['user_id'] ?? null;

    if (empty($domain_name)) $errors[] = "Domain wajib diisi!";
    if (empty($root_path))   $errors[] = "Root path wajib diisi!";
    if (empty($user_id))     $errors[] = "Pilih user!";

    if (empty($errors)) {
        try {
            if ($action === 'edit' && $id) {
                $stmt = $pdo->prepare("UPDATE domains SET domain_name=?, root_path=?, user_id=? WHERE id=?");
                $stmt->execute([$domain_name, $root_path, $user_id, $id]);
                $success = "Domain berhasil diperbarui!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO domains (user_id, domain_name, root_path) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $domain_name, $root_path]);
                $success = "Domain berhasil ditambahkan!";
            }
            header("Location: domains.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $errors[] = "Domain '$domain_name' sudah terdaftar!";
            } else {
                $errors[] = "Error: " . $e->getMessage();
            }
        }
    }
}

// === HAPUS DOMAIN ===
if ($action === 'delete' && $id && isAdmin()) {
    $stmt = $pdo->prepare("DELETE FROM domains WHERE id = ?");
    $stmt->execute([$id]);
    $success = "Domain berhasil dihapus!";
    header("Location: domains.php");
    exit;
}

// === EDIT: Ambil data ===
$edit_data = null;
if (isAdmin() && $action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM domains WHERE id = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}

// === Daftar user untuk admin ===
$users = isAdmin() ? $pdo->query("SELECT id, name, email FROM users ORDER BY name")->fetchAll() : [];

// === Ambil semua domain ===
if (isAdmin()) {
    $stmt = $pdo->query("SELECT d.*, u.name as user_name FROM domains d LEFT JOIN users u ON d.user_id = u.id ORDER BY d.created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM domains WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
}
$domains = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domains — ARTDEVATA PANEL</title>
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
                <h1 class="text-3xl font-bold text-gray-800">Domains</h1>
                <?php if (isAdmin()): ?>
                    <a href="?action=add" class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 transition">
                        <span class="material-icons">add</span> Tambah Domain
                    </a>
                <?php endif; ?>
            </div>

            <!-- Notifikasi -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded-xl mb-6">
                    <ul class="list-disc pl-6 space-y-1">
                        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 p-4 rounded-xl mb-6 flex items-center gap-2">
                    <span class="material-icons">check_circle</span> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- Form Tambah/Edit -->
            <?php if (isAdmin() && in_array($action, ['add', 'edit'])): ?>
                <div class="bg-white rounded-2xl shadow-xl p-8 mb-10 border">
                    <h2 class="text-2xl font-bold mb-6"><?= $action === 'edit' ? 'Edit' : 'Tambah' ?> Domain</h2>
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
                            <label class="block font-semibold mb-2">Domain</label>
                            <input type="text" name="domain_name" class="w-full border rounded-lg px-4 py-3" 
                                   value="<?= htmlspecialchars($edit_data['domain_name'] ?? '') ?>" 
                                   placeholder="contoh.com" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block font-semibold mb-2">Root Path (folder di server)</label>
                            <input type="text" name="root_path" class="w-full border rounded-lg px-4 py-3 font-mono text-sm" 
                                   value="<?= htmlspecialchars($edit_data['root_path'] ?? '') ?>" 
                                   placeholder="/home/username/public_html/contoh.com" required>
                        </div>

                        <div class="md:col-span-2 flex gap-4">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg">
                                <?= $action === 'edit' ? 'Update' : 'Tambah' ?> Domain
                            </button>
                            <a href="domains.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-8 rounded-lg">Batal</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Tabel Domains -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border">
                <table class="min-w-full">
                    <thead class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">
                        <tr>
                            <?php if (isAdmin()): ?><th class="px-6 py-4 text-left">User</th><?php endif; ?>
                            <th class="px-6 py-4 text-left">Domain</th>
                            <th class="px-6 py-4 text-left">Root Path</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($domains)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-16 text-gray-400">
                                    <span class="material-icons text-6xl opacity-20 block mb-4">language</span>
                                    Belum ada domain
                                </td>
                            </tr>
                        <?php else: foreach ($domains as $d): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <?php if (isAdmin()): ?>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($d['user_name'] ?? '-') ?></td>
                                <?php endif; ?>
                                <td class="px-6 py-4 font-semibold text-indigo-600">
                                    <?= htmlspecialchars($d['domain_name']) ?>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-600 break-all">
                                    <?= htmlspecialchars($d['root_path']) ?>
                                </td>
                                <td class="px-6 py-4 text-center space-x-3">
                                    <a href="http://<?= htmlspecialchars($d['domain_name']) ?>" target="_blank" 
                                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm inline-flex items-center gap-1">
                                        <span class="material-icons text-lg">open_in_new</span> Lihat Web
                                    </a>
                                    <?php if (isAdmin()): ?>
                                        <a href="?action=edit&id=<?= $d['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">Edit</a>
                                        <a href="?action=delete&id=<?= $d['id'] ?>" 
                                           onclick="return confirm('Hapus domain <?= htmlspecialchars($d['domain_name']) ?>?')"
                                           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">Hapus</a>
                                    <?php endif; ?>
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