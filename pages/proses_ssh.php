<?php
session_start(); 
include_once '../config.php';

/**
 * -------------------------------------------------------
 * GATE 1: PROSES EKSEKUSI DATA (POST)
 * -------------------------------------------------------
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!$conn) { die("Koneksi Database Gagal"); }

    if (!isset($_POST['server_id'], $_POST['user'], $_POST['pass'])) {
        header("Location: ../index");
        exit;
    }

    $id      = (int)$_POST['server_id']; 
    $u       = mysqli_real_escape_string($conn, strtolower(trim($_POST['user']))); 
    $p       = mysqli_real_escape_string($conn, $_POST['pass']);
    $day     = isset($_POST['duration']) ? (int)$_POST['duration'] : 30; 
    $iplimit = isset($_POST['iplimit']) ? (int)$_POST['iplimit'] : 2;
    $ip_user = $_SERVER['REMOTE_ADDR']; 
    $today   = date('Y-m-d');

    // --- TAMBAHAN PROTEKSI GLOBAL (LIMIT 3 AKUN PER PERANGKAT) ---
    $dev_sig = isset($_POST['device_sig']) ? mysqli_real_escape_string($conn, $_POST['device_sig']) : 'UNKNOWN';
    if ($dev_sig !== 'UNKNOWN') {
        // Menghitung SEMUA akun yang dibuat oleh ID Perangkat ini hari ini (Semua Protokol)
        $check_limit = $conn->query("SELECT COUNT(*) as total FROM accounts WHERE device_id = '$dev_sig' AND date_created = '$today'");
        if ($check_limit) {
            $res_limit = $check_limit->fetch_assoc();
            if ($res_limit['total'] >= 3) {
                $_SESSION['error_msg'] = "Gagal! Limit Perangkat Tercapai (Maks 3 akun harian All-Protocol).";
                header("Location: create_ssh?id=$id");
                exit;
            }
        }
    }

    // PROTEKSI: Cek Username Duplikat
    $check_user = $conn->query("SELECT id FROM accounts WHERE username = '$u'");
    if ($check_user && $check_user->num_rows > 0) {
        $_SESSION['error_msg'] = "Username <b>$u</b> sudah aktif!";
        header("Location: create_ssh?id=$id");
        exit;
    }

    $res = $conn->query("SELECT * FROM servers WHERE id = $id");
    $s   = $res->fetch_assoc();

    if ($s) {
        $ssh = @ssh2_connect($s['ip'], 22);

        if ($ssh && @ssh2_auth_password($ssh, 'root', $s['password'])) {
            // EKSEKUSI SKRIP VPS (Kirim 4 Input agar Notif Telegram Jalan)
            $cmd = "printf \"$u\\n$p\\n$iplimit\\n$day\\n\" | add-ssh";
            $stream = @ssh2_exec($ssh, $cmd);
            if($stream) {
                stream_set_blocking($stream, true);
                stream_get_contents($stream); 
                fclose($stream);
            }

            // Simpan ke Database (Update: Tambahkan device_id agar terdeteksi)
            $exp = date('Y-m-d', strtotime("+$day days"));
            $conn->query("INSERT INTO accounts (username, password, vps_ip, protocol, created_by_ip, device_id, date_created, date_expired) 
                          VALUES ('$u', '$p', '{$s['ip']}', 'ssh', '$ip_user', '$dev_sig', '$today', '$exp')");

            $conn->query("UPDATE servers SET daily_used = daily_used + 1 WHERE id = $id");

            // TOKEN KEAMANAN & SESSION
            $secure_token = bin2hex(random_bytes(16));
            $_SESSION['ssh_success_token'] = $secure_token;
            $_SESSION['ssh_result'] = [
                'u' => $u,
                'p' => $p,
                'host' => $s['ip'],
                'name' => $s['name'],
                'exp' => date('d M, Y', strtotime($exp)),
                'day' => $day
            ];

            header("Location: proses_ssh?status=success&auth=" . $secure_token);
            exit;
        } else {
            die("<script>alert('Gagal Koneksi VPS!'); window.location='../index';</script>");
        }
    }
}

/**
 * -------------------------------------------------------
 * GATE 2: PROTEKSI AKSES LANGSUNG (GET)
 * -------------------------------------------------------
 */
$url_auth = isset($_GET['auth']) ? $_GET['auth'] : '';
$ses_auth = isset($_SESSION['ssh_success_token']) ? $_SESSION['ssh_success_token'] : '';

if (!isset($_GET['status']) || $_GET['status'] !== 'success' || empty($ses_auth) || $url_auth !== $ses_auth) {
    header("Location: ../index");
    exit;
}

$data = $_SESSION['ssh_result'];
include_once '../header.php';
?>

<style>
    :root {
        --primary: #3b82f6;
        --primary-dark: #1d4ed8;
        --bg-site: #f8fafc;
        --card-bg: #ffffff;
        --inner-box: #f1f5f9;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border: #e2e8f0;
    }

    body.dark, [data-bs-theme="dark"] body {
        --bg-site: #0b0f1a;
        --card-bg: #161b2c;
        --inner-box: #0f1424;
        --text-main: #f1f5f9;
        --text-muted: #94a3b8;
        --border: rgba(255,255,255,0.06);
    }

    body { background-color: var(--bg-site) !important; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; margin: 0; overflow-x: hidden; }
    
    /* Header Area */
    .elite-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        padding: 100px 20px 180px;
        text-align: center;
        clip-path: ellipse(150% 100% at 50% 0%);
    }
    .header-tag { background: rgba(255,255,255,0.1); color: #fff; padding: 6px 15px; border-radius: 50px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; display: inline-block; margin-bottom: 15px; border: 1px solid rgba(255,255,255,0.2); }
    .header-title { font-weight: 900; font-size: clamp(1.8rem, 7vw, 2.8rem); color: #fff; letter-spacing: -2px; margin-bottom: 10px; }

    /* Layout Wrapper */
    .main-wrapper { margin-top: -120px; padding: 0 15px 80px; position: relative; z-index: 50; }
    .result-card {
        background: var(--card-bg) !important; border-radius: 40px; max-width: 550px;
        margin: 0 auto; border: 1px solid var(--border); box-shadow: 0 40px 100px rgba(0,0,0,0.15); overflow: hidden;
    }

    /* Account Grid */
    .account-grid { padding: 35px 25px 0; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .grid-tile { background: var(--inner-box); border: 1px solid var(--border); border-radius: 20px; padding: 18px; display: flex; align-items: center; gap: 15px; transition: 0.3s; }
    .tile-icon { width: 40px; height: 40px; background: rgba(59, 130, 246, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 18px; }
    .tile-label { font-size: 9px; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
    .tile-val { font-size: 14px; font-weight: 700; color: var(--text-main); word-break: break-all; }

    /* Config Content */
    .config-area { margin: 25px; }
    .config-box { 
        background: var(--inner-box); padding: 25px; border-radius: 25px; border: 1px solid var(--border);
        font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-main); line-height: 1.6;
        white-space: pre-wrap; word-break: break-all; position: relative; overflow: hidden;
    }
    .config-box::before { content: "RAW CONFIGURATION"; position: absolute; top: 0; right: 0; background: var(--primary); color: #fff; font-size: 8px; padding: 4px 12px; border-radius: 0 0 0 12px; font-weight: 900; }

    /* Actions */
    .action-panel { padding: 0 25px 40px; display: flex; flex-direction: column; gap: 12px; }
    .btn-elite { padding: 18px; border-radius: 50px; font-weight: 900; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; border: none; transition: 0.3s; cursor: pointer; text-align: center; text-decoration: none; }
    .btn-primary-pro { background: var(--primary); color: #fff !important; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3); }
    .btn-primary-pro:hover { transform: translateY(-3px); background: var(--primary-dark); box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4); }
    .btn-outline-pro { background: var(--inner-box); color: var(--text-muted) !important; border: 1px solid var(--border); }

    /* Footer Navigation */
    .nav-other { max-width: 550px; margin: 40px auto 0; text-align: center; }
    .nav-label { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; display: block; }
    .proto-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .proto-link { background: var(--card-bg); border: 1px solid var(--border); padding: 12px; border-radius: 15px; text-decoration: none; color: var(--text-main); font-size: 10px; font-weight: 800; transition: 0.2s; }
    .proto-link:hover { border-color: var(--primary); transform: scale(1.05); }

    @media (max-width: 500px) {
        .account-grid { grid-template-columns: 1fr; }
        .elite-header { padding: 80px 15px 150px; }
        .proto-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="elite-header">
    <div class="container animate__animated animate__fadeIn">
        <span class="header-tag">Provisioning Successful</span>
        <h1 class="header-title">Elite Node Deployment</h1>
        <p class="text-white opacity-75 small">Your secure authenticated session has been established.</p>
    </div>
</div>

<main class="main-wrapper">
    <div class="result-card animate__animated animate__fadeInUp">
        <div class="account-grid">
            <div class="grid-tile">
                <div class="tile-icon"><i class="fas fa-server"></i></div>
                <div><span class="tile-label">Node Host</span><div class="tile-val"><?= $data['host'] ?></div></div>
            </div>
            <div class="grid-tile">
                <div class="tile-icon"><i class="fas fa-user-shield"></i></div>
                <div><span class="tile-label">Access ID</span><div class="tile-val"><?= $data['u'] ?></div></div>
            </div>
            <div class="grid-tile">
                <div class="tile-icon"><i class="fas fa-key"></i></div>
                <div><span class="tile-label">Security Key</span><div class="tile-val"><?= $data['p'] ?></div></div>
            </div>
            <div class="grid-tile" style="border-left: 3px solid #ef4444;">
                <div class="tile-icon" style="background:rgba(239,68,68,0.1); color:#ef4444;"><i class="fas fa-calendar-check"></i></div>
                <div><span class="tile-label" style="color:#ef4444;">Expiration</span><div class="tile-val" style="color:#ef4444;"><?= $data['exp'] ?></div></div>
            </div>
        </div>

        <div class="config-area">
            <div class="config-box" id="configContent">◇━━━━━━━━━━━━━━━━━◇
Account ID       :  <?= $data['u'] ?> 
Security Key     :  <?= $data['p'] ?> 
◇━━━━━━━━━━━━━━━━━◇
Host/Ip          :  <?= $data['host'] ?> 
Port OpenSSH     : 443, 80, 22
Port Dropbear    : 443, 109
Port SSH WS      : 80, 8080, 8081-9999 
Port SSH UDP     : 1-65535 
Port SSL/TLS     : 400-900
BadVPN UDP       : 7100, 7300, 7300
◇━━━━━━━━━━━━━━━━━◇
SSH WEBSOCKET    : <?= $data['host'] ?>:80@<?= $data['u'] ?>:<?= $data['p'] ?> 
SSH SSL          : <?= $data['host'] ?>:443@<?= $data['u'] ?>:<?= $data['p'] ?> 
SSH UDP          : <?= $data['host'] ?>:1-65535@<?= $data['u'] ?>:<?= $data['p'] ?> 
◇━━━━━━━━━━━━━━━━━◇
Access Expiry    : <?= $data['exp'] ?> 
◇━━━━━━━━━━━━━━━━━◇</div>
        </div>

        <div class="action-panel">
            <button class="btn-elite btn-primary-pro shadow" id="copyBtn" onclick="copyAccount()">
                <i class="fas fa-copy me-2"></i> Copy Access Details
            </button>
            <a href="../index" class="btn-elite btn-outline-pro">
                <i class="fas fa-th-large me-2"></i> Return to Infrastructure
            </a>
        </div>
    </div>

    <div class="nav-other">
        <span class="nav-label">Explore Protocols</span>
        <div class="proto-grid">
            <a href="../v2ray_vmess" class="proto-link">VMESS NODE</a>
            <a href="../v2ray_vless" class="proto-link">VLESS NODE</a>
            <a href="../v2ray_trojan" class="proto-link">TROJAN NODE</a>
        </div>
    </div>
</main>

<script>
function copyAccount() {
    const text = document.getElementById('configContent').innerText;
    const el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    
    const btn = document.getElementById('copyBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check-circle me-2"></i> Copied to Clipboard!';
    btn.style.background = '#10b981';
    
    setTimeout(() => { 
        btn.innerHTML = originalText;
        btn.style.background = '';
    }, 2500);
}
</script>

<?php include_once '../footer.php'; ?>