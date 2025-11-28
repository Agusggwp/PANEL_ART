<?php
require_once 'functions.php';
if(!isLoggedIn()) redirect('index.php');

$user_id = $_SESSION['user_id'];
$ftp_id = $_GET['ftp_id'] ?? null;
$file = $_GET['file'] ?? null;

if(!$ftp_id || !$file) die("File tidak ditemukan");

// Ambil akun FTP user
$stmt = $pdo->prepare("SELECT * FROM ftp_accounts WHERE id=? AND user_id=? LIMIT 1");
$stmt->execute([$ftp_id, $user_id]);
$ftp_account = $stmt->fetch();
if(!$ftp_account) die("Tidak ada akun FTP atau akses ditolak!");

// Koneksi FTP
$conn = ftp_connect($ftp_account['host'], $ftp_account['port']);
if(!$conn) die("Gagal koneksi FTP!");
if(!ftp_login($conn, $ftp_account['username'], $ftp_account['password'])){
    die("Login FTP gagal!");
}
ftp_pasv($conn, true);

// Simpan file jika ada POST
$msg = '';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $new_content = $_POST['content'] ?? '';
    $tmp_file = tempnam(sys_get_temp_dir(), 'ftp_');
    file_put_contents($tmp_file, $new_content);
    if(ftp_put($conn, $file, $tmp_file, FTP_ASCII)){
        logActivity($user_id, "Edit file $file");
        $msg = "File berhasil disimpan!";
    } else {
        $msg = "Gagal menyimpan file!";
    }
    unlink($tmp_file);
}

// Ambil file untuk editor
$tmp_file = tempnam(sys_get_temp_dir(), 'ftp_');
if(!ftp_get($conn, $tmp_file, $file, in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','bmp','webp']) ? FTP_BINARY : FTP_ASCII)){
    die("Gagal ambil file!");
}
$content = file_get_contents($tmp_file);
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$img_types = ['jpg','jpeg','png','gif','webp','bmp'];
$is_image = in_array($ext, $img_types);

ftp_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editor: <?= e(basename($file)) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<?php if(!$is_image): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.23.1/ace.js"></script>
<?php endif; ?>
<style>
html, body {height:100%; margin:0; overflow:hidden; background:#1e1e1e;}
#editor {height:100%; width:100%;}
.toolbar {position:absolute; top:0; left:0; width:100%; height:50px; background:#1e1e1e; display:flex; align-items:center; padding:0 10px; gap:10px; z-index:100;}
.toolbar button {color:white; background:#007acc; border:none; padding:5px 10px; border-radius:3px; cursor:pointer;}
.toolbar button:hover {background:#005f99;}
.toolbar.mini {height:30px; padding:0 5px;}
</style>
</head>
<body>

<?php if($is_image): ?>
<img src="ftp_preview_image.php?ftp_id=<?= $ftp_id ?>&file=<?= urlencode($file) ?>" class="max-w-full max-h-full mx-auto my-auto" style="display:block;">
<?php else: ?>

<div id="toolbar" class="toolbar">
    <button onclick="goBack()">Kembali</button>
    <button onclick="saveFile()">Save</button>
    <button onclick="downloadFile()">Download</button>
    <button onclick="renameFile()">Rename</button>
    <button onclick="toggleToolbar()">Toggle Mini/Full</button>
    <span class="ml-auto text-white"><?= e($file) ?></span>
</div>

<form method="POST" id="editor-form">
    <textarea name="content" id="content" class="hidden"><?= htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
</form>

<div id="editor"><?= htmlspecialchars($content) ?></div>

<script>
let editor = ace.edit("editor");
editor.setTheme("ace/theme/monokai");
editor.session.setMode("ace/mode/<?= in_array($ext,['php','html','js','css','json','txt'])?$ext:'text' ?>");
editor.setOptions({ fontSize: "14pt", showPrintMargin:false, wrap:true });
editor.resize();

function saveFile(){
    document.getElementById('content').value = editor.getValue();
    document.getElementById('editor-form').submit();
}

function downloadFile(){
    window.location.href='ftp_download.php?ftp_id=<?= $ftp_id ?>&file=<?= urlencode($file) ?>';
}

function renameFile(){
    let newname = prompt("Nama baru:", "<?= basename($file) ?>");
    if(newname && newname!== "<?= basename($file) ?>"){
        let folder = "<?= dirname($file) ?>";
        window.location.href='ftp_manager.php?ftp_id=<?= $ftp_id ?>&action=rename&oldname='+encodeURIComponent("<?= basename($file) ?>")+'&newname='+encodeURIComponent(newname)+'&folder='+encodeURIComponent(folder);
    }
}

function goBack(){
    let folder = "<?= urlencode(dirname($file)) ?>";
    window.location.href = 'ftp_manager.php?ftp_id=<?= $ftp_id ?>&folder=' + folder;
}

// Toggle toolbar mini/full
function toggleToolbar(){
    const tb = document.getElementById('toolbar');
    tb.classList.toggle('mini');
    editor.resize();
}

window.addEventListener('resize', ()=> editor.resize());
</script>

<?php if(!empty($msg)): ?>
<div class="absolute top-0 right-0 mt-12 mr-4 bg-green-500 text-white p-2 rounded shadow"><?= e($msg) ?></div>
<?php endif; ?>

<?php endif; ?>

</body>
</html>
