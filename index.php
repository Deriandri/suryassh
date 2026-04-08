<?php
// 1. Panggil koneksi database
include_once 'config.php';

// 2. Panggil Header
include_once 'header.php';

// 3. Logika Database Stats
if (!$conn) {
    $total_srv = 0; $total_acc = 0;
} else {
    $srv_query = $conn->query("SELECT COUNT(*) as t FROM servers");
    $total_srv = ($srv_query) ? $srv_query->fetch_assoc()['t'] : 0;
    $acc_query = $conn->query("SELECT COUNT(*) as t FROM accounts");
    $total_acc = ($acc_query) ? $acc_query->fetch_assoc()['t'] : 0;
}
?>

<!-- SEO METADATA - ALL PROTOCOLS WEBSOCKET OPTIMIZED -->
<title>SuryaSSH - Premium SSH WebSocket & V2Ray VLESS VMess Reality</title>
<meta name="description" content="Penyedia infrastruktur SSH WebSocket, V2Ray VMess, VLESS Reality, dan Trojan GFW premium. Nikmati koneksi stabil dengan optimasi CDN Cloudflare dan bypass DPI tercepat.">
<meta name="keywords" content="ssh websocket, v2ray vmess, v2ray vless, v2ray trojan, vless reality, ssh ws cdn, xray core premium, SuryaSSH, bypass internet positif">

<style>
    /* --- SYSTEM THEME ADAPTIVE --- */
    :root {
        --primary: #2563eb;
        --bg-site: #f8fafc;
        --bg-card: #ffffff;
        --text-main: #1e293b;
        --text-muted: #475569;
        --border: #e2e8f0;
    }

    body.dark, [data-bs-theme="dark"] body {
        --bg-site: #0f172a;
        --bg-card: #1e293b;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border: rgba(255,255,255,0.06);
    }

    body { background-color: var(--bg-site) !important; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; margin: 0; overflow-x: hidden; }

    /* HERO SECTION */
    .hero-banner {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        padding: 120px 20px 180px;
        color: #fff;
        text-align: center;
        clip-path: ellipse(150% 100% at 50% 0%);
    }
    .hero-banner h1 { font-weight: 900; font-size: clamp(2.2rem, 7vw, 3.5rem); letter-spacing: -2px; line-height: 1.1; margin-bottom: 20px; }
    .hero-banner p { max-width: 750px; margin: 0 auto 40px; font-size: 16px; opacity: 0.85; line-height: 1.8; }

    .btn-hero { padding: 15px 35px; border-radius: 50px; font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; text-decoration: none; display: inline-block; }
    .btn-get { background: #fff; color: #1e3a8a !important; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    .btn-status { border: 2px solid rgba(255,255,255,0.3); color: #fff !important; margin-left: 15px; }

    /* STATS SECTION (SYMMETRICAL) */
    .info-section { margin-top: -100px; position: relative; z-index: 50; }
    .info-box { 
        background: var(--bg-card); border: 1px solid var(--border); 
        transition: 0.3s; height: 100%; display: flex; flex-direction: column; 
        justify-content: center; border-radius: 20px;
    }

    /* SERVICE CARDS (SYMMETRICAL) */
    .service-card { 
        background: var(--bg-card); border-radius: 28px; padding: 35px 20px; 
        border: 1px solid var(--border); text-align: center; transition: 0.4s; 
        height: 100%; display: flex; flex-direction: column;
    }
    .service-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); border-color: var(--primary); }
    .service-icon-wrap { width: 55px; height: 55px; border-radius: 18px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 22px; }
    .btn-service { border-radius: 50px; padding: 12px; font-weight: 800; font-size: 11px; text-transform: uppercase; color: #fff !important; width: 100%; display: block; border: none; margin-top: auto; }

    /* SEO CONTENT AREA (SYMMETRICAL) */
    .seo-label { font-size: 10px; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 3px; display: block; margin-bottom: 12px; }
    .seo-card { 
        background: var(--bg-card); border-radius: 28px; padding: 35px; 
        border: 1px solid var(--border); height: 100%; 
    }
    .seo-card h3 { font-weight: 800; font-size: 18px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; color: var(--text-main); }
    .seo-card p { font-size: 13.5px; color: var(--text-muted); line-height: 1.8; }

    @media (max-width: 768px) {
        .hero-banner { padding: 90px 15px 140px; }
        .btn-status { margin-left: 0; margin-top: 10px; }
        .hero-banner .d-flex { flex-direction: column; align-items: center; }
    }
</style>

<!-- MAIN HERO -->
<section class="hero-banner">
    <div class="container">
        <h1 class="animate__animated animate__fadeInDown">Premium WebSocket & <br> Tunneling Infrastructure Gratis</h1>
        <p class="animate__animated animate__fadeInUp">
            SuryaSSH menghadirkan solusi konektivitas premium dengan optimasi jalur **WebSocket** dan **CDN global**. Kami menjamin kebebasan akses internet dengan privasi mutakhir dan latensi rendah untuk aktivitas digital Anda.
        </p>
        <div class="d-flex justify-content-center animate__animated animate__fadeInUp">
            <a href="#services" class="btn-hero btn-get">Get Started</a>
            <a href="server_status.php" class="btn-hero btn-status">Status Sistem</a>
        </div>
    </div>
</section>

<!-- STATS SECTION (SYMMETRICAL) -->
<section class="container info-section">
    <div class="row g-3 g-md-4">
        <div class="col-6 col-md-3">
            <div class="info-box text-center p-4 shadow-sm">
                <i class="fas fa-shield-alt fa-2x text-primary mb-3"></i>
                <h6 class="fw-800 mb-1">Encrypted</h6>
                <p class="small text-muted mb-0">AES-256 Bit Security</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="info-box text-center p-4 shadow-sm">
                <i class="fas fa-bolt fa-2x text-warning mb-3"></i>
                <h6 class="fw-800 mb-1">High-Speed</h6>
                <p class="small text-muted mb-0">WebSocket Optimized</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="info-box text-center p-4 shadow-sm">
                <i class="fas fa-globe-americas fa-2x text-success mb-3"></i>
                <h6 class="fw-800 mb-1"><?= $total_srv ?> Server</h6>
                <p class="small text-muted mb-0">Infrastruktur Global</p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="info-box text-center p-4 shadow-sm">
                <i class="fas fa-user-check fa-2x text-info mb-3"></i>
                <h6 class="fw-800 mb-1"><?= $total_acc ?> Users</h6>
                <p class="small text-muted mb-0">Trusted Daily Users</p>
            </div>
        </div>
    </div>
</section>

<!-- SERVICE SELECTION -->
<section id="services" class="container py-5 mt-5">
    <div class="text-center mb-5">
        <span class="seo-label">Next-Gen Protocols</span>
        <h2 class="fw-900" style="font-size: clamp(1.8rem, 5vw, 2.5rem); letter-spacing: -1.5px;">Premium Tunneling Core</h2>
    </div>

    <div class="row g-3 g-md-4">
        <!-- SSH WS -->
        <div class="col-6 col-md-3">
            <div class="service-card shadow-sm">
                <div class="service-icon-wrap" style="background: rgba(37, 99, 235, 0.1); color: #2563eb;"><i class="fas fa-terminal"></i></div>
                <h6 class="fw-800 mb-2">SSH WebSocket</h6>
                <p class="small text-muted mb-4 d-none d-md-block">Optimasi port 80/443 dengan jalur CDN Cloudflare untuk stabilitas total.</p>
                <a href="ssh_websocket.php" class="btn-service" style="background: #2563eb;">Create SSH WS</a>
            </div>
        </div>

        <!-- VMESS WS -->
        <div class="col-6 col-md-3">
            <div class="service-card shadow-sm">
                <div class="service-icon-wrap" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;"><i class="fas fa-project-diagram"></i></div>
                <h6 class="fw-800 mb-2">V2Ray VMess</h6>
                <p class="small text-muted mb-4 d-none d-md-block">Protokol fleksibel dengan enkripsi multi-layer canggih untuk privasi maksimal.</p>
                <a href="v2ray_vmess.php" class="btn-service" style="background: #22c55e;">Create VMess WS</a>
            </div>
        </div>

        <!-- VLESS WS -->
        <div class="col-6 col-md-3">
            <div class="service-card shadow-sm">
                <div class="service-icon-wrap" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="fas fa-bolt"></i></div>
                <h6 class="fw-800 mb-2">V2Ray VLESS</h6>
                <p class="small text-muted mb-4 d-none d-md-block">Evolusi teringan tanpa overhead, mendukung teknologi Reality & Vision.</p>
                <a href="v2ray_vless.php" class="btn-service" style="background: #f59e0b;">Create VLESS WS</a>
            </div>
        </div>

        <!-- TROJAN WS -->
        <div class="col-6 col-md-3">
            <div class="service-card shadow-sm">
                <div class="service-icon-wrap" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="fas fa-shield-virus"></i></div>
                <h6 class="fw-800 mb-2">V2Ray Trojan</h6>
                <p class="small text-muted mb-4 d-none d-md-block">Stealth mode menyamar sebagai trafik HTTPS standar untuk bypass DPI.</p>
                <a href="v2ray_trojan.php" class="btn-service" style="background: #ef4444;">Create Trojan WS</a>
            </div>
        </div>
    </div>
</section>

<!-- PROFESSIONAL SEO INSIGHTS (ALL PROTOCOLS INCLUDED) -->
<section class="container py-5">
    <div class="text-center mb-5">
        <span class="seo-label">Technical Authority</span>
        <h2 class="fw-900" style="font-size: clamp(1.6rem, 4vw, 2.2rem);">Solusi Tunneling Kelas Enterprise</h2>
    </div>

    <div class="row g-4">
        <!-- SSH WEBSOCKET -->
        <div class="col-md-6">
            <div class="seo-card shadow-sm">
                <h3><i class="fas fa-link text-primary"></i> Layanan SSH WebSocket Premium</h3>
                <p>Layanan **SSH WebSocket** SuryaSSH dirancang khusus untuk melewati sensor firewall yang ketat melalui jalur **CDN (Content Delivery Network)**. Dengan optimasi port web standar, trafik tunnel Anda tersamarkan sempurna, memberikan jaminan uptime 99.9% dan koneksi stabil untuk kebutuhan browsing profesional.</p>
            </div>
        </div>
        <!-- VMESS -->
        <div class="col-md-6">
            <div class="seo-card shadow-sm">
                <h3><i class="fas fa-crown text-success"></i> V2Ray VMess WebSocket Premium</h3>
                <p>Gunakan **V2Ray VMess WebSocket** untuk keamanan data end-to-end yang tak tertandingi. Menggunakan basis enkripsi asimetris dan autentikasi unik, VMess di SuryaSSH menawarkan durasi aktif panjang hingga 30 hari dengan kualitas server premium kelas dunia.</p>
            </div>
        </div>
        <!-- VLESS -->
        <div class="col-md-6">
            <div class="seo-card shadow-sm">
                <h3><i class="fas fa-microchip text-warning"></i> Next-Gen VLESS Reality Technology</h3>
                <p>Teknologi **VLESS Reality** adalah standar terbaru dalam dunia perutean. Dengan menghilangkan beban enkripsi internal berlebih (No-Overhead), VLESS menghasilkan latensi yang sangat rendah, menjadikannya Createan utama bagi para *gamer* dan penikmat konten *streaming* 4K.</p>
            </div>
        </div>
        <!-- TROJAN -->
        <div class="col-md-6">
            <div class="seo-card shadow-sm">
                <h3><i class="fas fa-mask text-danger"></i> V2Ray Trojan Stealth Camouflage</h3>
                <p>Protokol **Trojan** menawarkan kemampuan penyamaran trafik HTTPS paling mutakhir. Dengan menyamar sebagai lalu lintas web normal, Trojan mampu melewati sistem **DPI (Deep Packet Inspection)** dengan tingkat keberhasilan tinggi, menjamin privasi total bagi setiap pengguna.</p>
            </div>
        </div>
    </div>
</section>

<?php 
include_once 'footer.php'; 
?>
