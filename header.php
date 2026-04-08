<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuryaSSH - Premium WebSocket Tunneling Solution</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <?php if(basename($_SERVER['PHP_SELF']) == 'create_ssh.php') { ?>
    <meta name="robots" content="noindex, nofollow">
    <?php } ?>


    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0f4cba 0%, #1e3a8a 100%);
            --accent-color: #3b82f6; --text-main: #1e293b; --text-muted: #64748b;
            --bg-body: #ffffff; --bg-card: #ffffff; --bg-nav: #ffffff;
            --border-color: #f1f5f9; --bg-toggle: #f8fafc;
        }

        [data-bs-theme="dark"] {
            --text-main: #f8fafc; --text-muted: #94a3b8; --bg-body: #0f172a;
            --bg-card: #1e293b; --bg-nav: #0f172a; --border-color: rgba(255, 255, 255, 0.05);
            --bg-toggle: #1e293b;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; color: var(--text-main); background-color: var(--bg-body); transition: 0.3s; overflow-x: hidden; }
        
        .navbar { background: var(--bg-nav) !important; padding: 12px 0; border-bottom: 1px solid var(--border-color); z-index: 9999 !important; }
        .navbar-brand { font-weight: 800; font-size: 1.4rem; color: var(--text-main) !important; }
        
        .nav-link { font-weight: 600; color: var(--text-main) !important; font-size: 14px; padding: 10px 15px !important; transition: 0.2s; border-radius: 8px; }
        .nav-link:hover { background: rgba(59, 130, 246, 0.05); }

        .theme-toggle { cursor: pointer; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: var(--bg-toggle); border: 1px solid var(--border-color); color: var(--text-main); }

        .hero-banner { background: var(--primary-gradient); padding: 100px 0 140px; color: #fff; text-align: center; clip-path: ellipse(160% 100% at 50% 0%); position: relative; z-index: 1; }

        /* FIX MENU MOBILE */
        @media (max-width: 991.98px) {
            .navbar-collapse { 
                display: none; /* Sembunyikan default */
                background: var(--bg-card) !important; 
                padding: 15px; 
                border-radius: 15px; 
                margin-top: 10px; 
                border: 1px solid var(--border-color);
                box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            }
            /* Class untuk memicu buka menu */
            .navbar-collapse.show-menu { display: block !important; animation: fadeInDown 0.3s; }
            .nav-link { border-bottom: 1px solid var(--border-color); }
            .dropdown-menu { display: none; padding-left: 20px; border: none; background: transparent; box-shadow: none; }
            .dropdown-menu.show-sub { display: block !important; }
        }

        /* Desktop Hover */
        @media (min-width: 992px) {
            .dropdown:hover .dropdown-menu { display: block; margin-top: 0; }
        }
    </style>
</head>
<body>

<nav class="navbar sticky-top navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="fas fa-cube text-primary me-2"></i>ZEAR GAMES
        </a>
        
        <div class="d-flex align-items-center order-lg-last ms-2">
            <div class="theme-toggle me-2" id="themeSwitcher">
                <i class="fas fa-sun" id="themeIcon"></i>
            </div>
            <button class="navbar-toggler border-0 shadow-none" type="button" id="manualToggler">
                <i class="fas fa-bars-staggered" style="color: var(--text-main);"></i>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto text-center text-lg-start">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="ssh_websocket.php">SSH Server</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="subTrigger">V2Ray Service</a>
                    <ul class="dropdown-menu" id="subContent">
                        <li><a class="dropdown-item nav-link border-0" href="v2ray_vmess.php">Vmess Premium</a></li>
                        <li><a class="dropdown-item nav-link border-0" href="v2ray_vless.php">Vless Gaming</a></li>
                        <li><a class="dropdown-item nav-link border-0" href="v2ray_trojan.php">Trojan Bypass</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="server_status.php">Status</a></li>
            </ul>
        </div>
    </div>
</nav>

<script>
    // 1. Dark Mode
    const themeSwitcher = document.getElementById('themeSwitcher');
    const themeIcon = document.getElementById('themeIcon');
    const htmlElement = document.documentElement;

    const savedTheme = localStorage.getItem('theme') || 'light';
    htmlElement.setAttribute('data-bs-theme', savedTheme);
    updateToggleState(savedTheme);

    themeSwitcher.addEventListener('click', () => {
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        htmlElement.setAttribute('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateToggleState(newTheme);
    });

    function updateToggleState(theme) {
        themeIcon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        themeIcon.style.color = theme === 'dark' ? '#f8fafc' : '#f1c40f';
    }

    // 2. MANUAL MENU LOGIC (ANTI-NYANGKUT)
    const toggler = document.getElementById('manualToggler');
    const menu = document.getElementById('navbarNav');
    const subTrigger = document.getElementById('subTrigger');
    const subContent = document.getElementById('subContent');

    // Buka/Tutup Menu Utama
    toggler.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('show-menu');
    });

    // Buka/Tutup Sub-menu (V2Ray) di HP
    subTrigger.addEventListener('click', (e) => {
        if(window.innerWidth < 992) {
            e.stopPropagation();
            subContent.classList.toggle('show-sub');
        }
    });

    // Klik di mana saja untuk menutup menu
    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target) && !toggler.contains(e.target)) {
            menu.classList.remove('show-menu');
            subContent.classList.remove('show-sub');
        }
    });

    // Menutup menu saat link diklik (kecuali tombol sub-menu)
    document.querySelectorAll('.nav-link:not(#subTrigger)').forEach(link => {
        link.addEventListener('click', () => {
            menu.classList.remove('show-menu');
        });
    });
</script>
