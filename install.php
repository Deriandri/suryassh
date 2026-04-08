<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZearGames - Professional Installer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f172a; color: #f8fafc; }
        .install-card { max-width: 500px; margin: 60px auto; border: none; border-radius: 30px; background: #1e293b; box-shadow: 0 25px 50px rgba(0,0,0,0.3); }
        .form-control { border-radius: 15px; padding: 12px 18px; border: 1px solid #334155; background: #0f172a; color: #fff; }
        .form-control:focus { background: #0f172a; color: #fff; border-color: #3b82f6; box-shadow: none; }
        .btn-install { border-radius: 15px; padding: 15px; font-weight: 800; background: #3b82f6; border: none; color: #fff; transition: 0.3s; }
        .btn-install:hover { background: #2563eb; transform: translateY(-2px); }
        .section-title { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 20px; display: block; border-left: 4px solid #3b82f6; padding-left: 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="card install-card p-4 p-md-5">
        <?php
        $config_file = 'config.php';
        if (file_exists($config_file)) {
            echo '<div class="text-center py-4">
                    <i class="fas fa-check-circle text-success mb-4" style="font-size: 60px;"></i>
                    <h3 class="fw-800">SISTEM TERINSTAL</h3>
                    <a href="index.php" class="btn btn-outline-info w-100 rounded-pill mt-4">KE DASHBOARD</a>
                  </div>';
        } else {
            echo '<div class="text-center mb-5">
                    <h2 class="fw-800 text-info">ZEAR INSTALLER</h2>
                    <p class="text-muted small">Database Schema v5.2.0 (Stable Status)</p>
                  </div>';

            if (isset($_POST['install'])) {
                $h = $_POST['host']; $u = $_POST['user']; $p = $_POST['pass']; $n = $_POST['name'];
                $adm_user = $_POST['adm_user']; $adm_pass = password_hash($_POST['adm_pass'], PASSWORD_DEFAULT);

                $db = @new mysqli($h, $u, $p, $n);
                if ($db->connect_error) {
                    echo '<div class="alert alert-danger border-0 rounded-4"><b>Error:</b> Database Ditolak.</div>';
                } else {
                    $db->query("CREATE TABLE IF NOT EXISTS `admin` (id INT PRIMARY KEY, username VARCHAR(50), password VARCHAR(255))");
                    $db->query("CREATE TABLE IF NOT EXISTS `servers` (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100), type VARCHAR(20) DEFAULT 'ssh', ip VARCHAR(100), password VARCHAR(255), location VARCHAR(100), flag VARCHAR(255), account_limit INT DEFAULT 10, quota_limit INT(11) DEFAULT 0, device_limit INT DEFAULT 2, daily_used INT DEFAULT 0, date_expired DATE, last_reset DATE)");
                    
                    $res = $db->query("SHOW COLUMNS FROM `servers` LIKE 'quota_limit'");
                    if ($res->num_rows == 0) $db->query("ALTER TABLE servers ADD quota_limit INT(11) DEFAULT 0 AFTER account_limit");
                    
                    $res = $db->query("SHOW COLUMNS FROM `servers` LIKE 'device_limit'");
                    if ($res->num_rows == 0) $db->query("ALTER TABLE servers ADD device_limit INT DEFAULT 2 AFTER quota_limit");

                    $db->query("CREATE TABLE IF NOT EXISTS `accounts` (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50), password VARCHAR(100), vps_ip VARCHAR(50), protocol VARCHAR(20), device_id VARCHAR(100), created_by_ip VARCHAR(50), date_created DATE, date_expired DATE)");

                    $res_acc = $db->query("SHOW COLUMNS FROM `accounts` LIKE 'password'");
                    if ($res_acc && $res_acc->num_rows == 0) $db->query("ALTER TABLE accounts ADD password VARCHAR(100) AFTER username");

                    $res_acc = $db->query("SHOW COLUMNS FROM `accounts` LIKE 'device_id'");
                    if ($res_acc && $res_acc->num_rows == 0) $db->query("ALTER TABLE accounts ADD device_id VARCHAR(100) AFTER protocol");

                    // MODIFIKASI: Tambahkan pembuatan tabel BLOG otomatis
                    $db->query("CREATE TABLE IF NOT EXISTS `blog` (
                        `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
                        `title` VARCHAR(255) NOT NULL,
                        `slug` VARCHAR(255) NOT NULL,
                        `content` LONGTEXT NOT NULL,
                        `image` VARCHAR(255) DEFAULT NULL,
                        `category` VARCHAR(100) DEFAULT 'General',
                        `author` VARCHAR(100) DEFAULT 'Admin',
                        `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )");

                    // PERBAIKAN: Sinkronisasi kolom tanggal blog jika sudah ada tabel lama
                    $res_blog = $db->query("SHOW COLUMNS FROM `blog` LIKE 'date_created'");
                    if ($res_blog && $res_blog->num_rows == 0) {
                        $db->query("ALTER TABLE blog ADD date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
                    }

                    $stmt = $db->prepare("REPLACE INTO admin (id, username, password) VALUES (1, ?, ?)");
                    $stmt->bind_param("ss", $adm_user, $adm_pass);
                    $stmt->execute();

                    $txt = "<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
\$conn = new mysqli('$h', '$u', '$p', '$n');
if (\$conn->connect_error) { die('Database Gagal!'); }

function checkOnline(\$ip, \$port = 22) {
    \$socket = @fsockopen(trim(\$ip), \$port, \$errno, \$errstr, 2.0);
    if (\$socket) { @fclose(\$socket); return true; }
    return false;
}

\$today = date('Y-m-d');
\$res = \$conn->query(\"SELECT id FROM servers WHERE last_reset != '\$today' OR last_reset IS NULL LIMIT 1\");
if (\$res && \$res->num_rows > 0) { \$conn->query(\"UPDATE servers SET daily_used = 0, last_reset = '\$today'\"); }
?>";
                    file_put_contents($config_file, $txt);
                    echo '<div class="alert alert-success border-0 rounded-4 mb-4 text-center"><b>SUKSES!</b> Sistem Terinstal.</div>';
                    echo '<a href="index.php" class="btn btn-info w-100 rounded-pill fw-bold py-3">MASUK DASHBOARD</a>';
                    echo '<style>.form-install { display:none; }</style>';
                }
            }
            ?>
            <form method="POST" class="form-install">
                <span class="section-title">Database MySQL</span>
                <div class="mb-3"><input type="text" name="host" class="form-control" value="localhost" required></div>
                <div class="mb-3"><input type="text" name="user" class="form-control" placeholder="DB Username" required></div>
                <div class="mb-3"><input type="password" name="pass" class="form-control" placeholder="DB Password"></div>
                <div class="mb-3"><input type="text" name="name" class="form-control" placeholder="DB Name" required></div>
                <hr class="my-4 opacity-25">
                <span class="section-title">Admin Account</span>
                <div class="mb-3"><input type="text" name="adm_user" class="form-control" placeholder="Admin Username" required></div>
                <div class="mb-4"><input type="password" name="adm_pass" class="form-control" placeholder="Admin Password" required></div>
                <button type="submit" name="install" class="btn btn-install w-100 shadow">SIMPAN & MULAI</button>
            </form>
        <?php } ?>
    </div>
</div>
</body>
</html>