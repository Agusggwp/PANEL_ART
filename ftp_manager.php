<?php
require_once 'functions.php';
if (!isLoggedIn()) redirect('index.php');

$user_id = $_SESSION['user_id'];
$ftp_id  = $_GET['ftp_id'] ?? null;
$folder  = $_GET['folder'] ?? '/';

// Validasi FTP Account
if (!$ftp_id) die("FTP Account tidak ditemukan");
$stmt = $pdo->prepare("SELECT * FROM ftp_accounts WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$ftp_id, $user_id]);
$ftp_account = $stmt->fetch();
if (!$ftp_account) die("Akses ditolak!");

// Koneksi FTP
$conn = ftp_connect($ftp_account['host'], $ftp_account['port'] ?? 21, 10);
if (!$conn || !ftp_login($conn, $ftp_account['username'], $ftp_account['password'])) {
    die("Gagal terhubung ke server FTP!");
}
ftp_pasv($conn, true);

function formatBytes($bytes, $precision = 2) {
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), $precision) . ' ' . $units[$i];
}

function deleteFTP($conn, $path) {
    if (ftp_size($conn, $path) !== -1) return ftp_delete($conn, $path);
    $list = ftp_nlist($conn, $path) ?: [];
    foreach ($list as $item) {
        if ($item === '.' || $item === '..') continue;
        deleteFTP($conn, "$path/$item");
    }
    return ftp_rmdir($conn, $path);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $res = ['status' => false, 'msg' => 'Unknown error'];

    if ($action === 'upload' && isset($_FILES['file'])) {
        $remote = rtrim($folder, '/') . '/' . $_FILES['file']['name'];
        if (ftp_put($conn, $remote, $_FILES['file']['tmp_name'], FTP_BINARY)) {
            logActivity($user_id, "Upload " . $_FILES['file']['name']);
            $res = ['status' => true, 'msg' => 'File berhasil diupload!'];
        } else $res['msg'] = 'Gagal upload file';
    }

    if ($action === 'mkdir' && !empty($_POST['dirname'])) {
        $dir = rtrim($folder, '/') . '/' . $_POST['dirname'];
        if (ftp_mkdir($conn, $dir)) {
            logActivity($user_id, "Buat folder " . $_POST['dirname']);
            $res = ['status' => true, 'msg' => 'Folder berhasil dibuat!'];
        } else $res['msg'] = 'Gagal membuat folder';
    }

    if ($action === 'delete' && !empty($_POST['path'])) {
        $path = rtrim($folder, '/') . '/' . $_POST['path'];
        if (deleteFTP($conn, $path)) {
            logActivity($user_id, "Hapus " . $_POST['path']);
            $res = ['status' => true, 'msg' => 'Berhasil dihapus!'];
        } else $res['msg'] = 'Gagal menghapus';
    }

    if ($action === 'rename' && !empty($_POST['old']) && !empty($_POST['new'])) {
        $old = rtrim($folder, '/') . '/' . $_POST['old'];
        $new = rtrim($folder, '/') . '/' . $_POST['new'];
        if (ftp_rename($conn, $old, $new)) {
            logActivity($user_id, "Rename " . $_POST['old'] . " → " . $_POST['new']);
            $res = ['status' => true, 'msg' => 'Berhasil di-rename!'];
        } else $res['msg'] = 'Gagal rename';
    }

    ftp_close($conn);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
}

$raw = ftp_rawlist($conn, $folder) ?: [];
$items = [];
foreach ($raw as $line) {
    $parts = preg_split('/\s+/', $line, 9);
    if (count($parts) < 9) continue;
    $name = $parts[8];
    if ($name === '.' || $name === '..') continue;
    $isDir = $parts[0][0] === 'd';
    $size = $isDir ? '-' : $parts[4];
    $date = $parts[5] . ' ' . $parts[6] . ' ' . $parts[7];
    $items[] = compact('name', 'isDir', 'size', 'date');
}
ftp_close($conn);

$path_parts = array_filter(explode('/', trim($folder, '/')));
$breadcrumb = [['path' => '/', 'name' => 'Root']];
$cumulative = '';
foreach ($path_parts as $part) {
    $cumulative .= '/' . $part;
    $breadcrumb[] = ['path' => $cumulative, 'name' => $part];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager — ARTDEVATA PANEL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; color: #1e293b; }
        .orbitron { font-family: 'Orbitron', sans-serif; }
        .card { box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .item-row:hover { background: #f1f5f9; }
        @media (min-width: 768px) { body { padding-left: 16rem; } }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <?php include 'dashboard_sidebar.php'; ?>

    <main class="p-6 md:p-10">
        <div class="max-w-7xl mx-auto">

            <!-- Header -->
            <div class="bg-white rounded-2xl card p-8 mb-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-4xl font-bold orbitron text-green-600 flex items-center gap-4">
                            <span class="material-icons text-5xl text-green-500">folder_special</span>
                           ARTDEVATA PANEL
                        </h1>
                        <p class="text-gray-600 mt-2 text-lg">Akun: <strong class="text-green-700"><?= htmlspecialchars($ftp_account['account_name']) ?></strong></p>
                    </div>
                    <div class="flex gap-4">
                        <button onclick="document.getElementById('uploadInput').click()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-7 py-4 rounded-xl flex items-center gap-3 shadow-lg transition transform hover:scale-105">
                            <span class="material-icons">cloud_upload</span> Upload File
                        </button>
                        <button onclick="showMkdir()" 
                                class="bg-green-600 hover:bg-green-700 text-white px-7 py-4 rounded-xl flex items-center gap-3 shadow-lg transition transform hover:scale-105">
                            <span class="material-icons">create_new_folder</span> Folder Baru
                        </button>
                    </div>
                </div>
            </div>

            <!-- Breadcrumb -->
            <div class="bg-white rounded-xl card p-5 mb-6 border">
                <div class="flex items-center gap-3 flex-wrap text-lg">
                    <?php foreach ($breadcrumb as $i => $crumb): ?>
                        <?php if ($i === count($breadcrumb) - 1): ?>
                            <span class="text-green-600 font-bold"><?= htmlspecialchars($crumb['name']) ?></span>
                        <?php else: ?>
                            <a href="?ftp_id=<?= $ftp_id ?>&folder=<?= urlencode($crumb['path']) ?>" 
                               class="text-gray-600 hover:text-green-600 transition font-medium"><?= htmlspecialchars($crumb['name']) ?></a>
                            <span class="material-icons text-gray-400">chevron_right</span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tabel File -->
            <div class="bg-white rounded-2xl card overflow-hidden border">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-green-600 to-emerald-600 text-white">
                        <tr>
                            <th class="px-8 py-5 text-left text-lg">Nama</th>
                            <th class="px-8 py-5 text-left hidden md:table-cell">Ukuran</th>
                            <th class="px-8 py-5 text-left hidden lg:table-cell">Terakhir Diubah</th>
                            <th class="px-8 py-5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-24 text-gray-500">
                                    <span class="material-icons text-9xl opacity-20 block mb-4">folder_open</span>
                                    <p class="text-2xl">Folder kosong</p>
                                </td>
                            </tr>
                        <?php else: foreach ($items as $item): ?>
                            <tr class="item-row transition">
                                <td class="px-8 py-6">
                                    <?php if ($item['isDir']): ?>
                                        <a href="?ftp_id=<?= $ftp_id ?>&folder=<?= urlencode(rtrim($folder, '/').'/'.$item['name']) ?>" 
                                           class="flex items-center gap-4 text-green-600 hover:text-green-700 font-semibold text-lg">
                                            <span class="material-icons text-4xl">folder</span>
                                            <?= htmlspecialchars($item['name']) ?>
                                        </a>
                                    <?php else: ?>
                                        <div class="flex items-center gap-4 text-gray-800">
                                            <span class="material-icons text-4xl text-blue-600">description</span>
                                            <span class="font-medium text-lg"><?= htmlspecialchars($item['name']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-6 text-gray-600 hidden md:table-cell">
                                    <?= $item['size'] === '-' ? '-' : '<strong>'.formatBytes($item['size']).'</strong>' ?>
                                </td>
                                <td class="px-8 py-6 text-gray-500 hidden lg:table-cell"><?= $item['date'] ?></td>
                                <td class="px-8 py-6 text-center space-x-3">
                                    <?php if (!$item['isDir']): ?>
                                        <a href="ftp_download.php?ftp_id=<?= $ftp_id ?>&file=<?= urlencode($folder.'/'.$item['name']) ?>" 
                                           class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg inline-flex items-center gap-2 shadow transition">
                                            <span class="material-icons">download</span>
                                        </a>
                                        <a href="ftp_preview.php?ftp_id=<?= $ftp_id ?>&file=<?= urlencode($folder.'/'.$item['name']) ?>" 
                                           class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-3 rounded-lg inline-flex items-center gap-2 shadow transition">
                                            <span class="material-icons">visibility</span>
                                        </a>
                                    <?php endif; ?>
                                    <button onclick="renameItem('<?= addslashes($item['name']) ?>')" 
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-3 rounded-lg shadow transition">
                                        <span class="material-icons">edit</span>
                                    </button>
                                    <button onclick="deleteItem('<?= addslashes($item['name']) ?>')" 
                                            class="bg-red-600 hover:bg-red-700 text-white px-5 py-3 rounded-lg shadow transition">
                                        <span class="material-icons">delete</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <input type="file" id="uploadInput" class="hidden" onchange="uploadFile(this.files[0])">

    <script>
        function uploadFile(file) {
            if (!file) return;
            const formData = new FormData();
            formData.append('action', 'upload');
            formData.append('file', file);
            Swal.fire({ title: 'Mengupload...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            fetch('', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                    Swal.fire(res.status ? 'Sukses!' : 'Gagal!', res.msg, res.status ? 'success' : 'error')
                        .then(() => res.status && location.reload());
                });
        }

        function showMkdir() {
            Swal.fire({
                title: 'Buat Folder Baru',
                input: 'text',
                inputPlaceholder: 'Nama folder',
                showCancelButton: true,
                confirmButtonText: 'Buat'
            }).then(result => {
                if (result.isConfirmed && result.value) {
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=mkdir&dirname=${encodeURIComponent(result.value)}`
                    }).then(r => r.json()).then(res => {
                        Swal.fire(res.status ? 'Sukses!' : 'Gagal!', res.msg, res.status ? 'success' : 'error')
                            .then(() => res.status && location.reload());
                    });
                }
            });
        }

        function renameItem(oldname) {
            Swal.fire({
                title: 'Rename',
                input: 'text',
                inputValue: oldname,
                showCancelButton: true
            }).then(result => {
                if (result.isConfirmed && result.value !== oldname) {
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=rename&old=${encodeURIComponent(oldname)}&new=${encodeURIComponent(result.value)}`
                    }).then(r => r.json()).then(res => {
                        if (res.status) location.reload();
                        else Swal.fire('Gagal!', res.msg, 'error');
                    });
                }
            });
        }

        function deleteItem(name) {
            Swal.fire({
                title: 'Hapus?',
                text: `${name} akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete&path=${encodeURIComponent(name)}`
                    }).then(r => r.json()).then(res => {
                        if (res.status) location.reload();
                        else Swal.fire('Gagal!', res.msg, 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>