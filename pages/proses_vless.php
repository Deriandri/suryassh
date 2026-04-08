<?php
session_start();
include_once '../config.php';

/**
 * 1. PROTEKSI AKSES ILEGAL & GLOBAL LIMIT
 */
$today = date('Y-m-d'); // Definisi hari ini untuk pengecekan database
$device_sig = isset($_POST['device_sig']) ? mysqli_real_escape_string($conn, $_POST['device_sig']) : 'UNKNOWN';

// --- PROTEKSI SERVER-SIDE GLOBAL (AKUMULASI SEMUA PROTOKOL) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $device_sig !== 'UNKNOWN') {
    // Hitung semua akun dari perangkat ini HARI INI tanpa peduli jenis protokolnya
    $check_dev = $conn->query("SELECT COUNT(*) as total FROM accounts WHERE device_id = '$device_sig' AND date_created = '$today'");
    if ($check_dev) {
        $dev_log = $check_dev->fetch_assoc();
        if ($dev_log['total'] >= 3) {
            $_SESSION['error_msg'] = "Limit Perangkat Tercapai! (Total 3 akun lintas protokol harian).";
            header("Location: " . $_SERVER['HTTP_REFERER']); 
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!isset($_GET['status']) || $_GET['status'] !== 'success' || !isset($_SESSION['vless_result'])) {
        header("Location: ../v2ray_vless.php"); 
        exit;
    }
}

// 2. LOGIKA PEMBUATAN AKUN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['user']) || empty($_POST['user'])) {
        header("Location: ../v2ray_vless.php");
        exit;
    }

    $id     = (int)$_POST['server_id']; 
    $u      = mysqli_real_escape_string($conn, strtolower(trim($_POST['user']))); 
    $d      = (int)$_POST['duration']; 
    $q      = (int)$_POST['quota'];
    $ip_lim = (int)$_POST['iplimit'];

    // --- PROTEKSI USERNAME DUPLIKAT (CEK DATABASE) ---
    $check_user = $conn->query("SELECT id FROM accounts WHERE username = '$u'");
    if ($check_user && $check_user->num_rows > 0) {
        $_SESSION['error_msg'] = "The username '$u' is already active in our database.";
        header("Location: create_vless.php?id=$id");
        exit;
    }

    $res = $conn->query("SELECT * FROM servers WHERE id = $id");
    $s = $res->fetch_assoc();

    if ($s) {
        $ssh = @ssh2_connect($s['ip'], 22);
        if ($ssh && @ssh2_auth_password($ssh, 'root', $s['password'])) {
            
            // 1. Eksekusi Script di VPS
            $command = "/usr/local/sbin/add-vle $u $q $ip_lim $d";
            $stream = ssh2_exec($ssh, $command);
            stream_set_blocking($stream, true);
            stream_get_contents($stream); 
            
            // 2. Ambil Output Config dari VPS
            $get_config = "cat /var/www/html/vless-$u.txt";
            $stream_cat = ssh2_exec($ssh, $get_config);
            stream_set_blocking($stream_cat, true);
            $raw_config = stream_get_contents($stream_cat);
            
            if ($raw_config && strlen($raw_config) > 100) {
                // Filter agar teks dimulai dari VLESS XRAY
                if (strpos($raw_config, 'VLESS XRAY') !== false) {
                    $raw_config = substr($raw_config, strpos($raw_config, 'VLESS XRAY'));
                }
                
                // Truncate sisa teks credit jika ada
                $expiry_pos = strpos($raw_config, 'Expiry');
                if ($expiry_pos !== false) {
                    $next_divider = strpos($raw_config, '◇━━━━━━━━━━━━━━━━━◇', $expiry_pos);
                    if ($next_divider !== false) {
                        $raw_config = substr($raw_config, 0, $next_divider + strlen('◇━━━━━━━━━━━━━━━━━◇'));
                    }
                }

                $exp_db = date('Y-m-d', strtotime("+$d days"));
                // UPDATE: Simpan device_id ke Database
                $conn->query("INSERT INTO accounts (username, vps_ip, protocol, created_by_ip, device_id, date_created, date_expired) 
                              VALUES ('$u', '{$s['ip']}', 'vless', '{$_SERVER['REMOTE_ADDR']}', '$device_sig', '$today', '$exp_db')");
                
                $conn->query("UPDATE servers SET daily_used = daily_used + 1 WHERE id = $id");

                $_SESSION['vless_result'] = [
                    'user'   => $u,
                    'config' => trim($raw_config),
                    'exp'    => date('d M, Y', strtotime($exp_db))
                ];

                header("Location: proses_vless.php?status=success");
                exit;
            } else {
                $_SESSION['error_msg'] = "Gagal memproses konfigurasi.";
            }
        } else {
            $_SESSION['error_msg'] = "Koneksi ke server node gagal.";
        }
    }
    header("Location: create_vless.php?id=$id"); 
    exit;
}

// --- TAMPILAN HASIL SUKSES (ELITE UI - TIDAK BERUBAH) ---
if (isset($_GET['status']) && $_GET['status'] == 'success' && isset($_SESSION['vless_result'])) {
    $data = $_SESSION['vless_result'];
    include_once '../header.php';
?>

<style>
    /* --- SYSTEM THEME ADAPTIVE --- */
    :root {
        --primary: #2563eb;
        --bg-site: #f8fafc;
        --bg-card: #ffffff;
        --bg-box: #f1f5f9;
        --text-main: #1e293b;
        --text-muted: #475569;
        --border: #e2e8f0;
    }

    body.dark, [data-bs-theme="dark"] body {
        --bg-site: #0f172a;
        --bg-card: #1e293b;
        --bg-box: rgba(15, 23, 42, 0.6);
        --text-main: #f1f5f9;
        --border: rgba(255,255,255,0.06);
    }

    body { background-color: var(--bg-site) !important; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; overflow-x: hidden; }

    .result-container { padding: 40px 12px 100px; }
    
    .elite-card {
        max-width: 600px; margin: 0 auto; background: var(--bg-card); border-radius: 30px;
        overflow: hidden; border: 1px solid var(--border); box-shadow: 0 40px 80px rgba(0,0,0,0.1);
    }

    .banner-premium {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        padding: 35px 20px; text-align: center; color: #fff;
    }
    .banner-premium h5 { font-weight: 900; font-size: 20px; letter-spacing: 1px; margin: 0; }

    .summary-bar {
        display: flex; justify-content: center; gap: 15px; padding: 12px;
        background: rgba(37, 99, 235, 0.05); border-bottom: 1px solid var(--border);
        font-size: 11px; font-weight: 800; flex-wrap: wrap; text-transform: uppercase;
    }
    .summary-bar span { color: var(--primary); }

    .config-box { margin: 20px 15px; padding: 20px; border-radius: 20px; background: var(--bg-box); border: 1px solid var(--border); overflow: hidden; }
    .config-pre {
        width: 100%; color: var(--text-main); font-family: 'SFMono-Regular', Consolas, monospace; 
        font-size: 11px; line-height: 1.6; white-space: pre-wrap; word-break: break-all; margin: 0;
    }

    .action-group { padding: 0 15px 35px; display: flex; justify-content: center; }
    .btn-action { padding: 15px 40px; border-radius: 50px; font-weight: 800; font-size: 12px; text-transform: uppercase; border: none; transition: 0.3s; text-decoration: none; text-align: center; min-width: 250px; }
    .btn-home { background: var(--bg-box); color: var(--text-main) !important; border: 1px solid var(--border); }
    .btn-action:hover { transform: translateY(-2px); filter: brightness(1.05); }

    .protocol-nav-title { font-size: 10px; font-weight: 800; color: var(--text-muted); text-align: center; text-transform: uppercase; letter-spacing: 2px; margin: 50px 0 25px; display: block; }
    
    .protocol-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; max-width: 600px; margin: 0 auto 50px; }
    .p-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 20px 10px; text-align: center; transition: 0.3s; }
    .p-card:hover { transform: translateY(-5px); border-color: var(--primary); }
    .p-card i { font-size: 20px; margin-bottom: 12px; display: block; }
    .p-card span { font-size: 9px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 15px; }
    
    .p-btn { border: none; border-radius: 50px; padding: 8px 5px; font-size: 9px; font-weight: 900; text-transform: uppercase; color: #fff !important; text-decoration: none; display: block; width: 100%; transition: 0.2s; }
    .p-ssh { background: #3b82f6; box-shadow: 0 5px 10px rgba(59, 130, 246, 0.2); }
    .p-vless { background: #f59e0b; box-shadow: 0 5px 10px rgba(245, 158, 11, 0.2); }
    .p-trojan { background: #ef4444; box-shadow: 0 5px 10px rgba(239, 68, 68, 0.2); }

    .vless-seo-section { max-width: 650px; margin: 0 auto; background: var(--bg-card); border-radius: 25px; padding: 30px; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .vless-seo-section h6 { font-weight: 800; font-size: 14px; color: var(--primary); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 15px; }
    .vless-seo-section p { font-size: 13px; color: var(--text-muted); line-height: 1.8; margin-bottom: 15px; }
    .vless-feature-list { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .vless-feature-list li { font-size: 11px; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px; }
    .vless-feature-list li i { color: #22c55e; }

    @media (max-width: 480px) {
        .protocol-grid { grid-template-columns: 1fr; }
        .p-card { padding: 15px; display: flex; align-items: center; justify-content: space-between; text-align: left; }
        .p-card i { margin-bottom: 0; }
        .p-card span { margin-bottom: 0; margin-left: 10px; flex-grow: 1; }
        .p-btn { width: 100px; }
        .btn-action { width: 100%; }
        .vless-feature-list { grid-template-columns: 1fr; }
    }
</style>

<div class="container result-container">
    <div class="elite-card animate__animated animate__fadeIn">
        <div class="banner-premium">
            <h5>.::. SURYA SSH .::.</h5>
            <p class="mb-0 opacity-75 small">Premium Profile Deployed Successfully</p>
        </div>

        <div class="summary-bar">
            <div>Username: <span><?= $data['user'] ?></span></div>
            <div>Expired: <span><?= $data['exp'] ?></span></div>
        </div>

        <div class="config-box">
            <div id="accountConfig" class="config-pre"><?= htmlspecialchars($data['config']) ?></div>
        </div>

        <div class="action-group">
            <a href="../index.php" class="btn-action btn-home">
                <i class="fas fa-home me-2"></i> Return Home
            </a>
        </div>
    </div>

    <span class="protocol-nav-title">Browse Other Protocols</span>
    <div class="protocol-grid">
        <div class="p-card shadow-sm">
            <i class="fas fa-terminal text-primary"></i>
            <span>SSH Server</span>
            <a href="../ssh_websocket.php" class="p-btn p-ssh">Pilih SSH</a>
        </div>
        
        <div class="p-card shadow-sm">
            <i class="fas fa-bolt text-warning"></i>
            <span>VLESS</span>
            <a href="../v2ray_vless.php" class="p-btn p-vless">Pilih VLESS</a>
        </div>
        
        <div class="p-card shadow-sm">
            <i class="fas fa-shield-alt text-danger"></i>
            <span>Trojan</span>
            <a href="../v2ray_trojan.php" class="p-btn p-trojan">Pilih Trojan</a>
        </div>
    </div>

    <div class="vless-seo-section animate__animated animate__fadeInUp">
        <h6>Premium V2RAY Vless Technology</h6>
        <p>Nikmati pengalaman internet tanpa batas dengan infrastruktur V2Ray Vless SuryaSSH.</p>
        <ul class="vless-feature-list">
            <li><i class="fas fa-check-circle"></i> High-Speed Xray Engine</li>
            <li><i class="fas fa-check-circle"></i> Low Overhead Encryption</li>
            <li><i class="fas fa-check-circle"></i> Adaptive Routing Path</li>
            <li><i class="fas fa-check-circle"></i> Zero-Activity Logging</li>
        </ul>
    </div>
</div>

<script>
    if (localStorage.getItem('theme') === 'dark' || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.body.classList.add('dark');
    }
</script>

<?php 
    unset($_SESSION['vless_result']); 
    include_once '../footer.php'; 
} else {
    header("Location: ../v2ray_vless.php");
    exit;
}
?>
