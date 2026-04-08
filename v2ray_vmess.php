<?php 
// 1. Koneksi & Header (Wajib)
include_once 'config.php'; 
include_once 'header.php'; 

// Safety Check Database
if (!$conn) {
    echo "<div class='container mt-5 pt-5'><div class='alert alert-danger text-center rounded-4 shadow-sm'>Database Gagal Konek, Bos!</div></div>";
    include_once 'footer.php';
    exit;
}
?>

<style>
    /* HERO SECTION - SEO FRIENDLY */
    .vmess-hero {
        background: linear-gradient(135deg, rgba(39, 174, 96, 0.1) 0%, var(--bg-body) 100%);
        padding: 80px 20px 60px;
        text-align: center;
        border-radius: 0 0 50px 50px;
        margin-bottom: 50px;
        border-bottom: 1px solid var(--border-color);
    }
    .vmess-title { font-weight: 800; font-size: clamp(1.8rem, 5vw, 2.5rem); color: var(--text-main); letter-spacing: -1px; }
    .seo-description { max-width: 750px; margin: 0 auto 35px; font-size: 14px; line-height: 1.7; color: var(--text-muted); }
    .keyword-highlight { color: #27ae60; font-weight: 700; }

    /* CARD GRID STYLE */
    .vmess-card {
        background: var(--bg-card);
        border-radius: 24px;
        padding: 25px;
        border: 1px solid var(--border-color);
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-align: center;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }
    .vmess-card:hover { transform: translateY(-10px); border-color: #2ecc71 !important; box-shadow: 0 15px 30px rgba(46, 204, 113, 0.15) !important; }
    
    .btn-create-vmess { background: #27ae60 !important; border: none !important; color: #fff !important; font-weight: 800; font-size: 11px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 50px !important; text-transform: uppercase; text-decoration: none; transition: 0.3s; }
    .btn-create-vmess:hover { transform: scale(1.05); filter: brightness(1.1); }

    /* STATUS BADGE GLOWING & PULSE (LIKE A LAMP) */
    .status-indicator {
        font-size: 9px;
        font-weight: 800;
        padding: 5px 14px;
        border-radius: 50px;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        letter-spacing: 1px;
        color: #fff;
        border: 1px solid rgba(255,255,255,0.1);
    }

    /* Glow Online (Hijau) */
    .lamp-online {
        background: #22c55e;
        box-shadow: 0 0 15px rgba(34, 197, 94, 0.6);
        animation: blink-green 2s infinite;
    }

    /* Glow Offline (Merah) */
    .lamp-offline {
        background: #ef4444;
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.6);
        animation: blink-red 2s infinite;
    }

    @keyframes blink-green {
        0% { box-shadow: 0 0 5px rgba(34, 197, 94, 0.4); opacity: 0.9; }
        50% { box-shadow: 0 0 20px rgba(34, 197, 94, 0.9); opacity: 1; }
        100% { box-shadow: 0 0 5px rgba(34, 197, 94, 0.4); opacity: 0.9; }
    }

    @keyframes blink-red {
        0% { box-shadow: 0 0 5px rgba(239, 68, 68, 0.4); opacity: 0.9; }
        50% { box-shadow: 0 0 20px rgba(239, 68, 68, 0.9); opacity: 1; }
        100% { box-shadow: 0 0 5px rgba(239, 68, 68, 0.4); opacity: 0.9; }
    }

    /* SECTIONS STYLES */
    .section-label { font-size: 11px; font-weight: 800; color: #27ae60; text-transform: uppercase; letter-spacing: 2px; display: block; margin-bottom: 10px; }
    .section-title { font-weight: 800; font-size: 28px; color: var(--text-main); margin-bottom: 40px; }
    .advantage-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 25px; padding: 30px; transition: 0.3s; }
    .adv-icon { width: 50px; height: 50px; background: rgba(39, 174, 96, 0.1); border-radius: 15px; display: flex; align-items: center; justify-content: center; color: #27ae60; font-size: 20px; margin-bottom: 20px; }
    
    /* STEP CARD */
    .step-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 25px; padding: 35px 20px; text-align: center; height: 100%; }
    .step-number { width: 35px; height: 35px; background: #27ae60; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; margin: 0 auto 20px; }
</style>

<!-- HERO -->
<section class="vmess-hero animate__animated animate__fadeIn">
    <div class="container text-center">
        <h1 class="vmess-title mb-3">V2Ray VMess Websocket (WS) Premium</h1>
        <div class="seo-description">
            Dapatkan akses <span class="keyword-highlight">V2Ray VMess Websocket (WS)</span> dengan performa terbaik untuk melewati sensor internet (Internet Positif). Layanan kami mendukung enkripsi tingkat tinggi, <span class="keyword-highlight">CDN Websocket</span>, serta kompatibel dengan TLS dan Non-TLS untuk kestabilan koneksi maksimal.
        </div>
    </div>
</section>

<!-- SERVER LIST -->
<section class="container pb-5">
    <div class="row g-3 g-md-4">
        <?php
        $servers = $conn->query("SELECT * FROM servers WHERE type = 'vmess' ORDER BY id DESC");
        if ($servers && $servers->num_rows > 0):
            while($s = $servers->fetch_assoc()):
                // PERBAIKAN: Timeout ditingkatkan menjadi 2.0 detik agar status ONLINE lebih akurat
                $is_online = @fsockopen(trim($s['ip']), 22, $errno, $errstr, 2.0); 
        ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="vmess-card shadow-sm">
                <div>
                    <div class="mb-3">
                        <span class="status-indicator <?= $is_online ? 'lamp-online' : 'lamp-offline' ?>">
                            <i class="fas fa-lightbulb"></i> <?= $is_online ? 'Online' : 'Offline' ?>
                        </span>
                    </div>
                    <img src="<?= $s['flag'] ?>" class="rounded-circle mb-3 shadow-sm border" style="width: 50px; height: 50px; object-fit: cover;">
                    <h6 class="fw-800 mb-1" style="color: var(--text-main);"><?= strtoupper(htmlspecialchars($s['name'])) ?></h6>
                    <p class="text-muted small"><i class="fas fa-map-marker-alt me-1 text-success"></i> <?= htmlspecialchars($s['location']) ?></p>
                </div>
                <div class="mt-auto pt-3">
                    <hr class="opacity-10 my-3">
                    <a href="pages/create_vmess.php?id=<?= $s['id'] ?>" class="btn-create-vmess <?= $is_online ? '' : 'disabled opacity-50' ?>">
                        <?= $is_online ? 'Select Node' : 'Maintenance' ?>
                    </a>
                </div>
            </div>
        </div>
        <?php if($is_online) fclose($is_online); endwhile; endif; ?>
    </div>
</section>

<!-- PRO ADVANTAGES -->
<section class="container py-5">
    <div class="text-center mb-5">
        <span class="section-label">Key Advantages</span>
        <h2 class="section-title">Why Trust Our V2Ray Network?</h2>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="advantage-card">
                <div class="adv-icon"><i class="fas fa-microchip"></i></div>
                <h5 class="adv-title">Dynamic Path Selection</h5>
                <p class="adv-desc">Sistem kami secara cerdas mengarahkan trafik Anda melalui jalur paling optimal untuk meminimalkan latensi.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="advantage-card">
                <div class="adv-icon"><i class="fas fa-user-secret"></i></div>
                <h5 class="adv-title">Stealth Protocol</h5>
                <p class="adv-desc">Implementasi teknik obfuscation tingkat lanjut untuk menyembunyikan pola trafik dari sensor pihak ketiga.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="advantage-card">
                <div class="adv-icon"><i class="fas fa-globe-americas"></i></div>
                <h5 class="adv-title">Universal Compatibility</h5>
                <p class="adv-desc">Mendukung berbagai transport layer mulai dari WebSocket, gRPC, hingga mKCP untuk fleksibilitas total.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="advantage-card">
                <div class="adv-icon"><i class="fas fa-bolt"></i></div>
                <h5 class="adv-title">Performance Tuning</h5>
                <p class="adv-desc">Setiap node dikalibrasi secara otomatis setiap jam untuk memastikan kestabilan bandwidth yang maksimal.</p>
            </div>
        </div>
    </div>
</section>

<!-- SETUP PROCESS -->
<section class="container py-5 mb-5">
    <div class="text-center mb-5">
        <span class="section-label">Quick Deployment</span>
        <h2 class="section-title">Activation Workflow</h2>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="step-card">
                <div class="step-number">1</div>
                <h5 class="fw-bold">Initialize Node</h5>
                <p class="small text-muted">Pilih server premium dan buat akun unik Anda melalui sistem deployment instan kami.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="step-card">
                <div class="step-number">2</div>
                <h5 class="fw-bold">Client Configuration</h5>
                <p class="small text-muted">Unduh aplikasi V2Ray/v2rayN yang kompatibel dengan perangkat mobile atau desktop Anda.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="step-card">
                <div class="step-number">3</div>
                <h5 class="fw-bold">Establish Link</h5>
                <p class="small text-muted">Salin konfigurasi atau scan QR Code untuk memulai koneksi aman dengan enkripsi tinggi.</p>
            </div>
        </div>
    </div>
</section>

<?php include_once 'footer.php'; ?>