<?php 
// 1. Koneksi & Header (Wajib)
include_once 'config.php'; 
include_once 'header.php'; 

if (!$conn) {
    echo "<div class='container mt-5 pt-5'><div class='alert alert-danger text-center rounded-4 shadow-sm'>Database Connection Failure.</div></div>";
    include_once 'footer.php';
    exit;
}
?>

<!-- SEO META TAGS & BOOSTER -->
<title>V2Ray VLESS Premium - High Speed & Secure Node Infrastructure | SuryaSSH</title>
<meta name="description" content="Akses infrastruktur V2Ray VLESS Premium dengan optimasi Xray Core. Dukungan Reality, XTLS-Vision, dan gRPC untuk koneksi tunneling tercepat dan paling stabil.">
<meta name="keywords" content="V2Ray VLESS, Vless Reality, XTLS Vision, gRPC Tunnel, SuryaSSH, Internet Cepat, Bypass DPI">

<style>
    /* --- SYSTEM THEME ADAPTIVE --- */
    :root {
        --primary-v: #f59e0b; /* Amber/Orange for VLESS */
        --primary-dark: #d97706;
        --bg-site: #f8fafc;
        --bg-card: #ffffff;
        --bg-inner: #f1f5f9;
        --text-main: #1e293b;
        --text-muted: #475569;
        --border: #e2e8f0;
    }

    /* DARK MODE AUTOMATIC DETECTION */
    body.dark, [data-bs-theme="dark"] body {
        --bg-site: #0f172a;
        --bg-card: #1e293b;
        --bg-inner: #0f172a;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border: rgba(255,255,255,0.06);
    }

    body { background-color: var(--bg-site) !important; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; margin: 0; overflow-x: hidden; }

    /* Hero Section */
    .vless-hero {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, var(--bg-site) 100%);
        padding: 100px 20px 80px;
        text-align: center;
        border-bottom: 1px solid var(--border);
        margin-bottom: 60px;
        border-radius: 0 0 60px 60px;
    }
    .vless-title { font-weight: 800; font-size: clamp(2.2rem, 7vw, 3rem); letter-spacing: -2px; margin-bottom: 15px; }
    .vless-desc { max-width: 750px; margin: 0 auto 35px; font-size: 15.5px; line-height: 1.8; color: var(--text-muted); }

    /* Info Badge Pills */
    .badge-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 30px; }
    .v-pill {
        background: var(--primary-v); color: #fff; padding: 6px 14px; border-radius: 50px; font-size: 9px; font-weight: 800;
        display: inline-flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 1px;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2); border: 1px solid rgba(255,255,255,0.1);
    }
    .v-pill span { background: #fff; color: var(--primary-v); padding: 2px 8px; border-radius: 50px; font-weight: 900; }

    /* --- SERVER CARDS REFINED --- */
    .v-card { 
        background: var(--bg-card) !important; border-radius: 30px; padding: 35px 25px; 
        border: 1px solid var(--border); text-align: center; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
        height: 100%; display: flex; flex-direction: column; justify-content: space-between;
    }
    .v-card:hover { transform: translateY(-12px); border-color: var(--primary-v); box-shadow: 0 25px 50px rgba(245, 158, 11, 0.12); }

    /* --- FIX BUTTON ANDROID (No Reducing, Just Polishing) --- */
    .btn-container { width: 100%; padding-top: 15px; margin-top: auto; }
    .v-btn { 
        background: var(--primary-v) !important; 
        color: #fff !important; 
        font-weight: 800; 
        border-radius: 50px !important; 
        padding: 14px 10px; 
        font-size: 11px; 
        border: none; 
        transition: 0.3s; 
        letter-spacing: 1px;
        display: block;
        width: 100%;
        text-align: center;
        text-decoration: none;
        box-shadow: 0 8px 15px rgba(245, 158, 11, 0.2);
    }
    .v-btn:hover { transform: scale(1.03); filter: brightness(1.1); }
    .v-btn.disabled { background: #94a3b8 !important; opacity: 0.6; pointer-events: none; }

    /* Status Indicator */
    .status-led { font-size: 9px; font-weight: 800; padding: 5px 14px; border-radius: 50px; text-transform: uppercase; color: #fff; }
    .led-online { background: #22c55e; box-shadow: 0 0 10px rgba(34, 197, 94, 0.4); }
    .led-offline { background: #ef4444; }

    /* --- TECHNICAL INSIGHTS GRID (RE-ADDED) --- */
    .section-label { font-size: 11px; font-weight: 800; color: var(--primary-v); text-transform: uppercase; letter-spacing: 3px; display: block; margin-bottom: 12px; }
    .section-title { font-weight: 800; font-size: 28px; color: var(--text-main); margin-bottom: 45px; }

    .insight-card { background: var(--bg-card); padding: 35px; border-radius: 28px; border: 1px solid var(--border); height: 100%; transition: 0.3s; }
    .insight-card h5 { font-weight: 800; font-size: 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; color: var(--text-main); }
    .insight-card i { color: var(--primary-v); font-size: 20px; }
    .insight-card p { font-size: 13.5px; color: var(--text-muted); line-height: 1.7; }
    
    .check-list { list-style: none; padding: 0; margin-top: 20px; }
    .check-list li { font-size: 12.5px; color: var(--text-muted); margin-bottom: 10px; display: flex; align-items: flex-start; gap: 10px; }
    .check-list li i { font-size: 12px; margin-top: 3px; color: var(--primary-v); }

    /* Deep Dive Area (RE-ADDED) */
    .deep-box { background: var(--bg-card); border-radius: 35px; padding: 50px; border: 1px solid var(--border); margin: 60px auto; max-width: 1000px; }
    .deep-header { border-bottom: 1px solid var(--border); padding-bottom: 30px; margin-bottom: 35px; }

    /* Setup Process (RE-ADDED) */
    .step-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 40px; }
    .step-item { background: var(--bg-inner); padding: 35px 25px; border-radius: 28px; text-align: center; border: 1px solid var(--border); }
    .step-circle { width: 45px; height: 45px; background: var(--primary-v); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; margin: 0 auto 20px; box-shadow: 0 8px 16px rgba(245, 158, 11, 0.3); }

    @media (max-width: 768px) { 
        .v-card { padding: 25px 15px; border-radius: 24px; }
        .v-btn { font-size: 10px; padding: 12px 5px; }
        .v-pill { font-size: 8px; }
        .deep-box { padding: 30px 20px; }
        .section-title { font-size: 22px; }
    }
</style>

<main>
    <!-- HERO SECTION -->
    <section class="vless-hero">
        <div class="container">
            <h1 class="vless-title">V2Ray VLESS Elite Node</h1>
            <p class="vless-desc">SuryaSSH menyajikan infrastruktur <span class="fw-bold text-warning">VLESS</span> tercanggih dengan arsitektur rendah overhead. Nikmati transmisi data yang lebih efisien dan hemat daya.</p>
            
            <div class="badge-row">
                <div class="v-pill">REALITY</div>
                <div class="v-pill">XTLS-VISION</div>
                <div class="v-pill">GRPC</div>
                <div class="v-pill">WS/HTTP</div>
                <div class="v-pill active">99.9% UPTIME</div>
            </div>
        </div>
    </section>

    <!-- SERVER LISTING GRID -->
    <section class="container pb-5">
        <div class="row g-3 g-md-4">
            <?php
            $servers = $conn->query("SELECT * FROM servers WHERE type = 'vless' ORDER BY id DESC");
            if ($servers && $servers->num_rows > 0):
                while($s = $servers->fetch_assoc()):
                    // PERBAIKAN: Timeout dinaikkan ke 2.0 agar status ONLINE akurat
                    $is_online = @fsockopen(trim($s['ip']), 443, $errno, $errstr, 2.0); 
            ?>
            <article class="col-6 col-md-4 col-lg-3">
                <div class="v-card shadow-sm">
                    <div>
                        <div class="mb-3">
                            <span class="status-led <?= $is_online ? 'led-online' : 'led-offline' ?>">
                                <i class="fas fa-bolt me-1"></i> <?= $is_online ? 'ACTIVE' : 'OFFLINE' ?>
                            </span>
                        </div>
                        <img src="<?= $s['flag'] ?>" alt="Flag" class="rounded-circle mb-3 shadow-sm border" style="width: 50px; height: 50px; object-fit: cover;">
                        <h2 class="h6 fw-800 mb-1" style="font-size: 14px;"><?= strtoupper(htmlspecialchars($s['name'])) ?></h2>
                        <p class="text-muted small mb-0" style="font-size: 11px;"><i class="fas fa-map-marker-alt me-1 text-warning"></i> <?= htmlspecialchars($s['location']) ?></p>
                    </div>
                    <div class="btn-container">
                        <hr class="opacity-10 mb-3">
                        <a href="pages/create_vless.php?id=<?= $s['id'] ?>" class="v-btn <?= $is_online ? '' : 'disabled' ?>">
                            <?= $is_online ? 'SELECT SERVER' : 'MAINTENANCE' ?>
                        </a>
                    </div>
                </div>
            </article>
            <?php if($is_online) fclose($is_online); endwhile; endif; ?>
        </div>
    </section>

    <!-- TECHNICAL INSIGHTS GRID -->
    <section class="container py-5">
        <div class="text-center">
            <span class="section-label">Technical Insights</span>
            <h2 class="section-title">Enforced Network Performance</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="insight-card shadow-sm">
                    <h5><i class="fas fa-microchip"></i> Node Optimization</h5>
                    <p>Infrastruktur VLESS kami meminimalkan beban komputasi pada sisi klien. Protokol ini mengalirkan data tanpa dekripsi internal berganda.</p>
                    <ul class="check-list">
                        <li><i class="fas fa-check-circle"></i> Konsumsi Baterai Rendah</li>
                        <li><i class="fas fa-check-circle"></i> Low Latency Gaming</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="insight-card shadow-sm">
                    <h5><i class="fas fa-fingerprint"></i> UUID Authentication</h5>
                    <p>Verifikasi identitas menggunakan standar UUIDv4 yang tidak bergantung pada sinkronisasi waktu ketat server.</p>
                    <ul class="check-list">
                        <li><i class="fas fa-check-circle"></i> Otentikasi Instan</li>
                        <li><i class="fas fa-check-circle"></i> Keamanan UUIDv4</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="insight-card shadow-sm">
                    <h5><i class="fas fa-mask"></i> Stealth Camouflage</h5>
                    <p>Mengintegrasikan Reality dan XTLS-Vision guna menyamarkan sidik jari TLS untuk trafik web normal.</p>
                    <ul class="check-list">
                        <li><i class="fas fa-check-circle"></i> Mitigasi DPI</li>
                        <li><i class="fas fa-check-circle"></i> XTLS Reality Support</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- DEEP-DIVE EXPLORATION -->
        <div class="deep-box shadow-sm animate__animated animate__fadeInUp">
            <div class="deep-header">
                <span class="section-label" style="text-align: left;">Deep-Dive Architecture</span>
                <h3 class="fw-800">V2Ray VLESS Structural Analysis</h3>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-7">
                    <h6 class="fw-800 text-warning mb-3">VLESS Fundamental Logic</h6>
                    <p class="small text-muted" style="line-height: 1.8;">VLESS (V-Less Encryption) menghilangkan enkripsi tradisional yang sering menyebabkan overhead. Dengan menyerahkan keamanan sepenuhnya pada TLS, VLESS mampu memindahkan data dengan throughput yang jauh lebih bersih bagi SuryaSSH user.</p>
                </div>
                <div class="col-lg-5">
                    <div class="p-4 rounded-4 border" style="background: var(--bg-inner);">
                        <h6 class="fw-800 mb-3" style="font-size: 13px;"><i class="fas fa-laptop-code me-2 text-warning"></i> Multi-Client Ready</h6>
                        <ul class="check-list mb-0">
                            <li><i class="fas fa-desktop"></i> Windows: v2rayN</li>
                            <li><i class="fas fa-mobile-alt"></i> Android: v2rayNG</li>
                            <li><i class="fas fa-apple"></i> iOS: Shadowrocket</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- SETUP PROCESS -->
            <div class="mt-5 pt-5 border-top text-center">
                <span class="section-label">Getting Started</span>
                <h4 class="fw-800 mb-4">Activation Workflow</h4>
                <div class="step-grid">
                    <div class="step-item">
                        <div class="step-circle">1</div>
                        <h6 class="fw-800">Node Selection</h6>
                        <p class="small text-muted mb-0">Pilih lokasi server terdekat untuk latensi terendah.</p>
                    </div>
                    <div class="step-item">
                        <div class="step-circle">2</div>
                        <h6 class="fw-800">Provision Account</h6>
                        <p class="small text-muted mb-0">Buat identitas akun unik melalui sistem deployment instan.</p>
                    </div>
                    <div class="step-item">
                        <div class="step-circle">3</div>
                        <h6 class="fw-800">Enjoy Freedom</h6>
                        <p class="small text-muted mb-0">Impor konfigurasi dan nikmati internet tanpa batas.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>
