<?php
session_start(); 
include_once '../config.php';

/**
 * -------------------------------------------------------
 * GATE 1: PROSES EKSEKUSI DATA (POST)
 * -------------------------------------------------------
 */
$today = date('Y-m-d'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!$conn) { die("Koneksi Database Gagal"); }

    if (!isset($_POST['server_id'], $_POST['user'])) {
        header("Location: ../index");
        exit;
    }

    $id      = (int)$_POST['server_id']; 
    $u       = mysqli_real_escape_string($conn, strtolower(trim($_POST['user']))); 
    $p       = "vmess_user"; 
    $day     = isset($_POST['duration']) ? (int)$_POST['duration'] : 30; 
    $quota   = isset($_POST['quota']) ? (int)$_POST['quota'] : 0;
    $iplimit = isset($_POST['iplimit']) ? (int)$_POST['iplimit'] : 2;
    $ip_user = $_SERVER['REMOTE_ADDR']; 
    $device_sig = isset($_POST['device_sig']) ? mysqli_real_escape_string($conn, $_POST['device_sig']) : 'UNKNOWN';

    // --- PROTEKSI GLOBAL ---
    if ($device_sig !== 'UNKNOWN') {
        $check_limit = $conn->query("SELECT COUNT(*) as total FROM accounts WHERE device_id = '$device_sig' AND date_created = '$today'");
        if ($check_limit) {
            $res_limit = $check_limit->fetch_assoc();
            if ($res_limit['total'] >= 3) {
                $_SESSION['error_msg'] = "Limit Perangkat Tercapai! (Maks 3 akun harian).";
                header("Location: create_vmess.php?id=$id");
                exit;
            }
        }
    }

    // --- CEK USERNAME ---
    $check_user = $conn->query("SELECT id FROM accounts WHERE username = '$u'");
    if ($check_user && $check_user->num_rows > 0) {
        $_SESSION['error_msg'] = "Username <b>$u</b> sudah aktif!";
        header("Location: create_vmess.php?id=$id");
        exit;
    }

    $res = $conn->query("SELECT * FROM servers WHERE id = $id");
    $s   = $res->fetch_assoc();

    if ($s) {
        $ssh = @ssh2_connect($s['ip'], 22);
        if ($ssh && @ssh2_auth_password($ssh, 'root', $s['password'])) {
            
            $command = "printf \"2\\n3\\n$u\\n$quota\\n$iplimit\\n$day\\n\" | menu"; 
            $stream = @ssh2_exec($ssh, $command);
            stream_set_blocking($stream, true);
            $backup_out = stream_get_contents($stream);
            fclose($stream);
            
            sleep(2);
            
            $stream2 = @ssh2_exec($ssh, "cat /var/www/html/vmess-$u.txt");
            $raw_vps = "";
            if ($stream2) {
                stream_set_blocking($stream2, true);
                $raw_vps = stream_get_contents($stream2);
                fclose($stream2);
            }

            $source = (!empty(trim($raw_vps))) ? $raw_vps : $backup_out;
            $clean = preg_replace('/\[[0-9;]*m/', '', $source); 

            // --- EXTRAKSI DATA UNTUK TEMPLATE BOS ---
            $uuid = ""; $l_tls = ""; $l_ntls = ""; $l_grpc = "";
            $exp_formatted = date('d M, Y', strtotime("+$day days"));
            $quota_view = ($quota == 0) ? "Unlimited" : $quota . " GB";

            if (preg_match('/(?:id|uuid)\s*:\s*([a-f0-9\-]{36})/i', $clean, $m)) $uuid = trim($m[1]);
            preg_match_all('/vmess:\/\/\S+/', $clean, $matches);
            if (isset($matches[0][0])) $l_tls = $matches[0][0];
            if (isset($matches[0][1])) $l_ntls = $matches[0][1];
            if (isset($matches[0][2])) $l_grpc = $matches[0][2];

            // --- TEMPLATE CUSTOM SESUAI PERMINTAAN BOS ---
            $box_content = "◇━━━━━━━━━━━━━━━━━◇\n";
            $box_content .= "      XRAY/VMESS\n";
            $box_content .= "◇━━━━━━━━━━━━━━━━━◇\n";
            $box_content .= "Remarks   : $u\n";
            $box_content .= "Domain    : {$s['ip']}\n";
            $box_content .= "Limit Quota : $quota_view\n";
            $box_content .= "id        : $uuid\n";
            $box_content .= "◇━━━━━━━━━━━━━━━━━◇\n";
            $box_content .= "Link TLS : \n$l_tls\n";
            $box_content .= "◇━━━━━━━━━━━━━━━━━◇\n";
            $box_content .= "Link NTLS : \n$l_ntls\n";
            $box_content .= "◇━━━━━━━━━━━━━━━━━◇\n";
            $box_content .= "Link GRPC : \n$l_grpc\n";
            $box_content .= "◇━━━━━━━━━━━━━━━━━◇\n";
            $box_content .= "Aktif Selama : $day Hari\n";
            $box_content .= "Berakhir Pada : $exp_formatted\n";
            $box_content .= "◇━━━━━━━━━━━━━━━━━◇";

            // Simpan ke DB
            $exp_db = date('Y-m-d', strtotime("+$day days"));
            $conn->query("INSERT INTO accounts (username, password, vps_ip, protocol, created_by_ip, device_id, date_created, date_expired) 
                          VALUES ('$u', '$p', '{$s['ip']}', 'vmess', '$ip_user', '$device_sig', '$today', '$exp_db')");
            $conn->query("UPDATE servers SET daily_used = daily_used + 1 WHERE id = $id");

            $secure_token = bin2hex(random_bytes(16));
            $_SESSION['vmess_success_token'] = $secure_token;
            $_SESSION['vmess_result'] = [
                'u' => $u,
                'config' => $box_content,
                'host' => $s['ip'],
                'exp' => $exp_formatted
            ];

            header("Location: proses_vmess.php?status=success&auth=" . $secure_token);
            exit;
        } else {
            $_SESSION['error_msg'] = "Gagal Koneksi VPS!";
            header("Location: create_vmess.php?id=$id");
            exit;
        }
    }
}

/**
 * -------------------------------------------------------
 * GATE 2: TAMPILAN HASIL SUKSES (ELITE UI)
 * -------------------------------------------------------
 */
$url_auth = isset($_GET['auth']) ? $_GET['auth'] : '';
$ses_auth = isset($_SESSION['vmess_success_token']) ? $_SESSION['vmess_success_token'] : '';

if (!isset($_GET['status']) || $_GET['status'] !== 'success' || empty($ses_auth) || $url_auth !== $ses_auth) {
    header("Location: ../index");
    exit;
}

$data = $_SESSION['vmess_result'];
include_once '../header.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');
    :root { --primary: #10b981; --bg: #f8fafc; --card: #ffffff; --text: #1e293b; --muted: #64748b; --border: #e2e8f0; --terminal-bg: #f1f5f9; --terminal-text: #064e3b; }
    body.dark { --bg: #0f172a; --card: #1e293b; --text: #f1f5f9; --muted: #94a3b8; --border: rgba(255,255,255,0.06); --terminal-bg: #0a0f1e; --terminal-text: #10b981; }
    body { background-color: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; transition: background 0.4s ease; margin: 0; }
    .premium-header { background: linear-gradient(135deg, #059669 0%, #10b981 100%); padding: 60px 20px 140px; text-align: center; clip-path: ellipse(150% 100% at 50% 0%); }
    .premium-title { font-size: 26px; font-weight: 800; color: #fff; letter-spacing: -1px; margin-bottom: 8px; }
    .status-pill { background: rgba(255,255,255,0.2); backdrop-filter: blur(5px); color: #fff; padding: 8px 20px; border-radius: 50px; font-size: 10px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 1px; }
    .container-res { margin-top: -100px; padding: 0 15px 60px; position: relative; z-index: 50; }
    .card-modern { background: var(--card); border-radius: 35px; max-width: 550px; margin: 0 auto 40px; border: 1px solid var(--border); box-shadow: 0 40px 80px rgba(0,0,0,0.1); overflow: hidden; }
    .info-section { padding: 30px; display: grid; gap: 12px; }
    .info-tile { background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 20px; padding: 15px 20px; display: flex; align-items: center; gap: 15px; }
    .tile-icon { width: 42px; height: 42px; background: rgba(16, 185, 129, 0.1); border-radius: 14px; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 18px; }
    .tile-label { font-size: 9px; color: var(--muted); font-weight: 800; text-transform: uppercase; margin-bottom: 2px; }
    .tile-value { font-size: 14px; font-weight: 700; color: var(--text); word-break: break-all; }
    .box-terminal { background: var(--terminal-bg); padding: 25px; border-radius: 25px; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--terminal-text); border: 1px solid var(--border); margin: 0 30px 30px; text-align: left; line-height: 1.7; white-space: pre-wrap; word-break: break-all; }
    .btn-return { display: flex; align-items: center; justify-content: center; gap: 10px; width: calc(100% - 60px); margin: 0 30px 35px; padding: 18px; background: var(--primary); color: #fff; text-decoration: none; border-radius: 22px; font-weight: 800; font-size: 14px; transition: 0.3s; }
</style>

<div class="premium-header">
    <h1 class="premium-title">ENTERPRISE NODE ACCESS</h1>
    <p class="text-white opacity-75 small">Your secure tunnel configuration has been successfully provisioned.</p>
    <div class="status-pill"><i class="fas fa-shield-alt"></i> Access Secured</div>
</div>

<div class="container-res">
    <div class="card-modern animate__animated animate__fadeInUp">
        <div class="info-section">
            <div class="info-tile">
                <div class="tile-icon"><i class="fas fa-network-wired"></i></div>
                <div><div class="tile-label">Node Infrastructure</div><div class="tile-value"><?= htmlspecialchars($data['host']) ?></div></div>
            </div>
            <div class="info-tile">
                <div class="tile-icon"><i class="fas fa-user-check"></i></div>
                <div><div class="tile-label">Authenticated User</div><div class="tile-value"><?= htmlspecialchars($data['u']) ?></div></div>
            </div>
            <div class="info-tile" style="border-left: 4px solid #ef4444;">
                <div class="tile-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="fas fa-calendar-check"></i></div>
                <div><div class="tile-label" style="color: #ef4444;">Access Expiration</div><div class="tile-value" style="color: #ef4444;"><?= $data['exp'] ?></div></div>
            </div>
        </div>
        <div class="box-terminal"><?= htmlspecialchars($data['config']) ?></div>
        <a href="../index.php" class="btn-return"><i class="fas fa-rocket"></i> FINISH & RETURN TO DASHBOARD</a>
    </div>
</div>

<script>
    if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark');
</script>

<?php include_once '../footer.php'; ?>