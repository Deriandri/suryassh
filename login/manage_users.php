<?php
session_start();
// 1. Integrasi Path & Koneksi
include_once '../config.php'; 

/**
 * PROTEKSI KEAMANAN ZEARGAMES
 * Menjamin halaman tidak bisa diakses tanpa login admin.
 */
if (!isset($_SESSION['admin_auth'])) { 
    header("Location: index.php"); 
    exit; 
}

if (!$conn) {
    die("Database Connection Failure.");
}

// 2. FUNGSI DELETE USER VPS
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

// --- LOGIKA PROSES (TAMBAH, DELETE, RENEW) ---

if (isset($_GET['delete_id'])) {
    delete_user_ssh_vps($conn, (int)$_GET['delete_id']);
    $_SESSION['msg'] = "User successfully removed!";
    header("Location: manage_users.php?type=" . ($_GET['type'] ?? 'ssh'));
    exit;
}

if (isset($_POST['mass_kill'])) {
    if (!empty($_POST['selected_users'])) {
        foreach ($_POST['selected_users'] as $id_user) {
            delete_user_ssh_vps($conn, (int)$id_user);
        }
        $_SESSION['msg'] = "Selected users terminated!";
    }
    header("Location: manage_users.php?type=" . ($_POST['current_type'] ?? 'ssh'));
    exit;
}

if (isset($_POST['renew_user'])) {
    $id = (int)$_POST['user_id'];
    $days = (int)$_POST['renew_days'];
    $conn->query("UPDATE accounts SET date_expired = DATE_ADD(CURDATE(), INTERVAL $days DAY) WHERE id = $id");
    $_SESSION['msg'] = "User extension successful!";
    header("Location: manage_users.php?type=" . ($_POST['current_type'] ?? 'ssh'));
    exit;
}

// --- KONFIGURASI FILTER, SEARCH & PAGINATION ---
$tab = isset($_GET['type']) ? $_GET['type'] : 'ssh';
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 10; 
$offset = ($page - 1) * $limit;

$where = "WHERE protocol = '$tab'";
if (!empty($search)) $where .= " AND (username LIKE '%$search%' OR vps_ip LIKE '%$search%')";

$total_rows = $conn->query("SELECT COUNT(*) as t FROM accounts $where")->fetch_assoc()['t'] ?? 0;
$total_pages = ceil($total_rows / $limit);

// Warna Tema Protokol
$tab_colors = ['ssh' => '#3b82f6', 'vmess' => '#10b981', 'vless' => '#f59e0b', 'trojan' => '#ef4444'];
$active_color = $tab_colors[$tab] ?? '#3b82f6';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --p-admin: <?= $active_color ?>; --bg-s: #f8fafc; --c-white: #ffffff; --t-m: #1e293b; --t-mut: #64748b; --b-c: #e2e8f0; --input-bg: #f1f5f9; }
        body.dark, [data-bs-theme="dark"] body { --bg-s: #0f172a; --c-white: #1e293b; --t-m: #f8fafc; --t-mut: #94a3b8; --b-c: rgba(255,255,255,0.06); --input-bg: #0f1424; }

        body { background-color: var(--bg-s) !important; color: var(--t-m); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; padding: 0; margin: 0; }
        .admin-container { padding: 40px 15px 100px; max-width: 1000px; margin: 0 auto; }
        
        /* MENU NAVIGASI UTAMA */
        .main-nav-admin { display: flex; gap: 10px; margin-bottom: 30px; background: var(--c-white); padding: 15px; border-radius: 20px; border: 1px solid var(--b-c); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .nav-link-admin { padding: 10px 18px; border-radius: 12px; color: var(--t-mut); text-decoration: none; font-weight: 800; font-size: 11px; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .nav-link-admin:hover { background: var(--input-bg); color: var(--p-admin); }
        .nav-link-admin.active { background: var(--p-admin); color: #fff; }

        .nav-infra { display: flex; gap: 8px; margin-bottom: 25px; overflow-x: auto; padding-bottom: 5px; scrollbar-width: none; }
        .tab-item { padding: 10px 20px; border-radius: 12px; background: var(--c-white); border: 1px solid var(--b-c); color: var(--t-mut); text-decoration: none; font-weight: 700; font-size: 11px; white-space: nowrap; transition: 0.3s; }
        .tab-item.active { background: var(--p-admin); color: #fff; border-color: var(--p-admin); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

        .search-box { position: relative; margin-bottom: 20px; }
        .search-box input { width: 100%; padding: 12px 20px 12px 45px; border-radius: 15px; border: 1px solid var(--b-c); background: var(--c-white); font-size: 13px; font-weight: 600; color: var(--t-m); transition: 0.3s; }
        .search-box i { position: absolute; left: 18px; top: 14px; color: var(--t-mut); }

        .user-card { background: var(--c-white); border-radius: 20px; border: 1px solid var(--b-c); padding: 18px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; transition: 0.3s; }
        .user-card:hover { border-color: var(--p-admin); transform: translateY(-2px); }
        
        .status-pill { padding: 4px 12px; border-radius: 10px; font-size: 10px; font-weight: 700; background: var(--input-bg); color: var(--t-mut); border: 1px solid var(--b-c); }
        .btn-circle { width: 35px; height: 35px; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: none; transition: 0.2s; cursor: pointer; }
        .btn-renew { background: #f0f3ff; color: #4361ee; }
        .btn-kill { background: #fef2f2; color: #ef4444; }

        .pagination-elite { display: flex; justify-content: center; gap: 8px; margin-top: 30px; }
        .page-link-elite { padding: 8px 16px; border-radius: 10px; background: var(--c-white); border: 1px solid var(--b-c); color: var(--t-m); text-decoration: none; font-weight: 800; font-size: 12px; }
        .page-link-elite.active { background: var(--p-admin); color: #fff; border-color: var(--p-admin); }

        .modal-content { border-radius: 30px; border: none; background: var(--c-white); color: var(--t-m); }
        .input-elite { width: 100%; padding: 14px 20px; border-radius: 15px; border: 1px solid var(--b-c); background: var(--input-bg); font-weight: 600; font-size: 14px; color: var(--t-m); margin-bottom: 15px; }

        @media (max-width: 768px) { 
            .main-nav-admin { flex-wrap: wrap; }
            .user-card { flex-direction: column; align-items: flex-start; gap: 15px; } 
            .right-side { width: 100%; justify-content: space-between; display: flex; border-top: 1px solid var(--b-c); padding-top: 12px; } 
        }
    </style>
</head>
<body>

<div class="admin-container">
    <!-- MENU NAVIGASI ADMIN UTAMA -->
    <div class="main-nav-admin">
        <a href="dashboard.php" class="nav-link-admin"><i class="fas fa-th-large"></i> DASHBOARD</a>
        <a href="setup_server.php" class="nav-link-admin"><i class="fas fa-server"></i> SETUP SERVER</a>
        <a href="manage_users.php" class="nav-link-admin active"><i class="fas fa-users"></i> USER MANAGEMENT</a>
        <a href="logout.php" class="nav-link-admin text-danger ms-auto"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>
    </div>

    <div class="mb-4">
        <h1 class="fw-900 h4 mb-1" style="color:var(--t-m);">User Management</h1>
        <p class="text-muted small mb-0">Total: <?= $total_rows ?> accounts found for <?= strtoupper($tab) ?>.</p>
    </div>

    <!-- TABS PROTOKOL -->
    <div class="nav-infra">
        <a href="?type=ssh" class="tab-item <?= ($tab == 'ssh') ? 'active' : '' ?>">SSH Websocket</a>
        <a href="?type=vmess" class="tab-item <?= ($tab == 'vmess') ? 'active' : '' ?>">V2Ray VMess</a>
        <a href="?type=vless" class="tab-item <?= ($tab == 'vless') ? 'active' : '' ?>">V2Ray Vless</a>
        <a href="?type=trojan" class="tab-item <?= ($tab == 'trojan') ? 'active' : '' ?>">V2Ray Trojan</a>
    </div>

    <!-- SEARCH BAR -->
    <form method="GET" class="search-box">
        <input type="hidden" name="type" value="<?= $tab ?>">
        <i class="fas fa-search"></i>
        <input type="text" name="q" placeholder="Cari berdasarkan username atau IP VPS..." value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit()">
    </form>

    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert alert-success rounded-4 small fw-bold mb-4 border-0 shadow-sm"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
    <?php endif; ?>

    <!-- MASS ACTION FORM -->
    <form method="POST" onsubmit="return confirm('Kill and remove selected users?')">
        <input type="hidden" name="current_type" value="<?= $tab ?>">
        <div class="d-flex gap-2 mb-3">
            <button type="button" id="selectAll" class="btn btn-sm btn-light border fw-800 rounded-pill px-3" style="font-size:10px;">SELECT ALL</button>
            <button type="submit" name="mass_kill" class="btn btn-sm btn-danger fw-800 rounded-pill px-3 shadow-sm" style="font-size:10px;">MASS KILL</button>
        </div>

        <div class="user-list">
            <?php
            $users = $conn->query("SELECT * FROM accounts $where ORDER BY id DESC LIMIT $limit OFFSET $offset");
            if($users && $users->num_rows > 0):
            while($u = $users->fetch_assoc()):
                $is_exp = (strtotime($u['date_expired']) < time());
                $sisa = (strtotime($u['date_expired']) - time() > 0) ? ceil((strtotime($u['date_expired']) - time()) / 86400) : 0;
            ?>
            <div class="user-card shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <input type="checkbox" name="selected_users[]" value="<?= $u['id'] ?>" class="user-idx" style="width:18px; height:18px; accent-color:var(--p-admin);">
                    <div>
                        <h6 class="mb-0 fw-800" style="font-size:13px;"><?= htmlspecialchars($u['username']) ?></h6>
                        <small class="text-muted d-block" style="font-size:10px;"><i class="fas fa-server me-1"></i><?= $u['vps_ip'] ?></small>
                    </div>
                </div>

                <div class="right-side d-flex align-items-center gap-2">
                    <span class="status-pill <?= $is_exp ? 'text-danger border-danger-subtle' : '' ?>">
                        <i class="fas fa-calendar-alt me-1"></i><?= $is_exp ? 'EXPIRED' : $sisa.' Hari' ?>
                    </span>
                    <div class="d-flex gap-2 ms-2">
                        <button type="button" class="btn-circle btn-renew" onclick='openRenewModal(<?= json_encode($u) ?>)'><i class="fas fa-clock-rotate-left"></i></button>
                        <button type="button" class="btn-circle btn-kill" onclick="confirmKill(<?= $u['id'] ?>)"><i class="fas fa-bolt"></i></button>
                    </div>
                </div>
            </div>
            <?php endwhile; else: ?>
                <div class="text-center py-5 bg-white rounded-5 border border-dashed opacity-50">Belum ada user <?= strtoupper($tab) ?>.</div>
            <?php endif; ?>
        </div>
    </form>

    <!-- PAGINATION ELITE -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-elite">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?type=<?= $tab ?>&q=<?= $search ?>&p=<?= $i ?>" class="page-link-elite <?= ($page == $i) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL RENEW -->
<div class="modal fade" id="renewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-800" style="color:var(--p-admin);">Extend User Access</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST">
                    <input type="hidden" name="user_id" id="val_user_id">
                    <input type="hidden" name="current_type" value="<?= $tab ?>">
                    <div class="mb-3">
                        <label class="small fw-700 text-muted mb-2">Username</label>
                        <input type="text" id="val_username" class="input-elite" disabled>
                    </div>
                    <div class="mb-4">
                        <label class="small fw-700 text-danger mb-2">Renew Duration (Days)</label>
                        <input type="number" name="renew_days" class="input-elite" value="30" required style="border-color:#fecaca;">
                    </div>
                    <button type="submit" name="renew_user" class="btn w-100 fw-800 py-3 text-white shadow-sm" style="background:var(--p-admin); border-radius:18px;">EXTEND ACCESS</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sync Theme
function syncTheme() {
    if (localStorage.getItem('theme') === 'dark' || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.body.classList.add('dark');
    }
}
syncTheme();

// Check All
document.getElementById('selectAll').onclick = function() {
    let boxes = document.querySelectorAll('.user-idx');
    let anyUnchecked = Array.from(boxes).some(b => !b.checked);
    boxes.forEach(box => box.checked = anyUnchecked);
}

function openRenewModal(data) {
    document.getElementById('val_user_id').value = data.id;
    document.getElementById('val_username').value = data.username;
    new bootstrap.Modal(document.getElementById('renewModal')).show();
}

function confirmKill(id) {
    if(confirm('Hapus user ini dari database dan VPS?')) {
        window.location.href = 'manage_users.php?type=<?= $tab ?>&delete_id=' + id;
    }
}
</script>
</body>
</html>