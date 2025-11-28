<?php
if(!isLoggedIn()) redirect('index.php');
?>

<!-- Sidebar - Full height (100vh) -->
<div class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-gray-900 to-gray-800 text-white flex flex-col shadow-2xl z-50">
    <!-- Header -->
    <div class="p-6 text-2xl font-extrabold tracking-wide text-green-400 border-b border-gray-700 flex-shrink-0">
        ARTDEVATA PANEL
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-3 py-4">
        <a href="dashboard.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all duration-200 group">
            <span class="material-icons mr-3 text-green-400 group-hover:text-green-300">dashboard</span>
            <span class="font-medium">Dashboard</span>
        </a>
        <a href="ftp.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all duration-200 group">
            <span class="material-icons mr-3 text-green-400 group-hover:text-green-300">folder</span>
            <span class="font-medium">FTP Accounts</span>
        </a>
        <a href="domains.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all duration-200 group">
            <span class="material-icons mr-3 text-green-400 group-hover:text-green-300">public</span>
            <span class="font-medium">Domains</span>
        </a>

        <?php if(isAdmin()): ?>
        <a href="admin.php" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-700 transition-all duration-200 group">
            <span class="material-icons mr-3 text-green-400 group-hover:text-green-300">admin_panel_settings</span>
            <span class="font-medium">Admin Panel</span>
        </a>
        <?php endif; ?>
    </nav>

    <!-- Logout - Selalu di paling bawah -->
    <div class="p-4 border-t border-gray-700 flex-shrink-0">
        <a href="logout.php" class="flex items-center justify-center w-full px-4 py-3 bg-red-600 hover:bg-red-700 rounded-lg transition-all duration-200 font-semibold shadow-md">
            <span class="material-icons mr-2">logout</span>
            Logout
        </a>
    </div>
</div>

<!-- Material Icons CDN (taruh di head atau sebelum </body>) -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- Optional: Agar konten utama tidak tertutup sidebar -->
<style>
    @media (min-width: 768px) {
        body {
            padding-left: 16rem; /* w-64 = 16rem */
        }
    }
    
    /* Mobile: sidebar overlay atau gunakan toggle JS jika perlu */
    @media (max-width: 767px) {
        .fixed.inset-y-0.left-0 {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }
        .fixed.inset-y-0.left-0.sidebar-open {
            transform: translateX(0);
        }
    }
</style>