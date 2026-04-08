<?php
include '../config.php';

// Proteksi Admin
if (!isset($_SESSION['admin_auth'])) {
    header("Location: index");
    exit;
}

// LOGIKA TAMBAH SERVER (Node Deployment)
if (isset($_POST['add_node'])) {
    $n = mysqli_real_escape_string($conn, $_POST['n']); 
    $i = mysqli_real_escape_string($conn, $_POST['ip']); 
    $p = mysqli_real_escape_string($conn, $_POST['pass']); 
    $l = mysqli_real_escape_string($conn, $_POST['loc']); 
    $f = mysqli_real_escape_string($conn, $_POST['flag']); 
    $dl = (int)$_POST['dev_lim'];
    $al = (int)$_POST['acc_lim']; 
    $hari = (int)$_POST['duration'];
    
    // AMBIL DATA LIMIT KUOTA (Default 0 jika tidak diisi/SSH)
    $quota = isset($_POST['quota_lim']) ? (int)$_POST['quota_lim'] : 0;
    
    $type = mysqli_real_escape_string($conn, $_POST['type']); 
    $de = date('Y-m-d', strtotime("+$hari days")); 

    // --- PERBAIKAN DI SINI: Menambahkan kolom quota_limit ke dalam Query ---
    $sql = "INSERT INTO servers (name, type, ip, password, location, flag, device_limit, account_limit, quota_limit, date_expired) 
            VALUES ('$n', '$type', '$i', '$p', '$l', '$f', $dl, $al, $quota, '$de')";
    
    if ($conn->query($sql)) {
        header("Location: dashboard?status=server_added");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provisioning Center - Zear Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root { --primary: #4361ee; --success: #10b981; --warning: #f59e0b; --danger: #ef4444; --bg: #f8faff; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: #2b3674; }
        
        .setup-card { border: none; border-radius: 35px; background: #fff; box-shadow: 0 20px 60px rgba(0,0,0,0.05); overflow: hidden; max-width: 900px; margin: 40px auto; border: 1px solid rgba(0,0,0,0.02); }
        .setup-header { background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); padding: 50px 40px; color: #fff; text-align: center; position: relative; }
        
        .srv-nav { border: none; background: rgba(255,255,255,0.1); padding: 8px; border-radius: 24px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 30px auto 0; max-width: 500px; backdrop-filter: blur(10px); }
        .srv-nav .nav-link { border: none; border-radius: 18px; font-weight: 800; font-size: 11px; color: rgba(255,255,255,0.7); padding: 12px; transition: 0.4s; text-transform: uppercase; display: flex; flex-direction: column; align-items: center; gap: 5px; background: transparent; }
        .srv-nav .nav-link i { font-size: 18px; }
        
        .srv-nav .nav-link.active { background: #fff !important; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .srv-nav .nav-link.active.ssh-t { color: var(--primary); }
        .srv-nav .nav-link.active.vmess-t { color: var(--success); }
        .srv-nav .nav-link.active.vless-t { color: var(--warning); }
        .srv-nav .nav-link.active.trojan-t { color: var(--danger); }

        .form-section { padding: 45px; }
        .form-label { font-weight: 800; font-size: 11px; color: #94a3b8; letter-spacing: 1px; margin-left: 5px; margin-bottom: 8px; }
        .form-control { border-radius: 18px; border: 1.5px solid #f1f5f9; padding: 14px 22px; margin-bottom: 25px; transition: 0.3s; background: #fdfdfd; font-size: 14px; font-weight: 600; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 5px rgba(67, 97, 238, 0.08); background: #fff; }
        
        .btn-deploy { border-radius: 20px; padding: 18px; font-weight: 800; letter-spacing: 1px; transition: 0.4s; border: none; color: #fff; text-transform: uppercase; font-size: 13px; }
        .btn-ssh { background: var(--primary); box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3); }
        .btn-vmess { background: var(--success); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3); }
        .btn-vless { background: var(--warning); box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3); }
        .btn-trojan { background: var(--danger); box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3); }
        
        .btn-deploy:hover { transform: translateY(-3px); filter: brightness(1.1); }
        .back-link { text-decoration: none; color: #94a3b8; font-weight: 800; font-size: 12px; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; }
        .back-link:hover { color: var(--primary); }
    </style>
</head>
<body>

<div class="container py-5">
    <a href="dashboard" class="back-link animate__animated animate__fadeInLeft">
        <i class="fas fa-arrow-left"></i> BACK TO INFRASTRUCTURE HUB
    </a>

    <div class="setup-card animate__animated animate__zoomIn">
        <div class="setup-header">
            <h3 class="fw-800 mb-2">Deploy New Infrastructure</h3>
            <p class="opacity-75 mb-0 small">Automated Node Provisioning System</p>
            
            <div class="nav srv-nav" id="srvTab">
                <button class="nav-link active ssh-t" data-bs-toggle="tab" data-bs-target="#ssh"><i class="fas fa-terminal"></i>SSH</button>
                <button class="nav-link vmess-t" data-bs-toggle="tab" data-bs-target="#vmess"><i class="fas fa-broadcast-tower"></i>VMESS</button>
                <button class="nav-link vless-t" data-bs-toggle="tab" data-bs-target="#vless"><i class="fas fa-project-diagram"></i>VLESS</button>
                <button class="nav-link trojan-t" data-bs-toggle="tab" data-bs-target="#trojan"><i class="fas fa-user-secret"></i>TRJN</button>
            </div>
        </div>
        
        <div class="tab-content form-section mt-2">
            <?php 
            $types = [
                'ssh' => ['label' => 'SSH TUNNEL', 'class' => 'btn-ssh', 'placeholder' => 'GERMANY SSH'],
                'vmess' => ['label' => 'VMESS XRAY', 'class' => 'btn-vmess', 'placeholder' => 'GERMANY VMESS'],
                'vless' => ['label' => 'VLESS XRAY', 'class' => 'btn-vless', 'placeholder' => 'GERMANY VLESS'],
                'trojan' => ['label' => 'TROJAN GFW', 'class' => 'btn-trojan', 'placeholder' => 'GERMANY TROJAN']
            ];
            foreach($types as $id => $attr):
            ?>
            <div class="tab-pane fade <?= ($id == 'ssh') ? 'show active' : '' ?>" id="<?= $id ?>">
                <form method="POST">
                    <input type="hidden" name="type" value="<?= $id ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-uppercase">Node Identifier</label>
                            <input type="text" name="n" class="form-control" placeholder="<?= $attr['placeholder'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase">IP Address / Hostname</label>
                            <input type="text" name="ip" class="form-control" placeholder="127.0.0.1 / host.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase">Root Authentication</label>
                            <input type="password" name="pass" class="form-control" placeholder="Server Password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase">Location Name</label>
                            <input type="text" name="loc" class="form-control" placeholder="Germany" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-uppercase">Flag Asset URL</label>
                            <input type="text" name="flag" class="form-control" placeholder="https://site.com/flag.png" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase">Multi-Login Limit</label>
                            <input type="number" name="dev_lim" class="form-control" value="2" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase">Account Limit</label>
                            <input type="number" name="acc_lim" class="form-control" value="50" required>
                        </div>

                        <?php if($id !== 'ssh'): ?>
                        <div class="col-md-12">
                            <label class="form-label text-uppercase">Quota Limit (GB)</label>
                            <input type="number" name="quota_lim" class="form-control" value="10" placeholder="Contoh: 10">
                        </div>
                        <?php endif; ?>

                        <div class="col-md-12">
                            <label class="form-label text-uppercase">Node Lifespan (Days)</label>
                            <input type="number" name="duration" class="form-control" value="30" required>
                        </div>
                    </div>
                    <button name="add_node" class="btn-deploy <?= $attr['class'] ?> w-100 mt-3">
                        <i class="fas fa-rocket me-2"></i> DEPLOY <?= $attr['label'] ?> INFRASTRUCTURE
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
