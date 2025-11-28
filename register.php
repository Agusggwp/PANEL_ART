<?php
require_once 'functions.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        $error = "CSRF token tidak valid!";
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm'] ?? '';

        if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
            $error = "Semua field wajib diisi!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email tidak valid!";
        } elseif ($password !== $confirm) {
            $error = "Password tidak sama!";
        } elseif (strlen($password) < 6) {
            $error = "Password minimal 6 karakter!";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email sudah terdaftar!";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
                $stmt->execute([$name, $email, $hash]);
                $success = "Registrasi berhasil! Silakan <a href='index.php' class='font-bold underline text-green-600'>login sekarang</a>.";
            }
        }
    }
}

$_SESSION['csrf'] = bin2hex(random_bytes(32));
$csrf_token = $_SESSION['csrf'];
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — ARTDEVATA PANEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .orbitron { font-family: 'Orbitron', sans-serif; }
        .glow { box-shadow: 0 0 50px rgba(34, 197, 94, 0.35); }
        .btn-neon:hover { box-shadow: 0 0 30px rgba(34, 197, 94, 0.9); transform: translateY(-4px); }
    </style>
</head>
<body class="bg-gradient-to-br from-[#0f0f23] via-[#1a1a2e] to-[#16213e] min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Background Art -->
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute top-0 left-0 w-96 h-96 bg-green-500 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-600 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <!-- Card Utama — DIPASTIKAN TIDAK KELUAR DARI LAYAR -->
    <div class="relative z-10 w-full max-w-md">
        <div class="bg-white/95 backdrop-blur-2xl rounded-3xl shadow-2xl p-8 md:p-12 glow">

            <!-- Logo ARTDEVATA (ukuran dikontrol ketat) -->
            <div class="text-center mb-10">
                
                <p class="text-lg md:text-xl text-gray-600 mt-3 font-light">Buat Akun Baru</p>
            </div>

            <!-- Error & Success -->
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-5 py-4 rounded-xl mb-6 flex items-center gap-3 text-sm">
                    <span class="material-icons">error</span>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-5 py-4 rounded-xl mb-6 flex items-center gap-3 text-sm">
                    <span class="material-icons">check_circle</span>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <!-- Form Register -->
            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf" value="<?= $csrf_token ?>">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-500 focus:border-transparent transition text-lg"
                           placeholder="Nama kamu">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-500 focus:border-transparent transition text-lg"
                           placeholder="you@domain.com">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-500 focus:border-transparent transition text-lg"
                           placeholder="Min. 6 karakter">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
                    <input type="password" name="confirm" required minlength="6"
                           class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-500 focus:border-transparent transition text-lg"
                           placeholder="Ketik ulang password">
                </div>

                <button type="submit"
                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold text-xl py-5 rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-300 shadow-lg btn-neon flex items-center justify-center gap-3 mt-8">
                    <span class="material-icons text-2xl">person_add</span>
                    Daftar Sekarang
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-gray-600">
                    Sudah punya akun?
                    <a href="index.php" class="text-green-600 font-bold hover:underline">Login di sini</a>
                </p>
            </div>

            <div class="mt-10 text-center text-xs text-gray-500">
                © 2025 ARTDEVATA PANEL • Dibuat dengan <span class="text-red-500">♥</span> untuk developer Indonesia
            </div>
        </div>
    </div>
</body>
</html>