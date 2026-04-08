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

<title>V2Ray Trojan WebSocket Premium - Stealth Node Infrastructure | SuryaSSH</title>
<meta name="description" content="Akses infrastruktur V2Ray Trojan WebSocket Premium dengan teknologi penyamaran trafik HTTPS. Bypass DPI, tembus GFW, dan nikmati koneksi tunneling paling aman dan anti-deteksi.">
<meta name="keywords" content="Trojan WebSocket, Trojan GFW, Trojan V2Ray, Bypass DPI, Stealth Protocol, SuryaSSH, Akun Trojan Premium">

<style>
    /* --- TROJAN RED THEME SYSTEM --- */
    :root {
        --primary-t: #ef4444; /* Trojan Red */
        --primary-dark-t: #991b1b;
        --primary-soft: rgba(239, 68, 68, 0.08);
        --bg-site: #f8fafc;
        --bg-card: #ffffff;
        --bg-inner: #fef2f2;
        --text-main: #1e293b;
        --text-muted: #475569;
        --border: #fee2e2;
    }

    body.dark, [data-bs-theme="dark"] body {
        --bg-site: #0f172a;
        --bg-card: #1e293b;
        --bg-inner: rgba(239, 68, 68, 0.05);
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border: rgba(239, 68, 68, 0.1);
    }

    body { background-color: var(--bg-site) !important; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; margin: 0; overflow-x: hidden; }

    /* Hero Section */
    .trojan-hero {
        background: linear-gradient(135deg, var(--primary-soft) 0%, var(--bg-site) 100%);
        padding: 100px 20px 80px;
        text-align: center;
        border-bottom: 1px solid var(--border);
        margin-bottom: 60px;
        border-radius: 0 0 60px 60px;
    }
    .hero-title { font-weight: 900; font-size: clamp(2.2rem, 7vw, 3rem); letter-spacing: -2px; margin-bottom: 15px; color: var(--text-main); }
    .hero-desc { max-width: 750px; margin: 0 auto 35px; font-size: 15.5px; line-height: 1.8; color: var(--text-muted); }

    /* Info Badge Pills */
    .badge-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
    .v-pill {
        background: var(--primary-t); color: #fff; padding: 6px 14px; border-radius: 50px; font-size: 9px; font-weight: 800;
        display: inline-flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 1px;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); border: 1px solid rgba(255,255,255,0.1);
    }
    .v-pill span { background: #fff; color: var(--primary-t); padding: 2px 8px; border-radius: 50px; font-weight: 900; }

    /* --- NODE CARDS --- */
    .node-card { 
        background: var(--bg-card) !important; border-radius: 30px; padding: 35px 25px; 
        border: 1px solid var(--border); text-align: center; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
        height: 100%; display: flex; flex-direction: column; justify-content: space-between;
    }
    .node-card:hover { transform: translateY(-12px); border-color: var(--primary-t); box-shadow: 0 25px 50px rgba(239, 68, 68, 0.12); }

    .status-led { font-size: 8px; font-weight: 900; padding: 5px 15px; border-radius: 50px; text-transform: uppercase; color: #fff; display: inline-block; margin-bottom: 15px; }
    .online { background: #22c55e; box-shadow: 0 0 10px rgba(34, 197, 94, 0.3); }
    .offline { background: #ef4444; }

    .node-btn { 
        background: var(--primary-t); color: #fff !important; font-weight: 800; border-radius: 50px; 
        padding: 14px 10px; font-size: 11px; border: none; transition: 0.3s; 
        display: block; width: 100%; text-decoration: none; white-space: nowrap;
        box-shadow: 0 8px 15px rgba(239, 68, 68, 0.2); text-transform: uppercase;
    }
    .node-btn:hover { transform: scale(1.03); filter: brightness(1.1); }
    .node-btn.disabled { background: #94a3b8 !important; opacity: 0.6; pointer-events: none; }

    /* SEO CONTENT SECTION */
    .section-label { font-size: 11px; font-weight: 800; color: var(--primary-t); text-transform: uppercase; letter-spacing: 3px; display: block; margin-bottom: 12px; text-align: center; }
    .section-title { font-weight: 800; font-size: 28px; color: var(--text-main); margin-bottom: 45px; text-align: center; }

    .seo-card { background: var(--bg-card); padding: 35px; border-radius: 28px; border: 1px solid var(--border); height: 100%; transition: 0.3s; }
    .seo-card h5 { font-weight: 800; font-size: 17px; margin-bottom: 15px; display: flex; align-items: center; gap: 12px; }
    .seo-card i { color: var(--primary-t); }
    .seo-card p { font-size: 13.5px; color: var(--text-muted); line-height: 1.7; margin-bottom: 0; }

    @media (max-width: 768px) { 
        .trojan-hero { padding: 80px 15px 60px; margin-bottom: 40px; }
        .node-card { padding: 25px 15px; }
        .node-btn { font-size: 10px; padding: 12px 5px; }
        .section-title { font-size: 22px; }
    }
</style>

<main>
    <section class="trojan-hero">
        <div class="container">
            <span class="section-label animate__animated animate__fadeIn">Stealth Protocol</span>
            <h1 class="hero-title animate__animated animate__fadeInDown">Trojan WebSocket Nodes</h1>
            <p class="hero-desc animate__animated animate__fadeInUp">
                SuryaSSH menghadirkan infrastruktur <span class="fw-bold text-danger">Trojan WebSocket</span> paling mutakhir. Dirancang untuk meniru trafik HTTPS secara sempurna guna menembus sensor DPI.
            </p>
            
            <div class="badge-row animate__animated animate__fadeInUp">
                <div class="v-pill">MODE <span>STEALTH</span></div>
                <div class="v-pill">ENGINE <span>XRAY CORE</span></div>
                <div class="v-pill">PROTOCOL <span>WEBSOCKET</span></div>
                <div class="v-pill">ACCESS <span>UNLIMITED</span></div>
                <div class="v-pill">UPTIME <span>99.9%</span></div>
            </div>
        </div>
    </section>

    <section class="container pb-5">
        <div class="row g-3 g-md-4">
            <?php
            $res = $conn->query("SELECT * FROM servers WHERE type = 'trojan' ORDER BY id DESC");
            if($res && $res->num_rows > 0):
                while($s = $res->fetch_assoc()):
                    // PERBAIKAN: Timeout dinaikkan ke 2.0 detik agar status ONLINE akurat
                    $online = @fsockopen(trim($s['ip']), 443, $errno, $errstr, 2.0);
            ?>
            <article class="col-6 col-md-4 col-lg-3">
                <div class="node-card shadow-sm">
                    <div>
                        <div class="text-center">
                            <span class="status-led <?= $online ? 'online' : 'offline' ?>">
                                <i class="fas fa-shield-alt me-1"></i> <?= $online ? 'ACTIVE' : 'OFFLINE' ?>
                            </span>
                        </div>
                        <img src="<?= $s['flag'] ?>" alt="Flag" class="rounded-circle mb-3 shadow-sm border" style="width: 50px; height: 50px; object-fit: cover;">
                        <h2 class="h6 fw-800 mb-1" style="font-size: 14px;"><?= strtoupper(htmlspecialchars($s['name'])) ?></h2>
                        <p class="text-muted small mb-0" style="font-size: 11px;"><i class="fas fa-map-marker-alt me-1 text-danger"></i> <?= htmlspecialchars($s['location']) ?></p>
                    </div>
                    <div class="mt-auto pt-4">
                        <hr class="opacity-10 mb-3">
                        <a href="pages/create_trojan.php?id=<?= $s['id'] ?>&proto=trojan" class="node-btn <?= $online ? '' : 'disabled' ?>">
                            <?= $online ? 'SELECT NODE' : 'MAINTENANCE' ?>
                        </a>
                    </div>
                </div>
            </article>
            <?php if($online) fclose($online); endwhile; else: ?>
                <div class="col-12 text-center py-5 opacity-50">Belum ada Trojan Nodes tersedia saat ini.</div>
            <?php endif; ?>
        </div>
    </section>

    <section class="container py-5">
        <div class="text-center mb-5">
            <span class="section-label">Technical Mastery</span>
            <h2 class="section-title">Enforced Trojan Performance</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="seo-card shadow-sm">
                    <h5><i class="fas fa-user-secret"></i> Anti-GFW Stealth</h5>
                    <p>Protokol Trojan di SuryaSSH dioptimalkan untuk menyamarkan sidik jari TLS Anda secara total.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="seo-card shadow-sm">
                    <h5><i class="fas fa-link"></i> WebSocket Integration</h5>
                    <p>Integrasi <strong>Trojan WebSocket</strong> melalui port 443 menjamin bypass firewall dengan stabilitas tinggi.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="seo-card shadow-sm">
                    <h5><i class="fas fa-tachometer-alt"></i> Unlimited Throughput</h5>
                    <p>Node Trojan kami dibangun di atas infrastruktur Bare-Metal dengan bandwidth besar tanpa pembatasan.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'footer.php'; ?>
