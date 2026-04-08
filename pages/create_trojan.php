<?php
// 1. Koneksi & Header (Wajib)
include_once '../config.php';
include_once '../header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$res = $conn->query("SELECT * FROM servers WHERE id = $id");
$s = ($res) ? $res->fetch_assoc() : null;

if (!$s) { 
    echo "<div class='container py-5 mt-5 text-center'><div class='alert alert-danger rounded-4 shadow-sm'><h4>Target node not found!</h4><a href='../v2ray_trojan.php' class='btn btn-danger mt-3 rounded-pill px-4'>Back to Node List</a></div></div>";
    include_once '../footer.php';
    exit; 
}

// --- LOGIKA JATAH HARIAN ---
$used = isset($s['daily_used']) ? (int)$s['daily_used'] : 0;
$max = isset($s['account_limit']) ? (int)$s['account_limit'] : 1; 
$is_full = ($used >= $max); 

// Status Badge - Trojan Red Theme
if ($is_full) {
    $status_badge = "<span class='badge bg-danger border border-white border-opacity-25 px-3 py-2 rounded-pill fw-900 shadow-sm' style='font-size: 9px;'>FULL CAPACITY ($used/$max)</span>";
} else {
    $status_badge = "<span class='badge bg-white text-danger px-3 py-2 rounded-pill fw-900 shadow-sm' style='font-size: 9px;'>AVAILABLE: " . ($max - $used) . " LEFT</span>";
}

$skrg = strtotime(date('Y-m-d'));
$exp_srv = isset($s['date_expired']) ? strtotime($s['date_expired']) : $skrg;
$display_days = ($exp_srv - $skrg > 0) ? ceil(($exp_srv - $skrg) / (60 * 60 * 24)) : 0;
?>

<!-- SEO & META BOOSTER TROJAN -->
<title>V2Ray Trojan Elite Node | Secure Stealth Infrastructure - ZearGames</title>
<meta name="description" content="Deploy premium V2Ray Trojan accounts with Xray-core. High-performance stealth tunneling featuring WebSocket, gRPC, and uncompromised privacy for GFW bypass.">

<style>
    /* --- TROJAN RED THEME SYSTEM --- */
    :root {
        --primary-t: #ef4444; /* Trojan Red */
        --primary-dark-t: #dc2626;
        --bg-site: #f8fafc;
        --bg-card: #ffffff;
        --bg-inner: #fef2f2;
        --text-main: #1e293b;
        --text-muted: #475569;
        --border: #fee2e2;
    }

    body.dark, [data-bs-theme="dark"] body {
        --bg-site: #0f172a;
        --card-bg: #1e293b;
        --bg-inner: rgba(239, 68, 68, 0.05);
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border: rgba(239, 68, 68, 0.1);
    }

    body { background-color: var(--bg-site) !important; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; margin: 0; overflow-x: hidden; }

    /* --- HEADER (TROJAN RED GRADIENT) --- */
    .trojan-header {
        background: linear-gradient(135deg, #991b1b 0%, var(--primary-t) 100%);
        padding: 90px 15px 160px;
        text-align: center;
        clip-path: ellipse(150% 100% at 50% 0%);
    }
    .breadcrumb-nav { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,0.7); margin-bottom: 15px; }
    .breadcrumb-nav a { color: #fff; text-decoration: none; }

    .header-title { font-weight: 900; font-size: clamp(1.6rem, 5vw, 2.8rem); color: #fff; letter-spacing: -1px; margin-bottom: 12px; line-height: 1.2; }
    .header-subtitle { color: rgba(255,255,255,0.9); font-size: 14px; max-width: 650px; margin: 0 auto 30px; line-height: 1.6; }

    .badge-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; }
    .v-pill {
        background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);
        color: #fff; padding: 5px 12px; border-radius: 50px; font-size: 8px; font-weight: 800;
        display: inline-flex; align-items: center; border: 1px solid rgba(255,255,255,0.2);
    }
    .v-pill.active { background: #fff; color: var(--primary-t); }

    /* --- MAIN CARD --- */
    .ocean-container { margin-top: -110px; padding: 0 12px 60px; position: relative; z-index: 50; }
    .ocean-card {
        background: var(--bg-card) !important; border-radius: 28px; max-width: 500px;
        margin: 0 auto; border: 1px solid var(--border); box-shadow: 0 30px 60px rgba(239, 68, 68, 0.1); overflow: hidden;
    }

    .card-head-trojan { background: linear-gradient(to right, var(--primary-t), #f87171); padding: 40px 20px; text-align: center; color: #fff; }
    .flag-img { width: 55px; height: 55px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.4); object-fit: cover; margin-bottom: 12px; }
    .server-label-ws { font-weight: 800; font-size: 18px; display: block; margin-bottom: 5px; }

    .node-detail-box { background: var(--bg-inner); padding: 20px; border-radius: 22px; margin: 20px 15px; border: 1px solid var(--border); }
    .node-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
    .node-icon { font-size: 14px; color: var(--primary-t); width: 20px; text-align: center; margin-top: 2px; }
    .node-label { font-size: 9px; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; display: block; }
    .node-val { font-size: 13px; font-weight: 700; color: var(--text-main); word-break: break-all; display: block; }

    .input-elite { width: 100%; padding: 15px 20px; border-radius: 50px; border: 1px solid var(--border); background: var(--bg-card) !important; color: var(--text-main) !important; font-weight: 600; font-size: 14px; transition: 0.3s; }
    .display-box { background: var(--bg-inner) !important; border: 1px solid var(--border); border-radius: 50px; padding: 12px; font-weight: 800; color: var(--primary-dark-t); text-align: center; font-size: 14px; }
    .btn-generate { background: var(--primary-t); border: none; padding: 18px; border-radius: 50px; color: #fff !important; font-weight: 900; width: 100%; transition: 0.3s; box-shadow: 0 10px 25px rgba(239, 68, 68, 0.2); }

    /* --- PREMIUM INFO --- */
    .premium-info-area { max-width: 1000px; margin: 50px auto 0; padding: 0 15px; }
    .info-label-top { font-size: 11px; font-weight: 800; color: var(--primary-t); text-transform: uppercase; letter-spacing: 2px; text-align: center; display: block; margin-bottom: 10px; }
    .feat-card { background: var(--bg-card); padding: 35px; border-radius: 24px; border: 1px solid var(--border); transition: 0.3s; height: 100%; }
    .feat-icon { width: 45px; height: 45px; background: rgba(239, 68, 68, 0.1); color: var(--primary-t); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 20px; }
    
    .cta-mini-banner { background: linear-gradient(to right, #991b1b, var(--primary-t)); border-radius: 25px; padding: 40px; color: #fff; position: relative; overflow: hidden; margin-bottom: 80px; }
    .btn-cta-white { background: #fff; color: #991b1b !important; font-weight: 800; padding: 12px 30px; border-radius: 50px; text-decoration: none; display: inline-block; position: relative; z-index: 2; transition: 0.3s; }

    .red-alert { background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; border-radius: 20px; padding: 18px; text-align: center; margin: 0 15px 25px; display: none; }
</style>

<section class="trojan-header">
    <div class="container animate__animated animate__fadeIn">
        <nav class="breadcrumb-nav">
            <a href="../index.php">HOME</a> / TROJAN / PROVISIONING
        </nav>
        <h1 class="header-title">V2Ray Trojan Elite Service</h1>
        <p class="header-subtitle">Deploy your secure stealth gateway using the latest Xray-Core Trojan protocol. Engineered for maximum DPI bypass and stability.</p>
        
        <div class="badge-row">
            <div class="v-pill">STEALTH</div>
            <div class="v-pill">WEBSOCKET</div>
            <div class="v-pill">GRPC</div>
            <div class="v-pill">SSL/TLS</div>
            <div class="v-pill active">ANTI-DPI</div>
        </div>
    </div>
</section>

<div class="container ocean-container">
    <div class="ocean-card animate__animated animate__fadeInUp">
        <div class="card-head-trojan">
            <img src="<?= htmlspecialchars($s['flag']) ?>" class="flag-img shadow-sm" alt="Flag">
            <span class="server-label-ws"><?= strtoupper(htmlspecialchars($s['name'])) ?> TROJAN</span>
            <div><?= $status_badge ?></div>
            <!-- RESET SCHEDULE TETAP ADA -->
            <div style="font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.8); margin-top: 10px; text-transform: uppercase; letter-spacing: 1px;">
                <i class="fas fa-history me-1"></i> Auto-Reset: Daily at 00:00 GMT+7
            </div>
        </div>

        <div class="node-detail-box">
            <div class="node-row">
                <i class="fas fa-link node-icon"></i>
                <div class="node-content">
                    <span class="node-label">Target Hostname</span>
                    <span class="node-val"><?= htmlspecialchars($s['ip']) ?></span>
                </div>
            </div>
            <div class="node-row">
                <i class="fas fa-microchip node-icon"></i>
                <div class="node-content">
                    <span class="node-label">Engine Protocol</span>
                    <span class="node-val">Xray-Core Trojan</span>
                </div>
            </div>
        </div>
        
        <div class="px-4 pb-5">
            <!-- BOX PERINGATAN GLOBAL -->
            <div id="devAlertBox" class="red-alert" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 30px; padding: 40px 25px;">
                <i class="fas fa-user-lock mb-2 d-block fa-3x" style="color: #ef4444;"></i>
                <span class="fw-900 d-block" style="color: #ef4444; font-size: 14px; text-transform: uppercase; margin-bottom: 8px;">Security Protocol Active</span>
                <p style="color: #ef4444; font-size: 13px; font-weight: 600; margin: 0;">Batas pembuatan 3 akun (Semua Protokol) tercapai. Silakan kembali besok.</p>
            </div>

            <!-- TAMPILAN PESAN ERROR -->
            <?php if(isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger text-center rounded-pill mb-4 small fw-bold" style="background: rgba(239, 68, 68, 0.1); border: 1px solid #fecaca; color: #ef4444;">
                    <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                </div>
            <?php endif; ?>

            <form action="proses_trojan.php" method="POST" id="trojanForm" onsubmit="return handleTrojanSubmit()">
                <input type="hidden" name="server_id" value="<?= $id ?>">
                <!-- Signature Perangkat -->
                <input type="hidden" name="device_sig" id="device_sig">
                
                <div class="mb-4 px-2">
                    <label class="node-label mb-2" style="display:block;">Identify Remarks (Username)</label>
                    <input type="text" id="username" name="user" class="input-elite" placeholder="Masukkan ID identitas akun Bos" required <?= $is_full ? 'disabled' : '' ?>>
                </div>
                
                <div class="row g-3 mb-4 px-2">
                    <div class="col-6">
                        <label class="node-label mb-2" style="display:block;">Validity</label>
                        <div class="display-box"><?= $display_days ?> Days</div>
                        <input type="hidden" name="duration" value="<?= $display_days ?>">
                    </div>
                    <div class="col-6">
                        <label class="node-label mb-2" style="display:block;">Allocation</label>
                        <div class="display-box"><?= (int)$s['quota_limit'] ?> GB</div>
                        <input type="hidden" name="quota" value="<?= $s['quota_limit'] ?>">
                    </div>
                </div>

                <input type="hidden" name="iplimit" value="<?= $s['device_limit'] ?>">

                <div class="px-2">
                    <button type="submit" id="genBtn" class="btn-generate" <?= $is_full ? 'disabled' : '' ?>>
                        <i class="fas <?= $is_full ? 'fa-ban' : 'fa-bolt' ?> me-2"></i> 
                        <?= $is_full ? 'CAPACITY FULL' : 'GENERATE ACCOUNT' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="premium-info-area animate__animated animate__fadeInUp">
        <span class="info-label-top">Elite Technology</span>
        <h2 class="text-center fw-900 mb-5">Advanced Trojan Features</h2>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="feat-card shadow-sm">
                    <div class="feat-icon"><i class="fas fa-mask"></i></div>
                    <h6>Invisible Camouflage</h6>
                    <p>Protokol Trojan kami meniru trafik HTTPS secara presisi, membuatnya hampir mustahil dibedakan dari trafik web normal oleh sistem sensor.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feat-card shadow-sm">
                    <div class="feat-icon"><i class="fas fa-tachometer-alt"></i></div>
                    <h6>Full Throughput</h6>
                    <p>Didesain untuk efisiensi tinggi, Trojan memberikan kecepatan transmisi data murni tanpa membebani performa CPU perangkat Bos.</p>
                </div>
            </div>
        </div>
        
        <div class="cta-mini-banner">
            <i class="fas fa-globe-americas cta-decor" style="position: absolute; right: -20px; bottom: -20px; font-size: 120px; opacity: 0.1; color: #fff; transform: rotate(-15deg);"></i>
            <h4>Experience Stealth Freedom</h4>
            <p>Deploy your ZearGames Trojan account now and enjoy superior performance with our professionally optimized network nodes.</p>
            <a href="#trojanForm" class="btn-cta-white">Initialize Account</a>
        </div>
    </div>
</div>

<script>
// --- LOGIKA PROTEKSI GLOBAL ZEARGAMES (UNIFIED) ---
document.addEventListener('DOMContentLoaded', function() {
    // 1. Identitas Perangkat Unik
    let sig = localStorage.getItem('zear_vElite_sig');
    if(!sig){
        sig = 'DS-' + Math.random().toString(36).substr(2, 9).toUpperCase() + '-' + Date.now();
        localStorage.setItem('zear_vElite_sig', sig);
    }
    document.getElementById('device_sig').value = sig;

    // 2. Gunakan Kunci Memori Global (Sinkron dengan SSH/VMess/VLess)
    const globalKey = 'zear_global_usage_v1';
    const today = new Date().toDateString();
    
    let devData = JSON.parse(localStorage.getItem(globalKey)) || { date: today, count: 0 };
    
    // Reset harian otomatis
    if (devData.date !== today) {
        devData = { date: today, count: 0 };
        localStorage.setItem(globalKey, JSON.stringify(devData));
    }

    // 3. Blokir Jika Akumulasi Sudah 3 Akun
    if (devData.count >= 3) {
        const formObj = document.getElementById('trojanForm');
        const alertObj = document.getElementById('devAlertBox');
        const btnObj = document.getElementById('genBtn');
        
        if(formObj) formObj.style.display = 'none';
        if(alertObj) alertObj.style.display = 'block';
        if(btnObj) btnObj.disabled = true;
    }
});

// Fungsi saat klik Generate
function handleTrojanSubmit() {
    const globalKey = 'zear_global_usage_v1';
    const today = new Date().toDateString();
    
    // Validasi tambahan khusus username jika diperlukan (opsional)
    const userField = document.getElementById('username');
    if (userField.value.trim().length < 3) {
        alert("Username minimal 3 karakter!");
        userField.focus();
        return false;
    }

    // Update Hitungan Global sebelum submit ke server
    let devData = JSON.parse(localStorage.getItem(globalKey)) || { date: today, count: 0 };
    devData.count += 1;
    localStorage.setItem(globalKey, JSON.stringify(devData));
    
    // Loading Animation
    const btn = document.getElementById('genBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> PROVISIONING...';
    
    return true; 
}
</script>

<?php include_once '../footer.php'; ?>