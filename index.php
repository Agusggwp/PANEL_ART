<?php
require_once 'functions.php';
if (isLoggedIn()) redirect('dashboard.php');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = "Email dan password wajib diisi!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            logActivity($user['id'], 'Login');
            redirect('dashboard.php');
        } else {
            $errors[] = "Email atau password salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARTDEVATA — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f241d 100%); }
        .btn-tosca { background: linear-gradient(to right, #10b981, #34d399); }
        .btn-tosca:hover { background: linear-gradient(to right, #059669, #10b981); transform: translateY(-2px); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4); }
        .glow-card { box-shadow: 0 0 50px rgba(16, 185, 129, 0.15); }
        .text-gradient { background: linear-gradient(to right, #10b981, #34d399); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center relative overflow-hidden text-white">

    <!-- Background Blur Circles -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-600/30 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 w-full max-w-6xl mx-auto px-6 py-12 flex flex-col lg:flex-row items-center justify-between gap-12">

        <!-- Kiri: Hero Section (sama persis seperti website) -->
        <div class="text-center lg:text-left space-y-8 max-w-2xl">
            <!-- Logo & Nav Mini -->
            <div class="flex items-center justify-center lg:justify-start gap-4 mb-10">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-lg flex items-center justify-center text-2xl font-bold">
                        <i class="fas fa-play text-white"></i>
                    </div>
                    <span class="text-3xl font-bold">ArtDevata</span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="inline-flex items-center gap-3 px-5 py-2 bg-emerald-500/20 rounded-full text-emerald-400 text-sm font-medium border border-emerald-500/30">
                    <i class="fas fa-sparkles"></i>
                    Solusi IT Premium
                </div>

                <h1 class="text-5xl lg:text-7xl font-black leading-tight">
                    Wujudkan Bisnis Digital<br>
                    <span class="text-gradient">Anda Bersama Kami</span>
                </h1>

                <p class="text-3xl lg:text-5xl font-bold text-emerald-400">Website Development</p>

                <p class="text-lg text-gray-300 max-w-xl">
                    Kami menyediakan solusi IT terpadu dari pengembangan website, hosting & domain,
                    instalasi CCTV, hingga IT support untuk mendukung transformasi digital bisnis Anda.
                </p>

                <!-- Tombol -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-6">
                    <a href="#layanan" class="px-8 py-4 bg-white/10 backdrop-blur-md border border-white/20 rounded-full hover:bg-white/20 transition">
                        <i class="fas fa-list-ul mr-2"></i> Lihat Layanan
                    </a>
                    <a href="#kontak" class="px-8 py-4 btn-tosca rounded-full font-semibold text-white transition flex items-center justify-center gap-2">
                        Mulai Proyek <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <!-- Stats -->
                <div class="flex justify-center lg:justify-start gap-10 pt-10">
                    <div class="text-center">
                        <div class="text-4xl font-black text-emerald-400">24/7</div>
                        <div class="text-sm text-gray-400">Dukungan Tersedia</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-black text-emerald-400">99.9%</div>
                        <div class="text-sm text-gray-400">Jaminan Uptime</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanan: Form Login -->
        <div class="w-full max-w-md">
            <div class="bg-slate-900/70 backdrop-blur-xl rounded-3xl p-10 glow-card border border-white/10">
                <div class="text-center mb-8">
                    <h2 class="text-4xl font-bold">Welcome Back</h2>
                    <p class="text-gray-400 mt-2">Masuk ke dashboard ArtDevata Panel</p>
                </div>

                <!-- Error -->
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-500/20 border border-red-500/50 text-red-300 px-4 py-3 rounded-xl mb-6 text-sm">
                        <?php foreach ($errors as $e): ?>
                            <p><?= htmlspecialchars($e) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" name="email" required class="w-full px-5 py-4 bg-white/10 border border-white/20 rounded-xl focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/30 transition text-white placeholder-gray-500"
                               placeholder="you@domain.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Password</label>
                        <input type="password" name="password" required class="w-full px-5 py-4 bg-white/10 border border-white/20 rounded-xl focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-500/30 transition text-white placeholder-gray-500"
                               placeholder="••••••••••••">
                    </div>

                    <button type="submit" class="w-full py-5 btn-tosca rounded-xl font-bold text-lg transition flex items-center justify-center gap-3">
                        <i class="fas fa-sign-in-alt"></i>
                        Masuk ke Panel
                    </button>
                </form>

                <div class="mt-8 text-center text-sm text-gray-400">
                    Belum punya akun? 
                    <a href="register.php" class="text-emerald-400 font-semibold hover:underline">Daftar Gratis</a>
                </div>

                <div class="mt-10 text-center text-xs text-gray-500">
                    © 2025 ArtDevata • Solusi IT Premium Indonesia
                </div>
            </div>
        </div>
    </div>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/6281234567890" target="_blank" class="fixed bottom-6 right-6 w-14 h-14 bg-emerald-500 rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition z-50">
        <i class="fab fa-whatsapp text-3xl text-white"></i>
    </a>
</body>
</html>