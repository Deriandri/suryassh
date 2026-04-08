<?php
// 1. Koneksi & Header (Wajib)
include_once '../config.php';
include_once '../header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$res = $conn->query("SELECT * FROM servers WHERE id = $id");
$s = ($res) ? $res->fetch_assoc() : null;

if (!$s) { 
    echo "<div class='container py-5 mt-5 text-center'><div class='alert alert-warning rounded-4 shadow-sm'><h4>Target node not found!</h4><a href='../v2ray_vless.php' class='btn btn-warning mt-3 rounded-pill px-4'>Back to Server List</a></div></div>";
    include_once '../footer.php';
    exit; 
}

// --- LOGIKA JATAH HARIAN ---
$used = isset($s['daily_used']) ? (int)$s['daily_used'] : 0;
$max = isset($s['account_limit']) ? (int)$s['account_limit'] : 1; 
$is_full = ($used >= $max); 

// Status Badge - Kontras Tinggi (VLESS Amber Theme)
if ($is_full) {
    $status_badge = "<span class='badge bg-danger border border-white border-opacity-25 px-3 py-2 rounded-pill fw-900 shadow-sm' style='font-size: 9px;'>FULL CAPACITY ($used/$max)</span>";
} else {
    $status_badge = "<span class='badge bg-white text-warning px-3 py-2 rounded-pill fw-900 shadow-sm' style='font-size: 9px;'>AVAILABLE: " . ($max - $used) . " LEFT</span>";
}

$skrg = strtotime(date('Y-m-d'));
$exp_srv = isset($s['date_expired']) ? strtotime($s['date_expired']) : $skrg;
$display_days = ($exp_srv - $skrg > 0) ? ceil(($exp_srv - $skrg) / (60 * 60 * 24)) : 0;
?>

<title>V2Ray VLESS Elite Node | Next-Gen Tunneling Infrastructure - ZearGames</title>
<meta name="description" content="Deploy premium V2Ray VLESS accounts with Xray-core. High-performance tunneling featuring Reality, XTLS, and uncompromised privacy.">

<style>
    /* --- VLESS AMBER THEME SYSTEM --- */
    :root {
        --primary-v: #f59e0b; /* Amber VLESS */
        --primary-dark-v: #d97706;
        --bg-site: #f8fafc;
        --bg-card: #ffffff;
        --bg-inner: #fef3c7; /* Soft Amber Inner */
        --text-main: #1e293b;
        --text-muted: #475569;
        --border: #fde68a;
    }

    body.dark, [data-bs-theme="dark"] body {
        --bg-site: #0f172a;
        --card-bg: #1e293b;
        --bg-inner: rgba(245, 158, 11, 0.05);
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border: rgba(245, 158, 11, 0.1);
    }

    body { background-color: var(--bg-site) !important; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; margin: 0; overflow-x: hidden; }

    /* --- HEADER (VLESS AMBER GRADIENT) --- */
    .vless-header {
        background: linear-gradient(135deg, var(--primary-dark-v) 0%, var(--primary-v) 100%);
        padding: 90px 15px 160px;
        text-align: center;
        clip-path: ellipse(150% 100% at 50% 0%);
    }
    .breadcrumb-nav { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,0.7); margin-bottom: 15px; }
    .breadcrumb-nav a { color: #fff; text-decoration: none; }

    .header-title { font-weight: 900; font-size: clamp(1.6rem, 5vw, 2.8rem); color: #fff; letter-spacing: -1px; margin-bottom: 12px; line-height: 1.2; }
    .header-subtitle { color: rgba(255,255,255,0.9); font-size: 14px; max-width: 650px; margin: 0 auto 30px; line-height: 1.6; }

    /* Badge Pills (VLESS Style) */
    .badge-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin: 0 auto; max-width: 100%; }
    .v-pill {
        background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);
        color: #fff; padding: 5px 12px; border-radius: 50px; font-size: 8px; font-weight: 800;
        display: inline-flex; align-items: center; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.2);
    }
    .v-pill.active { background: #fff; color: var(--primary-v); }

    /* --- MAIN CARD --- */
    .ocean-container { margin-top: -110px; padding: 0 12px 60px; position: relative; z-index: 50; }
    .ocean-card {
        background: var(--bg-card) !important; border-radius: 28px; max-width: 500px;
        margin: 0 auto; border: 1px solid var(--border); box-shadow: 0 30px 60px rgba(245, 158, 11, 0.1); overflow: hidden;
    }

    .card-head-vless { background: linear-gradient(to right, var(--primary-v), #fbbf24); padding: 40px 20px; text-align: center; color: #fff; }
    .flag-img { width: 55px; height: 55px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.4); object-fit: cover; margin-bottom: 12px; }
    .server-label-ws { font-weight: 800; font-size: 18px; display: block; margin-bottom: 5px; }
    .reset-timer { font-size: 10px; opacity: 0.9; margin-top: 10px; display: block; }

    /* Node List Detail (Amber Accents) */
    .node-detail-box { background: var(--bg-inner); padding: 20px; border-radius: 22px; margin: 20px 15px; border: 1px solid var(--border); }
    .node-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
    .node-row:last-child { margin-bottom: 0; }
    .node-icon { font-size: 14px; color: var(--primary-dark-v); width: 20px; text-align: center; margin-top: 2px; }
    .node-content { flex: 1; min-width: 0; }
    .node-label { font-size: 9px; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; display: block; }
    .node-val { font-size: 13px; font-weight: 700; color: var(--text-main); word-break: break-all; line-height: 1.3; display: block; }

    /* Form Kuning VLESS */
    .input-elite { width: 100%; padding: 15px 20px; border-radius: 50px; border: 1px solid var(--border); background: var(--bg-card) !important; color: var(--text-main) !important; font-weight: 600; font-size: 14px; transition: 0.3s; }
    .input-elite:focus { border-color: var(--primary-v); box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); outline: none; }
    .display-box { background: var(--bg-inner) !important; border: 1px solid var(--border); border-radius: 50px; padding: 12px; font-weight: 800; color: var(--primary-dark-v); text-align: center; font-size: 14px; }
    .btn-generate { background: var(--primary-v); border: none; padding: 18px; border-radius: 50px; color: #fff !important; font-weight: 900; width: 100%; transition: 0.3s; box-shadow: 0 10px 25px rgba(245, 158, 11, 0.2); }
    .btn-generate:hover { background: var(--primary-dark-v); transform: translateY(-2px); }

    /* --- PREMIUM INFO AREA --- */
    .premium-info-area { max-width: 1000px; margin: 50px auto 0; padding: 0 15px; }
    .info-label-top { font-size: 11px; font-weight: 800; color: var(--primary-v); text-transform: uppercase; letter-spacing: 2px; text-align: center; display: block; margin-bottom: 10px; }
    .info-title-top { font-size: clamp(1.5rem, 4vw, 2.2rem); font-weight: 900; text-align: center; margin-bottom: 45px; letter-spacing: -1px; }

    .feat-card { background: var(--bg-card); padding: 35px; border-radius: 24px; border: 1px solid var(--border); transition: 0.3s; height: 100%; }
    .feat-card:hover { transform: translateY(-8px); border-color: var(--primary-v); }
    .feat-icon { width: 45px; height: 45px; background: rgba(245, 158, 11, 0.1); color: var(--primary-v); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 20px; }
    .feat-card h6 { font-weight: 800; font-size: 16px; margin-bottom: 12px; }
    .feat-list li::before { content: "\f058"; font-family: "Font Awesome 5 Free"; font-weight: 900; color: var(--primary-v); font-size: 10px; }

    .insight-block { background: var(--bg-card); border-radius: 35px; padding: 45px; border: 1px solid var(--border); margin-bottom: 60px; }
    .insight-title { font-weight: 800; font-size: 24px; color: var(--primary-v); margin-bottom: 25px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
    .comp-card { background: var(--bg-inner); padding: 30px; border-radius: 24px; border: 1px solid var(--border); }
    .comp-card h6 i { color: var(--primary-v); }

    .cta-mini-banner { background: linear-gradient(to right, var(--primary-dark-v), #fbbf24); border-radius: 25px; padding: 40px; color: #fff; position: relative; overflow: hidden; margin-bottom: 80px; }
    .btn-cta-white { background: #fff; color: #primary-dark-v !important; font-weight: 800; padding: 12px 30px; border-radius: 50px; text-decoration: none; display: inline-block; position: relative; z-index: 2; transition: 0.3s; }

    .red-alert { background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; border-radius: 20px; padding: 18px; text-align: center; margin: 0 15px 25px; display: none; }
</style>

<section class="vless-header">
    <div class="container animate__animated animate__fadeIn">
        <nav class="breadcrumb-nav">
            <a href="../index.php">HOME</a> / VLESS / PROVISIONING
        </nav>
        <h1 class="header-title">V2Ray VLESS Elite Service</h1>
        <p class="header-subtitle">Deploy your secure digital gateway using next-generation Xray-Core protocols. Optimized for maximum privacy and blazing speeds.</p>
        
        <div class="badge-row">
            <div class="v-pill">WEBSOCKET</div>
            <div class="v-pill">REALITY</div>
            <div class="v-pill">XTLS-VISION</div>
            <div class="v-pill">GRPC</div>
            <div class="v-pill">SSL/TLS</div>
            <div class="v-pill">HTTP</div>
            <div class="v-pill active">99.9% UPTIME</div>
        </div>
    </div>
</section>

<div class="container ocean-container">
    <div class="ocean-card animate__animated animate__fadeInUp">
        <div class="card-head-vless">
            <img src="<?= htmlspecialchars($s['flag']) ?>" class="flag-img shadow-sm" alt="Flag">
            <span class="server-label-ws"><?= strtoupper(htmlspecialchars($s['name'])) ?> WS</span>
            <div><?= $status_badge ?></div>
            <span class="reset-timer"><i class="fas fa-history me-1"></i> Reset Schedule: 00:00 (GMT+7)</span>
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
                <i class="fas fa-globe-asia node-icon"></i>
                <div class="node-content">
                    <span class="node-label">Geographical Node</span>
                    <span class="node-val"><?= htmlspecialchars($s['location']) ?></span>
                </div>
            </div>
            <div class="node-row">
                <i class="fas fa-microchip node-icon"></i>
                <div class="node-content">
                    <span class="node-label">Engine Protocol</span>
                    <span class="node-val">Xray-Core VLESS</span>
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

            <?php if(isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger text-center rounded-4 mb-4 small fw-bold">
                    <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                </div>
            <?php endif; ?>

            <form action="proses_vless.php" method="POST" id="vlessForm" onsubmit="return handleVlessSubmit()">
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
        <span class="info-label-top">Advanced Technology</span>
        <h2 class="info-title-top">Elite VLESS Infrastructure Features</h2>

        <div class="feat-grid">
            <div class="feat-card shadow-sm">
                <div class="feat-icon"><i class="fas fa-layer-group"></i></div>
                <h6>Streamlined Architecture</h6>
                <p>Implementasi VLESS kami membuang beban enkripsi internal yang tidak perlu, menghasilkan transmisi data yang lebih murni.</p>
                <ul class="feat-list p-0" style="list-style:none;">
                    <li><small class="text-muted">Zero system time dependency</small></li>
                    <li><small class="text-muted">Reduced protocol overhead</small></li>
                </ul>
            </div>
            <div class="feat-card shadow-sm">
                <div class="feat-icon"><i class="fas fa-user-shield"></i></div>
                <h6>Adaptive Cryptography</h6>
                <p>Menghadirkan opsi enkripsi fleksibel yang menyeimbangkan antara keamanan militer dan performa kecepatan tinggi.</p>
                <ul class="feat-list p-0" style="list-style:none;">
                    <li><small class="text-muted">TLS 1.3 native integration</small></li>
                    <li><small class="text-muted">Reality stealth technology</small></li>
                </ul>
            </div>
        </div>
        
        <div class="insight-block mt-4 shadow-sm">
            <h4 class="insight-title">Decoding VLESS Technology</h4>
            <p class="insight-text text-muted">
                VLESS adalah protokol transmisi teringan dari ekosistem V2Ray yang memecahkan batasan efisiensi pada protokol tradisional. ZearGames mengintegrasikan teknologi ini dengan <strong>XTLS-Vision</strong> guna menjamin anonimitas total Bos.
            </p>
            <div class="cta-mini-banner">
                <i class="fas fa-globe-americas cta-decor"></i>
                <h4>Experience Next-Gen Tunneling</h4>
                <p>Enjoy superior performance with our professionally optimized VLESS network nodes.</p>
                <a href="#vlessForm" class="btn-cta-white">Initialize Account</a>
            </div>
        </div>
    </div>
</div>

<script>
// --- LOGIKA PROTEKSI GLOBAL ZEARGAMES (UNIFIED) ---
document.addEventListener('DOMContentLoaded', function() {
    // 1. Identifikasi Perangkat Unik
    let sig = localStorage.getItem('zear_vElite_sig');
    if(!sig){
        sig = 'DS-' + Math.random().toString(36).substr(2, 9).toUpperCase() + '-' + Date.now();
        localStorage.setItem('zear_vElite_sig', sig);
    }
    document.getElementById('device_sig').value = sig;

    // 2. Kunci Penyimpanan Tunggal (Berbagi data antar semua protokol)
    const globalKey = 'zear_global_usage_v1';
    const today = new Date().toDateString();
    
    let devData = JSON.parse(localStorage.getItem(globalKey)) || { date: today, count: 0 };
    
    // Reset otomatis jika sudah ganti hari
    if (devData.date !== today) {
        devData = { date: today, count: 0 };
        localStorage.setItem(globalKey, JSON.stringify(devData));
    }

    // 3. Eksekusi Pemblokiran Jika Sudah 3 Akun
    if (devData.count >= 3) {
        document.getElementById('vlessForm').style.display = 'none';
        document.getElementById('devAlertBox').style.display = 'block';
    }
});

// Fungsi saat klik Generate
function handleVlessSubmit() {
    const userField = document.getElementById('username');
    const globalKey = 'zear_global_usage_v1';
    const today = new Date().toDateString();

    // Validasi panjang username (Min 3 Karakter)
    if (userField.value.trim().length < 3) { 
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> PROVISIONING...';
    
    return true;
}
</script>

<?php include_once '../footer.php'; ?>