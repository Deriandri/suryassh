<?php 
// 1. Koneksi & Header (Wajib)
include_once 'config.php'; 
include_once 'header.php'; 

if (!$conn) {
    echo "<div class='container mt-5 pt-5'><div class='alert alert-danger text-center rounded-4 shadow-sm'>Database Connection Error.</div></div>";
    include_once 'footer.php';
    exit;
}

/**
 * SEO BOOSTER BLOCK
 */
?>
<!-- SEO Meta Tags Tambahan -->
<title>SSH Websocket (WS) Premium - UDP Custom, SlowDNS & OpenVPN | SuryaSSH</title>
<meta name="description" content="Layanan SSH Websocket (WS) Premium dengan dukungan UDP Custom, SlowDNS, Stunnel, dan OpenVPN. Nikmati koneksi stabil dengan CDN Cloudflare dan enkripsi tingkat tinggi.">
<meta name="keywords" content="SSH Websocket, SSH WS, UDP Custom, SSH SlowDNS, OpenVPN, Stunnel, Cloudflare CDN, Tunneling, SuryaSSH">

<style>
    /* --- SYSTEM THEME ADAPTIVE (FIX DARK MODE) --- */
    :root {
        --primary: #3b82f6;
        --p-soft: rgba(59, 130, 246, 0.1);
        --bg-body: #f8fafc;
        --bg-card: #ffffff;
        --text-main: #1e293b;   
        --text-muted: #475569;  
        --border: #e2e8f0;
    }

    /* Pendeteksi Otomatis untuk Dark Mode */
    body.dark, [data-bs-theme="dark"] body {
        --bg-body: #0f172a;
        --bg-card: #1e293b;
        --text-main: #f8fafc;   
        --text-muted: #94a3b8;  
        --border: rgba(255,255,255,0.06);
    }

    body { background-color: var(--bg-body) !important; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; transition: background 0.3s, color 0.3s; margin: 0; }
    
    /* Hero Section - Gradasi Mengikuti Tema */
    .ssh-hero {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, var(--bg-body) 100%);
        padding: 90px 20px 70px;
        text-align: center;
        border-radius: 0 0 50px 50px;
        border-bottom: 1px solid var(--border);
        margin-bottom: 50px;
    }
    .hero-title { font-weight: 800; font-size: clamp(2rem, 6vw, 2.8rem); letter-spacing: -1.5px; margin-bottom: 20px; color: var(--text-main); }
    .hero-desc { max-width: 850px; margin: 0 auto 25px; font-size: 15px; line-height: 1.8; color: var(--text-muted); }

    /* INFO PILL STYLING */
    .badge-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 25px; max-width: 1000px; margin-left: auto; margin-right: auto; }
    .info-pill {
        background: var(--primary);
        color: #fff;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 9px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 5px;
    }
    .info-pill span { background: #fff; color: var(--primary); padding: 2px 8px; border-radius: 50px; font-weight: 900; }

    /* LED Status Indicators */
    .status-led { font-size: 9px; font-weight: 800; padding: 6px 16px; border-radius: 50px; text-transform: uppercase; color: #fff; }
    .led-online { background: #22c55e; box-shadow: 0 0 15px rgba(34, 197, 94, 0.5); animation: pulseGlow 2s infinite; }
    .led-offline { background: #ef4444; box-shadow: 0 0 15px rgba(239, 68, 68, 0.5); animation: pulseGlow 2s infinite; }
    @keyframes pulseGlow { 0%, 100% { opacity: 0.85; transform: scale(1); } 50% { opacity: 1; transform: scale(1.05); box-shadow: 0 0 25px rgba(34, 197, 94, 0.8); } }

    /* Cards - Fix Tema */
    .server-card { background: var(--bg-card) !important; border-radius: 28px; padding: 30px; border: 1px solid var(--border); text-align: center; transition: 0.4s; }
    .server-card h2 { color: var(--text-main); }
    .server-card:hover { transform: translateY(-12px); border-color: var(--primary); box-shadow: 0 20px 40px rgba(59, 130, 246, 0.12); }
    .btn-select { background: var(--primary); color: #fff !important; font-weight: 800; border-radius: 50px; padding: 14px; font-size: 12px; text-decoration: none; display: block; border: none; transition: 0.3s; }

    /* Infrastructure Benefits - Fix Tema */
    .section-label { font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 3px; display: block; margin-bottom: 12px; }
    .section-title { font-weight: 800; font-size: 30px; color: var(--text-main); margin-bottom: 40px; }
    .benefit-card { background: var(--bg-card) !important; border: 1px solid var(--border); border-radius: 25px; padding: 35px; height: 100%; transition: 0.3s; }
    .ben-title { font-weight: 800; font-size: 17px; margin-bottom: 12px; color: var(--text-main); }
    .ben-desc { font-size: 13px; color: var(--text-muted); line-height: 1.7; }

    /* Scenarios - Fix Tema */
    .sc-card { background: var(--bg-card) !important; border: 1px solid var(--border); border-radius: 24px; padding: 25px; height: 100%; border-bottom: 4px solid #ef4444; }
    .sc-title { font-weight: 800; font-size: 15px; margin-bottom: 12px; color: var(--text-main); }
    .sc-desc { font-size: 13px; color: var(--text-muted); line-height: 1.7; }

    @media (max-width: 768px) { .hero-title { font-size: 2.2rem; } .info-pill { font-size: 8px; } }
</style>

<main>
    <!-- HERO SECTION -->
    <section class="ssh-hero">
        <div class="container">
            <h1 class="hero-title">SSH Websocket (WS) Premium</h1>
            <p class="hero-desc">SuryaSSH menghadirkan infrastruktur <span class="fw-bold text-primary">SSH Websocket (WS)</span> dengan teknologi optimasi CDN global. Kami mendukung berbagai metode tunneling termasuk <span class="fw-bold text-primary">UDP Custom, SlowDNS, Stunnel, dan OpenVPN</span> untuk memastikan kebebasan akses internet Anda tetap aman dan tak terbatas.</p>
            
            <div class="badge-container">
                <div class="info-pill">SUPPORT <span>WEBSOCKET</span></div>
                <div class="info-pill">SUPPORT <span>UDP CUSTOM</span></div>
                <div class="info-pill">SUPPORT <span>STUNNEL</span></div>
                <div class="info-pill">SUPPORT <span>SLOWDNS</span></div>
                <div class="info-pill">SUPPORT <span>OPENVPN</span></div>
                <div class="info-pill">SUPPORT <span>SSL/TLS</span></div>
                <div class="info-pill">SUPPORT <span>HTTP</span></div>
                <div class="info-pill">UPTIME <span>99.9%</span></div>
            </div>
        </div>
    </section>

    <!-- SERVER LISTING -->
    <section class="container pb-5">
        <div class="row g-4">
            <?php
            $servers = $conn->query("SELECT * FROM servers WHERE type = 'ssh' ORDER BY id DESC");
            if ($servers && $servers->num_rows > 0):
                while($s = $servers->fetch_assoc()):
                    $is_online = @fsockopen(trim($s['ip']), 22, $errno, $errstr, 2.0); 
            ?>
            <article class="col-6 col-md-4 col-lg-3">
                <div class="server-card h-100 d-flex flex-column justify-content-between shadow-sm">
                    <div>
                        <div class="mb-3">
                            <span class="status-led <?= $is_online ? 'led-online' : 'led-offline' ?>">
                                <i class="fas fa-bolt"></i> <?= $is_online ? 'Online' : 'Offline' ?>
                            </span>
                        </div>
                        <img src="<?= $s['flag'] ?>" alt="Server <?= htmlspecialchars($s['location']) ?>" class="rounded-circle mb-3 shadow-sm border" style="width: 60px; height: 60px; object-fit: cover;">
                        <h2 class="h6 fw-800 mb-1" style="font-size: 15px;"><?= strtoupper(htmlspecialchars($s['name'])) ?></h2>
                        <p class="text-muted small mb-0"><i class="fas fa-map-marker-alt me-1 text-primary"></i> <?= htmlspecialchars($s['location']) ?></p>
                    </div>
                    <div class="mt-auto">
                        <hr class="opacity-10 my-3">
                        <a href="pages/create_ssh.php?id=<?= $s['id'] ?>" class="btn-select <?= $is_online ? '' : 'disabled opacity-50' ?>">SELECT NODE</a>
                    </div>
                </div>
            </article>
            <?php if($is_online) fclose($is_online); endwhile; endif; ?>
        </div>
    </section>

    <!-- INFRASTRUCTURE BENEFITS -->
    <section class="container py-5">
        <div class="text-center mb-5">
            <span class="section-label">Elite Features</span>
            <h2 class="section-title">Why Trust Our SSH Network?</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="benefit-card shadow-sm">
                    <div class="text-primary mb-4"><i class="fas fa-tachometer-alt fa-2x"></i></div>
                    <h3 class="ben-title">Giga-Speed Port</h3>
                    <p class="ben-desc">Didukung oleh uplink 1Gbps untuk memastikan bandwidth tidak terbagi, memberikan kecepatan unduhan maksimal.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="benefit-card shadow-sm">
                    <div class="text-primary mb-4"><i class="fas fa-fingerprint fa-2x"></i></div>
                    <h3 class="ben-title">Military Encryption</h3>
                    <p class="ben-desc">Menggunakan algoritma enkripsi modern yang menjamin privasi data Anda tetap terjaga dari pihak ketiga.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="benefit-card shadow-sm">
                    <div class="text-primary mb-4"><i class="fas fa-cloud-upload-alt fa-2x"></i></div>
                    <h3 class="ben-title">Cloud Integration</h3>
                    <p class="ben-desc">Integrasi penuh dengan CDN Cloudflare untuk stabilitas rute internasional dan bypass sensor yang lebih kuat.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="benefit-card shadow-sm">
                    <div class="text-primary mb-4"><i class="fas fa-heartbeat fa-2x"></i></div>
                    <h3 class="ben-title">High Uptime Ratio</h3>
                    <p class="ben-desc">Monitoring sistem real-time selama 24 jam untuk menjamin ketersediaan layanan hingga 99,9% setiap bulan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PRACTICAL SCENARIOS -->
    <section class="container py-5 mb-5">
        <div class="text-center mb-5">
            <span class="section-label">Usage Case</span>
            <h2 class="section-title">Optimal Environments for SSH WS</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="sc-card shadow-sm">
                    <i class="fas fa-user-secret text-danger mb-3 fa-2x"></i>
                    <h3 class="sc-title">Anonymity on Public WiFi</h3>
                    <p class="sc-desc">Lindungi jejak digital dan data sensitif Bos saat terhubung ke jaringan publik yang tidak terenkripsi.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sc-card shadow-sm">
                    <i class="fas fa-globe-asia text-danger mb-3 fa-2x"></i>
                    <h3 class="sc-title">Unblock Regional Content</h3>
                    <p class="sc-desc">Akses konten, website, atau aplikasi yang dibatasi di wilayah tertentu dengan rute server internasional kami.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sc-card shadow-sm">
                    <i class="fas fa-video text-danger mb-3 fa-2x"></i>
                    <h3 class="sc-title">Lag-Free Media Streaming</h3>
                    <p class="sc-desc">Nikmati konten resolusi tinggi (4K) tanpa gangguan buffering berkat optimasi jalur data CDN khusus media.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>
