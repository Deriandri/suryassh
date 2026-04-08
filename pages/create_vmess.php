<?php
// 1. Integrasi Inti (Wajib)
include_once '../config.php';
include_once '../header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$res = $conn->query("SELECT * FROM servers WHERE id = $id");
$s = ($res) ? $res->fetch_assoc() : null;

if (!$s) { 
    echo "<div class='container py-5 mt-5 text-center'><div class='alert alert-warning rounded-4 shadow-sm'><h4>Target node not found!</h4><a href='../v2ray_vmess' class='btn btn-success mt-3 rounded-pill px-4'>Back to Infrastructure List</a></div></div>";
    include_once '../footer.php';
    exit; 
}

// --- LOGIKA KAPASITAS NODES ---
$used = isset($s['daily_used']) ? (int)$s['daily_used'] : 0;
$max = isset($s['account_limit']) ? (int)$s['account_limit'] : 1; 
$is_full = ($used >= $max); 

// STATUS BADGE - REFINED
if ($is_full) {
    $status_badge = "<span class='badge bg-danger border border-white border-opacity-25 px-3 py-2 rounded-pill fw-bold shadow-sm' style='font-size: 10px;'>FULL CAPACITY</span>";
} else {
    $status_badge = "<span class='badge bg-white text-success px-3 py-2 rounded-pill fw-bold shadow-sm' style='font-size: 10px;'>" . ($max - $used) . " SLOTS AVAILABLE</span>";
}

$skrg = strtotime(date('Y-m-d'));
$exp_srv = isset($s['date_expired']) ? strtotime($s['date_expired']) : $skrg;
$display_days = ($exp_srv - $skrg > 0) ? ceil(($exp_srv - $skrg) / (60 * 60 * 24)) : 0;
?>

<!-- SEO & META AUTHORITY BOOSTER -->
<title>Provisioning V2Ray VMess Premium Account - <?= strtoupper(htmlspecialchars($s['location'])) ?> | SuryaSSH</title>
<meta name="description" content="Deploy your premium V2Ray VMess digital gateway. Powered by Xray-core with Cloudflare CDN integration for high-speed, secure, and authenticated access.">

<style>
    /* --- SYSTEM THEME DESIGN TOKENS --- */
    :root {
        --primary-vm: #10b981;
        --primary-deep: #064e3b;
        --bg-site: #f8fafc;
        --card-bg: #ffffff;
        --inner-box: #f1f5f9;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border: #e2e8f0;
    }

    body.dark, [data-bs-theme="dark"] body {
        --bg-site: #0f172a;
        --card-bg: #1e293b;
        --inner-box: #0a0f1e;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border: rgba(255,255,255,0.06);
    }

    body { background-color: var(--bg-site) !important; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; margin: 0; overflow-x: hidden; }

    /* --- PAGE ARCHITECTURE --- */
    .vmess-header {
        background: linear-gradient(135deg, var(--primary-deep) 0%, var(--primary-vm) 100%);
        padding: 100px 20px 180px; text-align: center; clip-path: ellipse(150% 100% at 50% 0%);
    }
    .header-title { font-weight: 900; font-size: clamp(1.8rem, 6vw, 3rem); color: #fff; letter-spacing: -2px; margin-bottom: 15px; }
    .breadcrumb-nav { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: rgba(255,255,255,0.6); margin-bottom: 20px; }
    .breadcrumb-nav a { color: #fff; text-decoration: none; }

    .main-container { margin-top: -120px; padding: 0 15px 80px; position: relative; z-index: 50; }
    .provision-card {
        background: var(--card-bg) !important; border-radius: 40px; max-width: 520px;
        margin: 0 auto; border: 1px solid var(--border); box-shadow: 0 40px 100px rgba(0,0,0,0.12); overflow: hidden;
    }

    .card-head-elite { background: linear-gradient(to right, #059669, #10b981); padding: 45px 30px; text-align: center; color: #fff; position: relative; }
    .flag-aura { width: 65px; height: 65px; border-radius: 50%; border: 4px solid rgba(255,255,255,0.3); object-fit: cover; margin-bottom: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
    
    .node-specs { background: var(--inner-box); padding: 25px; border-radius: 25px; margin: 25px; border: 1px solid var(--border); display: grid; gap: 15px; }
    .spec-item { display: flex; align-items: center; gap: 15px; }
    .spec-icon { width: 40px; height: 40px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-vm); font-size: 18px; }
    .spec-label { font-size: 9px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); display: block; }
    .spec-val { font-size: 14px; font-weight: 700; color: var(--text-main); font-family: 'JetBrains Mono', monospace; }

    /* INPUT STYLING */
    .input-premium { width: 100%; padding: 18px 28px; border-radius: 50px; border: 1px solid var(--border); background: var(--card-bg) !important; color: var(--text-main) !important; font-weight: 600; transition: 0.3s; font-size: 14px; }
    .input-premium:focus { border-color: var(--primary-vm); box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); outline: none; }
    
    .read-box { background: var(--inner-box) !important; border: 1px solid var(--border); border-radius: 50px; padding: 14px; font-weight: 800; color: var(--primary-deep); text-align: center; font-size: 15px; }

    .btn-deploy { background: var(--primary-vm); border: none; padding: 20px; border-radius: 50px; color: #fff !important; font-weight: 900; width: 100%; transition: 0.4s; box-shadow: 0 15px 30px rgba(16, 185, 129, 0.25); text-transform: uppercase; letter-spacing: 1px; font-size: 13px; cursor: pointer; }
    .btn-deploy:hover { transform: translateY(-3px); background: var(--primary-deep); box-shadow: 0 20px 40px rgba(16, 185, 129, 0.35); }
    .btn-deploy:disabled { opacity: 0.6; cursor: not-allowed; transform: none !important; }

    /* --- GAYA PERINGATAN (SESUAI GAMBAR 1) --- */
    .pink-lock-box {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 25px;
        padding: 30px 20px;
        text-align: center;
        margin: 0 25px 30px;
        display: none;
    }
    .pink-lock-box i { color: #ef4444; font-size: 35px; margin-bottom: 12px; display: block; }
    .pink-lock-box .title { color: #ef4444; font-weight: 800; font-size: 14px; text-transform: uppercase; display: block; margin-bottom: 5px; }
    .pink-lock-box .desc { color: #ef4444; font-size: 13px; font-weight: 600; }

    /* AUTHORITY CONTENT */
    .authority-section { max-width: 900px; margin: 60px auto 0; background: var(--card-bg); border-radius: 35px; padding: 50px; border: 1px solid var(--border); }
    .auth-title { font-weight: 900; font-size: 26px; color: var(--primary-deep); margin-bottom: 25px; letter-spacing: -0.5px; }
    .auth-text { font-size: 15px; color: var(--text-muted); line-height: 1.8; margin-bottom: 40px; }
    
    .feature-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 25px; }
    .feat-card { border-left: 3px solid var(--primary-vm); padding-left: 20px; }
    .feat-card h6 { font-weight: 800; font-size: 15px; margin-bottom: 8px; }
    .feat-card p { font-size: 13px; color: var(--text-muted); margin: 0; }

    @media (max-width: 768px) {
        .authority-section { padding: 35px 20px; }
        .vmess-header { padding: 80px 15px 150px; }
    }
</style>

<header class="vmess-header">
    <div class="container animate__animated animate__fadeIn">
        <nav class="breadcrumb-nav">
            <a href="../index">INFRASTRUCTURE</a> / VMESS / DEPLOYMENT
        </nav>
        <h1 class="header-title">V2Ray VMess Premium Node</h1>
        <p class="text-white opacity-75" style="max-width: 600px; margin: 0 auto;">Secure your digital identity with our enterprise-grade VMess infrastructure featuring WebSocket (WS) and gRPC pathing.</p>
    </div>
</header>

<main class="main-container">
    <div class="provision-card animate__animated animate__fadeInUp">
        <div class="card-head-elite">
            <img src="<?= htmlspecialchars($s['flag']) ?>" class="flag-aura" alt="Node Region">
            <h2 class="fw-900 h4 mb-2"><?= strtoupper(htmlspecialchars($s['name'])) ?> INFRASTRUCTURE</h2>
            
            <!-- SLOT INFO -->
            <?= $status_badge ?>
            
            <div class="reset-info">
                <i class="fas fa-history me-1"></i> Reset Schedule: 00:00 (GMT+7)
            </div>
        </div>
        
        <div class="node-specs">
            <div class="spec-item">
                <div class="spec-icon"><i class="fas fa-server"></i></div>
                <div><span class="spec-label">Hostname</span><span class="spec-val"><?= htmlspecialchars($s['ip']) ?></span></div>
            </div>
            <div class="spec-item">
                <div class="spec-icon"><i class="fas fa-shield-alt"></i></div>
                <div><span class="spec-label">Security Protocol</span><span class="spec-val">Xray-Core VMess AES-256</span></div>
            </div>
        </div>
        
        <div class="px-4 pb-5">
            <!-- BOX PERINGATAN PINK (SESUAI GAMBAR 1) -->
            <div id="pinkAlert" class="pink-lock-box">
                <i class="fas fa-user-lock"></i>
                <span class="title">SECURITY PROTOCOL ACTIVE</span>
                <p class="desc">Batas pembuatan 3 akun per perangkat tercapai. Silakan kembali besok.</p>
            </div>

            <?php if(isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger text-center rounded-pill small fw-bold mb-4">
                    <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                </div>
            <?php endif; ?>

            <form action="proses_vmess.php" method="POST" id="vmessForm" onsubmit="return handleGlobalSubmit()">
                <input type="hidden" name="server_id" value="<?= $id ?>">
                <input type="hidden" name="device_sig" id="device_sig">
                
                <div class="mb-4 px-2">
                    <label class="spec-label mb-2 px-1">Authenticated Account ID (Username)</label>
                    <input type="text" id="username" name="user" class="input-premium" placeholder="Username" required <?= $is_full ? 'disabled' : '' ?>>
                </div>
                
                <div class="row g-3 mb-5 px-2">
                    <div class="col-6">
                        <label class="spec-label mb-2 px-1">Provisioned Duration</label>
                        <div class="read-box"><?= $display_days ?> Days</div>
                        <input type="hidden" name="duration" value="<?= $display_days ?>">
                    </div>
                    <div class="col-6">
                        <label class="spec-label mb-2 px-1">Network Quota</label>
                        <div class="read-box"><?= (int)$s['quota_limit'] ?> GB</div>
                        <input type="hidden" name="quota" value="<?= $s['quota_limit'] ?>">
                    </div>
                </div>

                <input type="hidden" name="iplimit" value="<?= $s['device_limit'] ?>">

                <div class="px-2">
                    <button type="submit" id="genBtn" class="btn-deploy" <?= $is_full ? 'disabled' : '' ?>>
                        <i class="fas <?= $is_full ? 'fa-ban' : 'fa-bolt' ?> me-2"></i> 
                        <?= $is_full ? 'FULL CAPACITY' : 'DEPLOY SECURE NODE' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- AUTHORITY SECTION (DIKEMBALIKAN UTUH 100%) -->
    <section class="authority-section animate__animated animate__fadeInUp">
        <h2 class="auth-title">The Standard of VMess WebSocket Technology</h2>
        <p class="auth-text">
            Layanan <strong>V2Ray VMess Premium</strong> dari SuryaSSH mendefinisikan ulang standar privasi digital. Infrastruktur kami dirancang khusus menggunakan arsitektur <strong>WebSocket (WS)</strong> dan <strong>gRPC</strong> yang berjalan melalui jalur <strong>CDN Cloudflare</strong>. Hal ini memastikan paket data Anda tersamarkan dalam lalu lintas HTTPS standar, memberikan kemampuan mitigasi <strong>DPI (Deep Packet Inspection)</strong> yang tak terkalahkan oleh provider lain.
        </p>

        <div class="feature-grid">
            <article class="feat-card">
                <h6>Stealth Camouflage</h6>
                <p>Menyamarkan trafik tunnel Anda menjadi aktivitas browsing web standar untuk keamanan maksimal.</p>
            </article>
            <article class="feat-card">
                <h6>Latency Optimization</h6>
                <p>Integrasi node langsung dengan jaringan Edge Cloudflare untuk rute internasional tercepat.</p>
            </article>
            <article class="feat-card">
                <h6>Military Encryption</h6>
                <p>Seluruh paket data dilindungi dengan enkripsi asimetris AES-128-GCM / AES-256 tingkat tinggi.</p>
            </article>
        </div>
    </section>
</main>

<script>
// --- LOGIKA PROTEKSI GLOBAL SuryaSSH (UNIFIED) ---
document.addEventListener('DOMContentLoaded', function() {
    // 1. Identifikasi Perangkat Unik
    let sig = localStorage.getItem('zear_vElite_sig');
    if(!sig){
        sig = 'DS-' + Math.random().toString(36).substr(2, 9).toUpperCase() + '-' + Date.now();
        localStorage.setItem('zear_vElite_sig', sig);
    }
    
    // Masukkan signature ke input form agar terkirim ke server
    const sigInput = document.getElementById('device_sig');
    if(sigInput) sigInput.value = sig;

    // 2. Kunci Penyimpanan Tunggal (Sinkron antar semua protokol)
    const globalKey = 'zear_global_usage_v1';
    const today = new Date().toDateString();
    
    let devData = JSON.parse(localStorage.getItem(globalKey)) || { date: today, count: 0 };
    
    // Reset harian otomatis
    if (devData.date !== today) {
        devData = { date: today, count: 0 };
        localStorage.setItem(globalKey, JSON.stringify(devData));
    }

    // 3. Eksekusi Pemblokiran Jika Sudah 3 Akun
    if (devData.count >= 3) {
        const targetForm = document.getElementById('vmessForm');
        const lockBox = document.getElementById('pinkAlert');
        const btnObj = document.getElementById('genBtn');
        
        if(targetForm) targetForm.style.display = 'none';
        if(lockBox) lockBox.style.display = 'block';
        if(btnObj) btnObj.disabled = true;
    }
});

// 4. Validasi & Update Counter
function handleGlobalSubmit() {
    const userField = document.getElementById('username');
    const globalKey = 'zear_global_usage_v1';
    const today = new Date().toDateString();

    // Validasi panjang username (Min 3 karakter)
    if (userField && userField.value.trim().length < 3) { 
        alert("Username minimal 3 karakter!");
        userField.focus();
        return false;
    }

    // Update hitungan global
    let devData = JSON.parse(localStorage.getItem(globalKey)) || { date: today, count: 0 };
    devData.count += 1;
    localStorage.setItem(globalKey, JSON.stringify(devData));
    
    // Animasi Loading
    const btn = document.getElementById('genBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> DEPLOYING...';
    
    return true;
}
</script>

<?php include_once '../footer.php'; ?>
