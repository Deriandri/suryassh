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
    // Hitung semua akun dari perangkat ini HARI INI tanpa peduli jenis protokolnya (Menggunakan device_id)
    $check_dev = $conn->query("SELECT COUNT(*) as total FROM accounts WHERE device_id = '$device_sig' AND date_created = '$today'");
    if ($check_dev) {
        $dev_log = $check_dev->fetch_assoc();
        if ($dev_log['total'] >= 3) {
            $_SESSION['error_msg'] = "Limit Perangkat Tercapai! (Maks 3 akun harian lintas protokol).";
            header("Location: " . $_SERVER['HTTP_REFERER']); 
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!isset($_GET['status']) || $_GET['status'] !== 'success' || !isset($_SESSION['trojan_result'])) {
        header("Location: ../v2ray_trojan.php"); 
        exit;
    }
}

// 2. LOGIKA PEMBUATAN AKUN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['user']) || empty($_POST['user'])) {
        header("Location: ../v2ray_trojan.php");
        exit;
    }

    $id = (int)$_POST['server_id']; 
    $u  = mysqli_real_escape_string($conn, strtolower(trim($_POST['user']))); 
    $d  = (int)$_POST['duration']; 
    $q  = (int)$_POST['quota'];
    $ip_lim = (int)$_POST['iplimit'];

    // --- PROTEKSI USERNAME DUPLIKAT ---
    $check_user = $conn->query("SELECT id FROM accounts WHERE username = '$u'");
    if ($check_user && $check_user->num_rows > 0) {
        $_SESSION['error_msg'] = "The username '$u' is already active in our database.";
        header("Location: create_trojan.php?id=$id");
        exit;
    }

    $res = $conn->query("SELECT * FROM servers WHERE id = $id");
    $s = $res->fetch_assoc();

    if ($s) {
        $ssh = @ssh2_connect($s['ip'], 22);
        
        if (!$ssh) {
            $_SESSION['error_msg'] = "Gagal menghubungi server! Pastikan VPS sedang aktif/online.";
            header("Location: create_trojan.php?id=$id");
            exit;
        }

        if (!@ssh2_auth_password($ssh, 'root', $s['password'])) {
            $_SESSION['error_msg'] = "Autentikasi VPS Gagal! Periksa password root di panel admin.";
            header("Location: create_trojan.php?id=$id");
            exit;
        }

        // JALANKAN PERINTAH JIKA KONEKSI BERHASIL
        $command = "printf \"$u\\n$q\\n$ip_lim\\n$d\\n\" | /usr/local/sbin/add-tro";
        $stream = @ssh2_exec($ssh, $command);
        
        if ($stream) {
            stream_set_blocking($stream, true);
            $output_exec = stream_get_contents($stream); 
            fclose($stream);
            
            sleep(1); 
            
            $get_config = "cat /var/www/html/trojan-$u.txt";
            $stream_cat = @ssh2_exec($ssh, $get_config);
            
            if ($stream_cat) {
                stream_set_blocking($stream_cat, true);
                $raw_config = stream_get_contents($stream_cat);
                fclose($stream_cat);
                
                if ($raw_config && strlen($raw_config) > 50) {
                    if (strpos($raw_config, 'TROJAN') !== false) {
                        $raw_config = substr($raw_config, strpos($raw_config, 'TROJAN'));
                    }
                    
                    $expiry_pos = strpos($raw_config, 'Expired');
                    if ($expiry_pos !== false) {
                        $next_divider = strpos($raw_config, '◇━━━━━━━━━━━━━━━━━◇', $expiry_pos);
                        if ($next_divider !== false) {
                            $raw_config = substr($raw_config, 0, $next_divider + strlen('◇━━━━━━━━━━━━━━━━━◇'));
                        }
                    }

                    $exp_db = date('Y-m-d', strtotime("+$d days"));
                    // SIMPAN KE DATABASE (Termasuk device_id untuk proteksi harian)
                    $conn->query("INSERT INTO accounts (username, vps_ip, protocol, created_by_ip, device_id, date_created, date_expired) 
                                  VALUES ('$u', '{$s['ip']}', 'trojan', '{$_SERVER['REMOTE_ADDR']}', '$device_sig', '$today', '$exp_db')");
                    
                    $conn->query("UPDATE servers SET daily_used = daily_used + 1 WHERE id = $id");

                    $_SESSION['trojan_result'] = [
                        'user'   => $u,
                        'config' => trim($raw_config),
                        'exp'    => date('d M, Y', strtotime($exp_db))
                    ];

                    header("Location: proses_trojan.php?status=success");
                    exit;
                } else {
                    $_SESSION['error_msg'] = "Sistem gagal mengambil data config dari VPS.";
                }
            }
        } else {
            $_SESSION['error_msg'] = "Gagal menjalankan skrip pada server VPS.";
        }
    }
    header("Location: create_trojan.php?id=$id"); 
    exit;
}

// --- TAMPILAN HASIL (ELITE UI - TIDAK BERUBAH) ---
if (isset($_GET['status']) && $_GET['status'] == 'success' && isset($_SESSION['trojan_result'])) {
    $data = $_SESSION['trojan_result'];
    include_once '../header.php';
?>

<style>
    :root { --primary-t: #ef4444; --bg-site: #f8fafc; --bg-card: #ffffff; --bg-content: #f1f5f9; --text-main: #1e293b; --border: #e2e8f0; }
    body.dark, [data-bs-theme="dark"] body { --bg-site: #0f172a; --bg-card: #1e293b; --bg-content: rgba(15, 23, 42, 0.6); --text-main: #f1f5f9; --border: rgba(255,255,255,0.06); }
    body { background-color: var(--bg-site) !important; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; overflow-x: hidden; }
    .result-container { padding: 40px 12px 100px; }
    .result-card { max-width: 600px; margin: 0 auto; background: var(--bg-card); border-radius: 30px; overflow: hidden; border: 1px solid var(--border); box-shadow: 0 40px 80px rgba(0,0,0,0.1); }
    .banner-elite { background: linear-gradient(135deg, #991b1b 0%, var(--primary-t) 100%); padding: 35px 20px; text-align: center; color: #fff; }
    .meta-bar { display: flex; justify-content: center; gap: 15px; padding: 15px; background: rgba(239, 68, 68, 0.05); border-bottom: 1px solid var(--border); font-size: 11px; font-weight: 800; text-transform: uppercase; }
    .meta-bar span { color: var(--primary-t); }
    .content-box { margin: 25px 15px; padding: 25px; border-radius: 20px; background: var(--bg-content); border: 1px solid var(--border); }
    .config-pre { width: 100%; color: var(--text-main); font-family: 'SFMono-Regular', Consolas, monospace; font-size: 11px; line-height: 1.6; white-space: pre-wrap; word-break: break-all; margin: 0; }
    .btn-home-pro { padding: 15px 40px; border-radius: 50px; font-weight: 800; font-size: 12px; text-transform: uppercase; text-decoration: none; text-align: center; background: var(--bg-content); color: var(--text-main) !important; border: 1px solid var(--border); display: inline-block; margin-bottom: 35px; width: 100%; max-width: 250px; transition: 0.3s; }
    .btn-home-pro:hover { transform: translateY(-3px); border-color: var(--primary-t); }
    .p-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; max-width: 600px; margin: 50px auto; }
    .p-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 15px; text-align: center; transition: 0.3s; }
    .p-btn { border-radius: 50px; padding: 8px; font-size: 9px; font-weight: 900; color: #fff !important; text-decoration: none; display: block; }
</style>

<div class="container result-container">
    <div class="result-card animate__animated animate__fadeIn">
        <div class="banner-elite">
            <h5 class="fw-900 mb-0">.::. ALWARI STORE .::.</h5>
            <p class="mb-0 opacity-75 small">Trojan Profile Deployed Successfully</p>
        </div>
        <div class="meta-bar">
            <div>User: <span><?= $data['user'] ?></span></div>
            <div>Expiry: <span><?= $data['exp'] ?></span></div>
        </div>
        <div class="content-box">
            <div id="finalConfig" class="config-pre"><?= htmlspecialchars($data['config']) ?></div>
        </div>
        <div class="text-center px-4">
            <a href="../index.php" class="btn-home-pro">Return Home</a>
        </div>
    </div>

    <div class="p-grid">
        <div class="p-card shadow-sm"><i class="fas fa-terminal text-primary"></i><a href="../ssh_websocket.php" class="p-btn bg-primary">Pilih SSH</a></div>
        <div class="p-card shadow-sm"><i class="fas fa-server text-success"></i><a href="../v2ray_vmess.php" class="p-btn bg-success">Pilih VMESS</a></div>
        <div class="p-card shadow-sm"><i class="fas fa-bolt text-warning"></i><a href="../v2ray_vless.php" class="p-btn bg-warning">Pilih VLESS</a></div>
    </div>
</div>

<?php 
    unset($_SESSION['trojan_result']); 
    include_once '../footer.php'; 
} else { header("Location: ../v2ray_trojan.php"); exit; }
?>