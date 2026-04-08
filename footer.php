<footer style="background: #0f172a; color: #94a3b8; padding: 60px 0 30px;">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6">
                <h4 class="fw-800 text-white mb-4">ZEAR GAMES</h4>
                <p class="small" style="max-width: 400px;">Penyedia infrastruktur tunneling kelas enterprise dengan fokus pada keamanan, kecepatan, dan privasi pengguna.</p>
            </div>
            <div class="col-lg-3 col-6">
                <h6 class="text-white fw-bold mb-3">SERVICES</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="index.php?page=ssh_list" class="text-decoration-none text-muted">SSH Websocket</a></li>
                    <li class="mb-2"><a href="index.php?page=v2ray_list" class="text-decoration-none text-muted">V2Ray Vmess</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-6">
                <h6 class="text-white fw-bold mb-3">RESOURCES</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="status" class="text-decoration-none text-muted">Server Status</a></li>
                    <li class="mb-2"><a href="admin/" class="text-decoration-none text-muted">Admin Panel</a></li>
                </ul>
            </div>
        </div>
        <hr class="mt-5 border-secondary opacity-25">
        <p class="text-center small mt-4 mb-0">© 2026 ZearGames Team. Made for better internet connection.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const themeSwitcher = document.getElementById('themeSwitcher');
    const themeIcon = document.getElementById('themeIcon');
    const htmlElement = document.documentElement;

    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);

    themeSwitcher.addEventListener('click', () => {
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        setTheme(newTheme);
    });

    function setTheme(theme) {
        htmlElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
        themeIcon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    }
</script>
</body>
</html>
