<?php
// 1. Panggil file dari ROOT
include_once '../config.php';
include_once '../header.php';

if (!$conn) {
    echo "<div class='container py-5 mt-5'><div class='alert alert-danger text-center rounded-4 shadow-sm'>Database Connection Failure.</div></div>";
    include_once '../footer.php';
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$res = $conn->query("SELECT * FROM servers WHERE id = $id");
$s = ($res) ? $res->fetch_assoc() : null;

if (!$s) { 
    echo "<div class='container py-5 mt-5 text-center'><div class='alert alert-warning rounded-4 shadow-sm'><h4>Target Node Not Found!</h4></div></div>";
    include_once '../footer.php';
    exit; 
}

// --- LOGIKA JATAH HARIAN SERVER ---
$used = isset($s['daily_used']) ? (int)$s['daily_used'] : 0;
$max = isset($s['account_limit']) ? (int)$s['account_limit'] : 1; 
$is_full = ($used >= $max); 

// Logika Masa Aktif
$skrg = strtotime(date('Y-m-d'));
$exp_srv = isset($s['date_expired']) ? strtotime($s['date_expired']) : $skrg;
$diff = $exp_srv - $skrg;
$display_days = ($diff > 0) ? ceil($diff / (60 * 60 * 24)) : 0;
?>

<style>
    /* --- SYSTEM THEME ADAPTIVE --- */
    :root {
        --primary: #2563eb;
        --primary-dark: #1e3a8a;
        --bg-site: #f8fafc;
        --card-bg: #ffffff;
        --inner-box: #f1f5f9;
        --text-main: #1e293b;
        --text-muted: #475569;
        --border: #e2e8f0;
    }

    /* DARK MODE AUTOMATIC DETECTION */
    body.dark, [data-bs-theme="dark"] body {
        --bg-site: #0f172a;
        --card-bg: #1e293b;
        --inner-box: #0f172a;
        --text-main: #f8fafc;
        --text-muted: #cbd5e1;
        --border: rgba(255,255,255,0.06);
    }

    body { background-color: var(--bg-site) !important; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; margin: 0; }

    /* Header Section */
    .page-header {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        padding: 90px 20px 160px;
        text-align: center;
        clip-path: ellipse(150% 100% at 50% 0%);
    }
    .header-title { font-weight: 800; font-size: clamp(2rem, 6vw, 2.8rem); color: #fff; letter-spacing: -1.5px; margin-bottom: 15px; }
    .header-subtitle { color: rgba(255,255,255,0.9); font-size: 15px; max-width: 700px; margin: 0 auto; line-height: 1.6; }

    /* BADGE CONTAINER */
    .badge-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 30px; max-width: 1000px; margin-left: auto; margin-right: auto; }
    .badge-pill {
        background: var(--primary); color: #fff; padding: 6px 12px; border-radius: 50px; font-size: 9px; font-weight: 800;
        display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2); border: 1px solid rgba(255,255,255,0.1); margin-bottom: 5px;
    }
    .badge-pill span { background: #fff; color: var(--primary); padding: 2px 8px; border-radius: 50px; font-weight: 900; }

    /* Main Card Container */
    .main-wrapper { margin-top: -110px; padding: 0 15px 60px; position: relative; z-index: 50; }
    .creation-card {
        background: var(--card-bg) !important; border-radius: 35px; max-width: 550px; margin: 0 auto;
        border: 1px solid var(--border); box-shadow: 0 30px 60px rgba(0,0,0,0.1); overflow: hidden;
    }

    /* Banner area */
    .elite-banner { background: linear-gradient(to right, #2563eb, #7c3aed); padding: 40px 20px; text-align: center; color: #fff; }
    .flag-circle { width: 55px; height: 55px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.25); object-fit: cover; margin-bottom: 15px; }
    .status-badge-mini { background: white; color: var(--primary); padding: 5px 15px; border-radius: 10px; font-size: 10px; font-weight: 800; display: inline-block; margin-top: 10px; }
    .reset-info { font-size: 10px; font-weight: 600; opacity: 0.85; margin-top: 8px; display: block; }

    /* Inputs */
    .label-pro { font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1px; margin-bottom: 8px; display: block; padding-left: 15px; }
    .input-pro {
        width: 100%; padding: 16px 25px; border-radius: 50px; border: 1px solid var(--border);
        background: var(--inner-box) !important; color: var(--text-main) !important; font-weight: 600; transition: 0.3s; margin-bottom: 5px;
    }
    .input-readonly {
        background: var(--inner-box) !important; border: 1px solid var(--border); border-radius: 50px;
        padding: 12px; font-weight: 700; color: var(--text-main); text-align: center; font-size: 14px;
    }

    .btn-submit-pro {
        background: var(--primary); color: #fff !important; border: none; padding: 18px; width: 100%;
        border-radius: 50px; font-weight: 800; font-size: 14px; letter-spacing: 1px; transition: 0.3s;
    }

    /* Professional Content Box */
    .info-container { max-width: 900px; margin: 60px auto 0; background: var(--card-bg) !important; border-radius: 30px; padding: 40px; border: 1px solid var(--border); }
    .intro-text { font-size: 13.5px; color: var(--text-muted); line-height: 1.8; border-bottom: 1px solid var(--border); padding-bottom: 25px; margin-bottom: 25px; }
    .pro-f-item h6 { font-weight: 800; font-size: 14px; margin-bottom: 8px; color: var(--primary); }
    .pro-f-item p { font-size: 12px; color: var(--text-muted); line-height: 1.6; margin: 0; }

    .alert-limit { background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; border-radius: 20px; padding: 20px; text-align: center; margin-bottom: 25px; display: none; }
</style>

<header class="page-header">
    <div class="container">
        <h1 class="header-title">SSH All In One Premium</h1>
        <p class="header-subtitle">Nikmati kebebasan akses internet dengan infrastruktur multi-protokol ZearGames yang stabil, aman, dan dioptimalkan untuk performa maksimal.</p>
        
        <div class="badge-row">
            <div class="badge-pill">SUPPORT <span>WEBSOCKET</span></div>
            <div class="badge-pill">SUPPORT <span>UDP CUSTOM</span></div>
            <div class="badge-pill">SUPPORT <span>STUNNEL</span></div>
            <div class="badge-pill">SUPPORT <span>DROPBEAR</span></div>
            <div class="badge-pill">SUPPORT <span>OPENSSH</span></div>
            <div class="badge-pill">SUPPORT <span>SSL/TLS</span></div>
            <div class="badge-pill">SUPPORT <span>HTTP</span></div>
            <div class="badge-pill">UPTIME <span>99.9%</span></div>
        </div>
    </div>
</header>

<main class="main-wrapper">
    <div class="creation-card animate__animated animate__fadeInUp">
        <div class="elite-banner">
            <img src="<?= htmlspecialchars($s['flag']) ?>" class="flag-circle" alt="Flag">
            <h5 class="fw-800 mb-1"><?= strtoupper(htmlspecialchars($s['name'])) ?> WS</h5>
            
            <div class="status-badge-mini">
               Server Status: <?= (int)$used ?> / <?= (int)$max ?> Slots
            </div>
            
            <div class="reset-info">
                <i class="fas fa-history me-1"></i> Reset Schedule: 00:00 (GMT+7)
            </div>
        </div>

        <div class="p-4 p-md-5">
            <div id="deviceAlert" class="alert-limit">
                <i class="fas fa-user-lock fa-2x mb-2 d-block"></i>
                <small class="fw-800 d-block">SECURITY PROTOCOL ACTIVE</small>
                <small>Batas pembuatan 3 akun per perangkat tercapai. Silakan kembali besok.</small>
            </div>

            <?php if(isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger rounded-4 text-center mb-4 small fw-bold">
                    <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
                </div>
            <?php endif; ?>

            <!-- MODIFIKASI: Input Device Sig Ditambahkan & onsubmit handleValidation -->
            <form action="proses_ssh.php" method="POST" id="sshForm" onsubmit="return handleValidation()">
                <input type="hidden" name="server_id" value="<?= $id ?>">
                <input type="hidden" name="device_sig" id="device_sig">
                
                <div class="mb-4">
                    <label class="label-pro">Access Identity (Username)</label>
                    <input type="text" id="username" name="user" class="input-pro" value="surya_ssh-" required <?= $is_full ? 'disabled' : '' ?>>
                </div>

                <div class="mb-4">
                    <label class="label-pro">Secure Key (Password)</label>
                    <input type="password" name="pass" class="input-pro" placeholder="Create a secure password" required <?= $is_full ? 'disabled' : '' ?>>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <label class="label-pro">Validity</label>
                        <div class="input-readonly"><?= $display_days ?> Days</div>
                        <input type="hidden" name="duration" value="<?= $display_days ?>">
                    </div>
                    <div class="col-6">
                        <label class="label-pro">Device Limit</label>
                        <div class="input-readonly"><?= (int)$s['device_limit'] ?> Sessions</div>
                    </div>
                </div>

                <button type="submit" id="subBtn" class="btn-submit-pro shadow-lg" <?= $is_full ? 'disabled' : '' ?>>
                    <i class="fas <?= $is_full ? 'fa-ban' : 'fa-bolt' ?> me-2"></i> 
                    <?= $is_full ? 'CAPACITY REACHED' : 'INITIALIZE ACCOUNT' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Kotak Info di Bawah Card -->
    <div class="badge-row mt-4">
        <div class="badge-pill" style="background:rgba(37,99,235,0.05); color:var(--primary); border-color:var(--primary);">Active valid for 30 Days</div>
        <div class="badge-pill">Enterprise Secure Shell SSL/TLS</div>
        <div class="badge-pill">High-Speed CDN Connection</div>
        <div class="badge-pill" style="background:rgba(37,99,235,0.05); color:var(--primary); border-color:var(--primary);">Mask Your Real IP</div>
    </div>

    <div class="info-container">
        <p class="intro-text">
            <strong>Enterprise Infrastructure:</strong> Sistem ZearGames All-In-One SSH mengintegrasikan berbagai lapisan keamanan termasuk enkripsi asimetris dan protokol obfuscation. Kami menjamin privasi total dan kecepatan tanpa batas melalui jalur perutean cerdas global.
        </p>

        <h3 style="font-weight: 800; font-size: 18px; margin-bottom: 25px; color: var(--primary);">Infrastructure Security Features</h3>
        <div class="row g-4">
            <div class="col-md-6 pro-f-item">
                <h6><i class="fas fa-shield-alt me-2"></i> Multi-Protocol Stacks</h6>
                <p>Mendukung Stunnel untuk enkripsi SSL tambahan dan SSH Websocket guna menembus sensor internet pada jaringan korporat.</p>
            </div>
            <div class="pro-f-item col-md-6">
                <h6><i class="fas fa-user-shield me-2"></i> Zero-Activity Logging</h6>
                <p>Kebijakan privasi ketat yang menjamin tidak ada pencatatan trafik browsing pengguna demi keamanan identitas digital Anda.</p>
            </div>
            <div class="pro-f-item col-md-6">
                <h6><i class="fas fa-microchip me-2"></i> High-Speed Packet Processing</h6>
                <p>Menggunakan node dengan spesifikasi tinggi untuk memproses enkripsi data secara real-time tanpa menurunkan kualitas bandwidth.</p>
            </div>
            <div class="pro-f-item col-md-6">
                <h6><i class="fas fa-network-wired me-2"></i> Dynamic CDN Pathing</h6>
                <p>Integrasi dengan jaringan distribusi konten global guna meminimalkan latensi dan memaksimalkan stabilitas rute internasional.</p>
            </div>
        </div>
    </div>
</main>

<script>
// CONFIGURASI PROTOKOL
const USER_PREFIX = "surya_ssh-"; 

document.addEventListener('DOMContentLoaded', function() {
    // 1. Identifikasi Perangkat Unik
    let sig = localStorage.getItem('zear_vElite_sig');
    if(!sig){
        sig = 'DS-' + Math.random().toString(36).substr(2, 9).toUpperCase() + '-' + Date.now();
        localStorage.setItem('zear_vElite_sig', sig);
    }
    const sigInput = document.getElementById('device_sig');
    if(sigInput) sigInput.value = sig;

    // 2. Cek Limit Global (Kunci Tunggal: zear_global_usage_v1)
    const globalKey = 'zear_global_usage_v1';
    const today = new Date().toDateString();
    
    let devData = JSON.parse(localStorage.getItem(globalKey)) || { date: today, count: 0 };
    
    // Reset harian
    if (devData.date !== today) {
        devData = { date: today, count: 0 };
        localStorage.setItem(globalKey, JSON.stringify(devData));
    }

    // 3. Blokir jika sudah 3 akun
    if (devData.count >= 3) {
        const formObj = document.getElementById('sshForm');
        const alertObj = document.getElementById('deviceAlert');
        const btnObj = document.getElementById('subBtn');
        
        if(formObj) formObj.style.display = 'none';
        if(alertObj) alertObj.style.display = 'block';
        if(btnObj) btnObj.disabled = true;
    }
});

// 4. Konsolidasi Fungsi Validasi & Penambahan Hitungan
function handleValidation() {
    const userField = document.getElementById('username');
    const globalKey = 'zear_global_usage_v1';
    const today = new Date().toDateString();
    
    // Validasi Panjang Username
    if (userField.value.trim().length < (USER_PREFIX.length + 3)) {
        alert("Username minimal tambah 3 karakter!");
        userField.focus();
        return false;
    }

    // Update Hitungan Global
    let devData = JSON.parse(localStorage.getItem(globalKey)) || { date: today, count: 0 };
    devData.count += 1;
    localStorage.setItem(globalKey, JSON.stringify(devData));
    
    // Animasi Loading
    const btn = document.getElementById('subBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> INITIALIZING...';
    
    return true; 
}
</script>

<?php include_once '../footer.php'; ?>