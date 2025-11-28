<?php
require_once 'functions.php';
if (!isLoggedIn() || !isAdmin()) redirect('index.php');

global $pdo;
$action  = $_GET['action'] ?? '';
$id      = $_GET['id'] ?? null;
$errors  = [];
$success = '';

// === TAMBAH USER BARU ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add' && isAdmin()) {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'user';

    if (empty($name))     $errors[] = "Nama wajib diisi!";
    if (empty($email))    $errors[] = "Email wajib diisi!";
    if (empty($password)) $errors[] = "Password wajib diisi!";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email tidak valid!";

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email sudah terdaftar!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $hash, $role]);
            $success = "User berhasil ditambahkan!";
            header("Location: admin.php");
            exit;
        }
    }
}

// === UBAH ROLE USER ===
if ($action === 'toggle' && $id && isAdmin()) {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if ($user) {
        $new_role = $user['role'] === 'admin' ? 'user' : 'admin';
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$new_role, $id]);
        $success = "Role user berhasil diubah menjadi <strong>$new_role</strong>!";
        header("Location: admin.php");
        exit;
    }
}

// === HAPUS USER ===
if ($action === 'delete' && $id && isAdmin()) {
    // Jangan izinkan hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        $errors[] = "Kamu tidak bisa menghapus akun sendiri!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $success = "User berhasil dihapus!";
        header("Location: admin.php");
        exit;
    }
}

// === AMBIL SEMUA USER ===
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — ARTDEVATA PANEL</title>
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
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <span class="material-icons text-yellow-500 text-4xl">shield</span>
                    Admin Panel — User Management
                </h1>
                <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                        class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 transition">
                    <span class="material-icons">person_add</span> Tambah User
                </button>
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
                    <span class="material-icons">check_circle</span> <?= $success ?>
                </div>
            <?php endif; ?>

            <!-- Tabel Users -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border">
                <table class="min-w-full">
                    <thead class="bg-gradient-to-r from-purple-800 to-pink-800 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left">Nama</th>
                            <th class="px-6 py-4 text-left">Email</th>
                            <th class="px-6 py-4 text-left">Role</th>
                            <th class="px-6 py-4 text-left">Bergabung</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($u['name']) ?></td>
                                <td class="px-6 py-4 text-indigo-600 font-mono text-sm"><?= htmlspecialchars($u['email']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold <?= $u['role'] === 'admin' ? 'bg-purple-600 text-white' : 'bg-gray-300 text-gray-700' ?>">
                                        <?= ucfirst($u['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= date('d M Y', strtotime($u['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <a href="?action=toggle&id=<?= $u['id'] ?>" 
                                       class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition shadow">
                                        <span class="material-icons text-sm align-middle">swap_horiz</span>
                                        <?= $u['role'] === 'admin' ? 'Jadikan User' : 'Jadikan Admin' ?>
                                    </a>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="?action=delete&id=<?= $u['id'] ?>" 
                                           onclick="return confirm('Yakin hapus user <?= addslashes(htmlspecialchars($u['name'])) ?>?')"
                                           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition shadow">
                                            <span class="material-icons text-sm align-middle">delete</span> Hapus
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">Akun kamu</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal Tambah User -->
    <div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6">Tambah User Baru</h2>
            <form method="POST" action="?action=add">
                <div class="space-y-5">
                    <div>
                        <label class="block font-semibold mb-2">Nama Lengkap</label>
                        <input type="text" name="name" class="w-full border rounded-lg px-4 py-3" required>
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">Email</label>
                        <input type="email" name="email" class="w-full border rounded-lg px-4 py-3" required>
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">Password</label>
                        <input type="password" name="password" class="w-full border rounded-lg px-4 py-3" required>
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">Role</label>
                        <select name="role" class="w-full border rounded-lg px-4 py-3">
                            <option value="user">User Biasa</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-4 mt-8">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg w-full">
                        Tambah User
                    </button>
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-8 rounded-lg w-full">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>