<?php
// 1. KONEKSI & AUTH
include '../config.php';
session_start();

if (!isset($_SESSION['admin_auth'])) { 
    header("Location: index"); 
    exit; 
}

// 2. FUNGSI DELETE USER VPS (SINKRON)
function delete_user_ssh_vps($conn, $id_user) {
    $res_u = $conn->query("SELECT * FROM accounts WHERE id = $id_user");
    if ($res_u && $acc = $res_u->fetch_assoc()) {
        $u_name = $acc['username']; 
        $u_ip   = trim($acc['vps_ip']); 
        $proto  = strtolower($acc['protocol']);
        
        $srv_res = $conn->query("SELECT password FROM servers WHERE ip = '$u_ip'");
        if ($srv_res && $srv = $srv_res->fetch_assoc()) {
            $ssh = @ssh2_connect($u_ip, 22);
            if ($ssh && @ssh2_auth_password($ssh, 'root', $srv['password'])) {
                if ($proto == 'ssh') { 
                    @ssh2_exec($ssh, "userdel -f $u_name && rm -f /etc/kyt/limit/ssh/ip/$u_name"); 
                } else {
                    @ssh2_exec($ssh, "sed -i \"/$u_name/d\" /etc/xray/config.json && systemctl restart xray");
                }
            }
        }
        $conn->query("DELETE FROM accounts WHERE id = $id_user");
        return true;
    }
    return false;
}

// 3. LOGIKA PROSES (POST/GET)
if (isset($_GET['del_user'])) {
    delete_user_ssh_vps($conn, (int)$_GET['del_user']);
    header("Location: admin_dashboard.php?status=deleted"); exit;
}

if (isset($_POST['delete_selected'])) {
    if (!empty($_POST['selected_users'])) {
        foreach ($_POST['selected_users'] as $id_user) {
            delete_user_ssh_vps($conn, (int)$id_user);
        }
    }
    header("Location: admin_dashboard.php?status=mass_deleted"); exit;
}

if (isset($_GET['del_srv'])) {
    $id = (int)$_GET['del_srv'];
    $conn->query("DELETE FROM servers WHERE id = $id");
    header("Location: admin_dashboard.php?status=srv_deleted"); exit;
}

// 4. DATA RETRIEVAL
$total_acc = $conn->query("SELECT COUNT(*) as t FROM accounts")->fetch_assoc()['t'] ?? 0;
$servers_data = $conn->query("SELECT * FROM servers ORDER BY id DESC");
$accounts_res = $conn->query("SELECT * FROM accounts ORDER BY id DESC");

// 5. HELPER FUNCTIONS
function get_sisa_hari($tgl) {
    $skrg = strtotime(date('Y-m-d')); $exp = strtotime($tgl);
    $diff = $exp - $skrg; $hari = ceil($diff / (60 * 60 * 24));
    return ($hari <= 0) ? "Expired" : $hari . " Hari";
}

function check_status($ip) {
    $fp = @fsockopen($ip, 22, $errC, $errS, 2.0);
    if($fp) { $is_online = true; fclose($fp); } else { $is_online = false; }
    
    if ($is_online) { 
        return '<span class="status-badge online"><i class="fas fa-check-circle me-1"></i>ONLINE</span>'; 
    }
    return '<span class="status-badge offline"><i class="fas fa-times-circle me-1"></i>OFFLINE</span>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Admin Dashboard - Zear Games</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --bg: #f4f7fe; --card-white: #ffffff; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: #2b3674; overflow-x: hidden; }
        
        /* MODERN MENU STYLING */
        .admin-menu-container { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 25px; }
        .menu-item { 
            background: #fff; border-radius: 20px; padding: 15px 10px; text-align: center; 
            text-decoration: none; color: #2b3674; transition: 0.3s; border: 1px solid rgba(0,0,0,0.02);
            box-shadow: 0 4px 15px rgba(165,182,209,0.05);
        }
        .menu-item:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(67, 97, 238, 0.1); border-color: var(--primary); }
        .menu-icon { width: 45px; height: 45px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 18px; }
        .bg-menu-1 { background: #eef2ff; color: #4361ee; }
        .bg-menu-2 { background: #fff7ed; color: #f59e0b; }
        .bg-menu-3 { background: #f0fdf4; color: #10b981; }
        .bg-menu-4 { background: #fef2f2; color: #ef4444; }
        .menu-text { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }

        /* DASHBOARD ELEMENTS */
        .glass-card { background: #fff; border-radius: 24px; border: none; box-shadow: 0 10px 30px rgba(165,182,209,0.1); padding: 25px; margin-bottom: 25px; }
        .stat-card { background: #fff; border-radius: 20px; padding: 20px; flex: 1; border-left: 5px solid var(--primary); }
        .btn-premium { background: var(--primary); color: #fff; border-radius: 15px; padding: 15px; font-weight: 800; border: none; transition: 0.3s; display: block; text-align: center; text-decoration: none; }
        .status-badge { padding: 4px 10px; border-radius: 8px; font-size: 9px; font-weight: 800; display: inline-flex; align-items: center; }
        .online { background: #ecfdf5; color: #10b981; }
        .offline { background: #fef2f2; color: #ef4444; }
        .info-pill { background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 10px; font-size: 9px; font-weight: 700; color: #64748b; }
        .action-btn { width: 35px; height: 35px; border-radius: 10px; border: none; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; text-decoration: none; }
        .btn-edit { background: #f0f3ff; color: var(--primary); }
        .btn-del { background: #fff1f2; color: #ef4444; }
        .form-control-search { border-radius: 15px; border: 1px solid #e2e8f0; padding: 12px 20px; font-size: 14px; width: 100%; }
        
        @media (max-width: 768px) {
            .admin-menu-container { grid-template-columns: repeat(2, 1fr); }
            .modern-table tr { display: block; background: #fff; border-radius: 20px; padding: 15px; margin-bottom: 15px; border: 1px solid #f1f5f9; }
            .modern-table td { display: block; padding: 5px 0; border: none; text-align: left !important; }
        }
    </style>
</head>
<body>

<div class="container py-4">
    <!-- TOP HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div><h4 class="fw-800 mb-0">Administrator Dashboard</h4><p class="text-muted small mb-0">Global Infrastructure Control</p></div>
        <div class="d-flex gap-2 w-100 w-md-auto">
            <div class="stat-card shadow-sm"><small class="text-muted d-block fw-700">NODES</small><span class="fw-800 h5 mb-0"><?= $servers_data->num_rows ?></span></div>
            <div class="stat-card shadow-sm"><small class="text-muted d-block fw-700">USERS</small><span class="fw-800 h5 mb-0 text-primary"><?= $total_acc ?></span></div>
        </div>
    </div>

    <!-- NEW ADMIN MENU SECTION -->
    <div class="admin-menu-container">
        <a href="blog/index.php" class="menu-item">
            <div class="menu-icon bg-menu-1"><i class="fas fa-newspaper"></i></div>
            <span class="menu-text">Blog</span>
        </a>
        <a href="setup_server.php" class="menu-item">
            <div class="menu-icon bg-menu-2"><i class="fas fa-cogs"></i></div>
            <span class="menu-text">Setup</span>
        </a>
        <a href="manage_users.php" class="menu-item">
            <div class="menu-icon bg-menu-3"><i class="fas fa-users-cog"></i></div>
            <span class="menu-text">User</span>
        </a>
        <a href="add_server.php" class="menu-item">
            <div class="menu-icon bg-menu-4"><i class="fas fa-server"></i></div>
            <span class="menu-text">Add Server</span>
        </a>
    </div>

    <?php if(isset($_GET['status'])): ?>
        <div class="alert alert-success border-0 rounded-4 shadow-sm py-3 mb-4 fw-bold small">Action processed successfully!</div>
    <?php endif; ?>

    <div class="row">
        <!-- INFRASTRUCTURE HUB -->
        <div class="col-lg-4">
            <div class="glass-card">
                <h6 class="fw-800 text-primary mb-4"><i class="fas fa-network-wired me-2"></i>Infrastructure Hub</h6>
                <?php if($servers_data->num_rows > 0): while($s = $servers_data->fetch_assoc()): ?>
                <div class="mb-4 border-bottom pb-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= $s['flag'] ?>" width="32" class="rounded shadow-sm" onerror="this.src='https://flagcdn.com/w80/un.png'">
                            <div>
                                <span class="fw-800 d-block small"><?= strtoupper(htmlspecialchars($s['name'])) ?></span>
                                <span class="text-muted" style="font-size: 9px;"><i class="fas fa-link me-1"></i><?= $s['ip'] ?></span>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="setup_server.php?edit_id=<?= $s['id'] ?>" class="action-btn btn-edit"><i class="fas fa-pen-nib"></i></a>
                            <a href="?del_srv=<?= $s['id'] ?>" class="action-btn btn-del" onclick="return confirm('Destroy node?')"><i class="fas fa-trash-alt"></i></a>
                        </div>
                    </div>
                    <div class="d-flex gap-1 mt-2 flex-wrap">
                        <?= check_status($s['ip']) ?>
                        <span class="info-pill"><?= get_sisa_hari($s['date_expired']) ?></span>
                        <span class="info-pill text-primary fw-800"><?= strtoupper($s['type']) ?></span>
                    </div>
                </div>
                <?php endwhile; else: echo "<p class='text-muted small'>No nodes deployed.</p>"; endif; ?>
            </div>
        </div>

        <!-- ACTIVE SESSIONS -->
        <div class="col-lg-8">
            <div class="glass-card">
                <div class="d-flex flex-column flex-md-row justify-content-between mb-4 gap-2">
                    <h6 class="fw-800 text-primary align-self-center mb-0"><i class="fas fa-broadcast-tower me-2"></i>Live Sessions</h6>
                    <div class="w-100 w-md-50">
                        <input type="text" id="userSearch" class="form-control-search bg-light border-0" placeholder="Search user...">
                    </div>
                </div>
                
                <form method="POST">
                    <div class="d-flex gap-2 mb-4">
                        <button type="button" id="checkAll" class="btn btn-white btn-sm fw-800 px-3 border shadow-sm" style="border-radius: 12px; font-size: 10px;">SELECT ALL</button>
                        <button type="submit" name="delete_selected" class="btn btn-warning btn-sm fw-800 px-3 text-white shadow-sm" style="border-radius: 12px; font-size: 10px;">MASS KILL</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table modern-table align-middle">
                            <tbody id="accTable">
                                <?php if($accounts_res && $accounts_res->num_rows > 0): while($acc = $accounts_res->fetch_assoc()): ?>
                                <tr class="user-row">
                                    <td width="30"><input type="checkbox" name="selected_users[]" value="<?= $acc['id'] ?>" class="user-idx form-check-input"></td>
                                    <td><span class="fw-800 small d-block"><?= htmlspecialchars($acc['username']) ?></span><span class="text-muted small" style="font-size: 10px;"><?= $acc['vps_ip'] ?></span></td>
                                    <td><span class="badge bg-primary-subtle text-primary border-primary-subtle px-2 py-1 fw-800" style="font-size: 8px; border-radius: 6px;"><?= strtoupper($acc['protocol']) ?></span></td>
                                    <td><span class="status-badge offline" style="font-size: 9px;"><?= get_sisa_hari($acc['date_expired']) ?></span></td>
                                    <td class="text-end"><a href="?del_user=<?= $acc['id'] ?>" class="action-btn btn-del" onclick="return confirm('Kill user?')"><i class="fas fa-bolt"></i></a></td>
                                </tr>
                                <?php endwhile; else: echo "<tr><td colspan='5' class='text-center py-5 opacity-50 text-muted'>No active sessions found.</td></tr>"; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Search Function
    document.getElementById('userSearch').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.user-row').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
        });
    });

    // Check All Function
    document.getElementById('checkAll').onclick = function() {
        let boxes = document.querySelectorAll('.user-idx');
        let anyUnchecked = Array.from(boxes).some(b => !b.checked);
        boxes.forEach(box => box.checked = anyUnchecked);
    }
</script>
</body>
</html>