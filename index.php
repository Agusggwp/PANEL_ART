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
    <title>ARTDEVATA PANEL — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .orbitron { font-family: 'Orbitron', sans-serif; }
        .card-float { animation: float 8s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        .glow { box-shadow: 0 0 40px rgba(34, 197, 94, 0.3); }
        .btn-neon:hover { box-shadow: 0 0 25px rgba(34, 197, 94, 0.9); transform: translateY(-3px); }
    </style>
</head>
<body class="bg-gradient-to-br from-[#0f0f23] via-[#1a1a2e] to-[#16213e] min-h-screen flex flex-col lg:flex-row items-center justify-center relative overflow-hidden">

    <!-- Background Art -->
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute top-0 left-0 w-96 h-96 bg-green-500 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-600 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>

    <!-- Kiri: Branding (di mobile jadi atas) -->
    <div class="flex-1 flex items-center justify-center p-8 lg:p-16 text-white order-2 lg:order-1">
        <div class="max-w-lg text-center lg:text-left space-y-10">
            <div>
                <h1 class="text-5xl sm:text-7xl lg:text-8xl font-black orbitron leading-tight">
                    ART<span class="text-green-400">DEVATA</span>
                </h1>
                <p class="text-xl sm:text-3xl mt-3 text-green-300 font-light">FTP & Hosting Panel</p>
            </div>

            <p class="text-base sm:text-lg opacity-90 leading-relaxed">
                Kelola semua FTP, domain, dan file hosting kamu dalam satu dashboard canggih, cepat, dan indah.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20 card-float">
                    <span class="material-icons text-5xl text-green-400 mb-3 block">folder_shared</span>
                    <h3 class="text-lg font-bold">FTP Manager</h3>
                    <p class="text-sm opacity-80">Upload, download, edit file langsung dari browser</p>
                </div>
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20 card-float" style="animation-delay: 0.4s;">
                    <span class="material-icons text-5xl text-blue-400 mb-3 block">language</span>
                    <h3 class="text-lg font-bold">Domain Control</h3>
                    <p class="text-sm opacity-80">Atur root path & akses web instan</p>
                </div>
            </div>

            <div class="flex items-center justify-center lg:justify-start gap-4 mt-10">
                <div class="flex -space-x-3">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-10 h-10 rounded-full border-4 border-[#0f0f23]" alt="">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-10 h-10 rounded-full border-4 border-[#0f0f23]" alt="">
                    <img src="https://randomuser.me/api/portraits/men/86.jpg" class="w-10 h-10 rounded-full border-4 border-[#0f0f23]" alt="">
                </div>
                <p class="text-sm opacity-80">Digunakan oleh <strong class="text-green-400">500+ developer</strong> Indonesia</p>
            </div>
        </div>
    </div>

    <!-- Kanan: Form Login (di mobile jadi bawah) -->
    <div class="flex-1 flex items-center justify-center p-8 order-1 lg:order-2 w-full lg:max-w-md">
        <div class="bg-white/95 backdrop-blur-2xl rounded-3xl shadow-2xl p-10 w-full glow card-float">
            <div class="text-center mb-8">
                <h2 class="text-4xl font-bold text-gray-800 orbitron">Welcome Back</h2>
                <p class="text-gray-600 mt-2">Masuk ke ARTDEVATA PANEL</p>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" required
                           class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-500 focus:border-transparent transition text-lg"
                           placeholder="you@domain.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-5 py-4 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-500 focus:border-transparent transition text-lg"
                           placeholder="••••••••">
                </div>

                <button type="submit"
                        class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold text-xl py-5 rounded-xl hover:from-green-600 hover:to-emerald-700 transform hover:scale-105 transition-all duration-300 shadow-lg btn-neon">
                    <span class="flex items-center justify-center gap-3">
                        <span class="material-icons">login</span>
                        Masuk ke Panel
                    </span>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-gray-600">
                    Belum punya akun?
                    <a href="register.php" class="text-green-600 font-bold hover:underline">
                        Daftar Gratis Sekarang
                    </a>
                </p>
            </div>

            <div class="mt-10 text-center text-xs text-gray-500">
                © 2025 ARTDEVATA PANEL • Dibuat dengan <span class="text-red-500">♥</span> untuk developer Indonesia
            </div>
        </div>
    </div>

    <script>
        // Fade in saat scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('opacity-100', 'translate-y-0');
                    entry.target.classList.remove('opacity-0', 'translate-y-10');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.card-float').forEach(el => {
            el.classList.add('opacity-0', 'translate-y-10', 'transition-all', 'duration-1000');
            observer.observe(el);
        });
    </script>
</body>
</html>