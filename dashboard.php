<?php
require_once 'functions.php';
if (!isLoggedIn()) redirect('index.php');

// Ambil data user
$user_id = $_SESSION['user_id'];

// Cek apakah user punya akun hosting/FTP di tabel hosting_accounts
$stmt = $pdo->prepare("SELECT COUNT(*) FROM hosting_accounts WHERE user_id = ?");
$stmt->execute([$user_id]);
$has_ftp = $stmt->fetchColumn() > 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — ARTDEVATA PANEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media (min-width: 768px) {
            .main-content { margin-left: 16rem; }
        }
        .gradient-bg { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f241d 100%); }
        .btn-tosca { background: linear-gradient(to right, #10b981, #34d399); }
        .btn-tosca:hover { background: linear-gradient(to right, #059669, #10b981); box-shadow: 0 15px 35px rgba(16,185,129,0.4); transform: translateY(-3px); }
        .glass-card { background: rgba(15,23,42,0.75); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Sidebar (tetap sama, hanya ganti warna sedikit biar lebih premium) -->
    <div class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-gray-900 to-gray-800 text-white flex flex-col shadow-2xl z-50">
        <div class="p-6 text-2xl font-extrabold tracking-wide text-emerald-400 border-b border-gray-700">
            ARTDEVATA PANEL
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <?php $current = basename($_SERVER['PHP_SELF']); ?>

            <a href="dashboard.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all group <?= $current=='dashboard.php'?'bg-gray-700':'' ?>">
                <i class="fas fa-tachometer-alt mr-3 text-emerald-400"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            <a href="ftp.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all group <?= $current=='ftp.php'?'bg-gray-700':'' ?>">
                <i class="fas fa-server mr-3 text-emerald-400"></i>
                <span class="font-medium">Hosting Accounts</span>
            </a>

            <a href="domains.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all group <?= $current=='domains.php'?'bg-gray-700':'' ?>">
                <i class="fas fa-globe mr-3 text-emerald-400"></i>
                <span class="font-medium">Domains</span>
            </a>

            <?php if (isAdmin()): ?>
            <a href="admin.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all group <?= $current=='admin.php'?'bg-gray-700':'' ?>">
                <i class="fas fa-user-shield mr-3 text-emerald-400"></i>
                <span class="font-medium">Admin Panel</span>
            </a>
            <?php endif; ?>
        </nav>

        <div class="p-4 border-t border-gray-700">
            <a href="logout.php" class="flex items-center justify-center w-full px-4 py-3 bg-red-600 hover:bg-red-700 rounded-lg font-semibold">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content p-6 md:p-10">

        <h1 class="text-4xl font-bold text-gray-800 mb-8">
            Selamat datang kembali, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?> 
        </h1>

        <!-- JIKA BELUM PUNYA AKUN FTP / HOSTING -->
        <?php if (!$has_ftp): ?>
            <div class="glass-card rounded-3xl p-10 text-center text-white mb-10">
                <i class="fas fa-exclamation-triangle text-8xl text-yellow-400 mb-6"></i>
                <h2 class="text-4xl font-black mb-4">Belum Punya Paket Hosting</h2>
                <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                    Saat ini kamu belum memiliki akun hosting/FTP aktif.<br>
                    Untuk dapat mengelola file website, silakan hubungi admin untuk membeli paket hosting premium.
                </p>
                <a href="https://wa.me/6281234567890?text=Halo%20Admin%2C%20saya%20mau%20beli%20paket%20hosting%20ArtDevata" 
                   target="_blank"
                   class="inline-flex items-center gap-3 px-10 py-5 btn-tosca rounded-full font-bold text-lg shadow-xl transition">
                    <i class="fab fa-whatsapp text-2xl"></i>
                    Hubungi Admin via WhatsApp
                </a>
                <p class="mt-6 text-sm text-gray-400">
                    Atau klik tombol <strong>Minta Penawaran</strong> di website utama
                </p>
            </div>
        <?php endif; ?>

        <!-- CARD MENU (hanya tampil kalau sudah punya hosting atau tetap tampil untuk domain) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 <?= !$has_ftp ? 'opacity-50 pointer-events-none' : '' ?>">
            <a href="ftp.php" class="bg-gradient-to-br from-emerald-500 to-teal-600 p-10 rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all text-white text-center">
                <i class="fas fa-server text-7xl mb-4"></i>
                <div class="text-2xl font-bold">Hosting Accounts</div>
            </a>

            <a href="domains.php" class="bg-gradient-to-br from-blue-500 to-indigo-600 p-10 rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all text-white text-center">
                <i class="fas fa-globe text-7xl mb-4"></i>
                <div class="text-2xl font-bold">Domains</div>
            </a>

            <?php if (isAdmin()): ?>
            <a href="admin.php" class="bg-gradient-to-br from-purple-600 to-pink-600 p-10 rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all text-white text-center">
                <i class="fas fa-user-shield text-7xl mb-4"></i>
                <div class="text-2xl font-bold">Admin Panel</div>
            </a>
            <?php endif; ?>
        </div>

        <!-- Informasi Akun -->
        <div class="mt-12 bg-white rounded-2xl shadow-xl p-8 border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Informasi Akun</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700">
                <div>
                    <p class="font-semibold">Nama</p>
                    <p class="text-lg"><?= htmlspecialchars($_SESSION['name'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="font-semibold">Email / Username</p>
                    <p class="font-mono text-blue-600 text-lg">
                        <?= htmlspecialchars($_SESSION['email'] ?? $_SESSION['username'] ?? '-') ?>
                    </p>
                </div>
                <div>
                    <p class="font-semibold">Status Akun</p>
                    <p class="text-lg font-bold <?= isAdmin() ? 'text-purple-600' : 'text-emerald-600' ?>">
                        <?= $has_ftp ? 'Aktif (Sudah punya hosting)' : 'Belum aktif (Belum punya hosting)' ?>
                    </p>
                </div>
                <div>
                    <p class="font-semibold">Level Akses</p>
                    <p class="text-lg font-bold <?= isAdmin() ? 'text-purple-600' : 'text-gray-700' ?>">
                        <?= isAdmin() ? 'Administrator' : 'Pengguna Biasa' ?>
                    </p>
                </div>
            </div>
            <div class="mt-6 pt-6 border-t text-sm text-gray-500">
                Terakhir login: <?= date('d F Y, H:i') ?> WIB
            </div>
        </div>
    </div>

    <!-- WhatsApp Float (selalu ada) -->
    <a href="https://wa.me/6281234567890" target="_blank"
       class="fixed bottom-6 right-6 w-16 h-16 bg-emerald-500 hover:bg-emerald-600 rounded-full flex items-center justify-center shadow-2xl z-50 transition hover:scale-110">
        <i class="fab fa-whatsapp text-3xl text-white"></i>
    </a>
</body>
</html>