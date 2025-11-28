<?php
require_once 'functions.php';
if (!isLoggedIn()) redirect('index.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — ARTDEVATA PANEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Desktop: konten digeser agar tidak tertutup sidebar */
        @media (min-width: 768px) {
            .main-content {
                margin-left: 16rem; /* 256px = w-64 */
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Sidebar Fixed Full Height (1 layar penuh) -->
    <div class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-gray-900 to-gray-800 text-white flex flex-col shadow-2xl z-50">
        <div class="p-6 text-2xl font-extrabold tracking-wide text-green-400 border-b border-gray-700 flex-shrink-0">
            ARTDEVATA PANEL
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <?php $current = basename($_SERVER['PHP_SELF']); ?>

            <a href="dashboard.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all duration-200 group <?= $current == 'dashboard.php' ? 'bg-gray-700 shadow-inner' : '' ?>">
                <span class="material-icons mr-3 text-green-400 group-hover:text-green-300">dashboard</span>
                <span class="font-medium">Dashboard</span>
            </a>

            <a href="ftp.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all duration-200 group <?= $current == 'ftp.php' ? 'bg-gray-700 shadow-inner' : '' ?>">
                <span class="material-icons mr-3 text-green-400 group-hover:text-green-300">folder</span>
                <span class="font-medium">FTP Accounts</span>
            </a>

            <a href="domains.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all duration-200 group <?= $current == 'domains.php' ? 'bg-gray-700 shadow-inner' : '' ?>">
                <span class="material-icons mr-3 text-green-400 group-hover:text-green-300">public</span>
                <span class="font-medium">Domains</span>
            </a>

            <?php if (isAdmin()): ?>
            <a href="admin.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all duration-200 group <?= $current == 'admin.php' ? 'bg-gray-700 shadow-inner' : '' ?>">
                <span class="material-icons mr-3 text-green-400 group-hover:text-green-300">admin_panel_settings</span>
                <span class="font-medium">Admin Panel</span>
            </a>
            <?php endif; ?>
        </nav>

        <!-- Logout selalu di paling bawah -->
        <div class="p-4 border-t border-gray-700 flex-shrink-0">
            <a href="logout.php" class="flex items-center justify-center w-full px-4 py-3 bg-red-600 hover:bg-red-700 rounded-lg transition-all duration-200 font-semibold shadow-md">
                <span class="material-icons mr-2">logout</span>
                Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content flex-1 p-6 md:p-10">
        <h1 class="text-3xl font-bold mb-8 text-gray-800">
            Selamat datang kembali, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?> 👋
        </h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <a href="ftp.php" class="bg-gradient-to-br from-green-500 to-green-600 p-8 rounded-xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 text-white flex flex-col items-center justify-center">
                <span class="material-icons text-6xl mb-4">folder_shared</span>
                <span class="text-xl font-semibold">Hosting Accounts</span>
            </a>

            <a href="domains.php" class="bg-gradient-to-br from-blue-500 to-blue-600 p-8 rounded-xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 text-white flex flex-col items-center justify-center">
                <span class="material-icons text-6xl mb-4">language</span>
                <span class="text-xl font-semibold">Domains</span>
            </a>

            <?php if (isAdmin()): ?>
            <a href="admin.php" class="bg-gradient-to-br from-yellow-500 to-orange-600 p-8 rounded-xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 text-white flex flex-col items-center justify-center">
                <span class="material-icons text-6xl mb-4">security</span>
                <span class="text-xl font-semibold">Admin Panel</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Informasi Akun – 100% AMAN dari Undefined key -->
        <div class="mt-12 bg-white rounded-xl shadow-lg p-6 border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Informasi Akun</h2>
            
            <div class="space-y-3 text-gray-700">
                <p>
                    <span class="font-semibold">Nama:</span> 
                    <?= htmlspecialchars($_SESSION['name'] ?? 'Tidak tersedia') ?>
                </p>
                
                <p>
                    <span class="font-semibold">Email / Username:</span> 
                    <span class="font-mono text-blue-600">
                        <?= htmlspecialchars($_SESSION['email'] ?? $_SESSION['username'] ?? $_SESSION['user'] ?? 'Tidak tersedia') ?>
                    </span>
                </p>
                
                <p>
                    <span class="font-semibold">Status:</span> 
                    <span class="text-green-600 font-bold <?= isAdmin() ? 'text-purple-600' : '' ?>">
                        <?= isAdmin() ? 'Administrator' : 'User Biasa' ?>
                    </span>
                </p>
                
                <p class="text-sm text-gray-500 pt-4 border-t">
                    Terakhir login: <?= date('d F Y, H:i') ?> WIB
                </p>
            </div>
        </div>
    </div>
</body>
</html>