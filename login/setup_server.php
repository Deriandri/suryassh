<?php 
session_start();

/**
 * --- PROTEKSI KEAMANAN ZEARGAMES ---
 * Mengecek apakah Admin sudah login. Jika belum, otomatis ditendang
 * ke halaman login utama (index.php).
 */
if (!isset($_SESSION['admin_auth'])) {
    header("Location: index.php"); 
    exit;
}

// 1. Koneksi Database
include_once '../config.php'; 

if (!$conn) {
    die("Database Connection Failure.");
}

$tab = isset($_GET['type']) ? $_GET['type'] : 'ssh';

// --- LOGIKA PROSES (SERVER SIDE) ---

// A. Hapus Single
if (isset($_GET['delete_id'])) {
    $id_del = (int)$_GET['delete_id'];
    if ($conn->query("DELETE FROM servers WHERE id = $id_del")) {
        $_SESSION['msg'] = "Node berhasil dihapus!";
    }
    header("Location: setup_server.php?type=$tab");
    exit;
}

// B. Mass Delete
if (isset($_POST['mass_delete_srv'])) {
    if (!empty($_POST['selected_servers'])) {
        $deleted_count = 0;
        foreach ($_POST['selected_servers'] as $id_srv) {
            $id_srv = (int)$id_srv;
            if ($conn->query("DELETE FROM servers WHERE id = $id_srv")) { $deleted_count++; }
        }
        $_SESSION['msg'] = "$deleted_count Node berhasil dibersihkan!";
    }
    header("Location: setup_server.php?type=$tab");
    exit;
}

// C. Tambah Server
if (isset($_POST['add_node'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $ip = mysqli_real_escape_string($conn, $_POST['ip']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);
    $loc = mysqli_real_escape_string($conn, $_POST['location']); 
    $flag = mysqli_real_escape_string($conn, $_POST['flag']);
    $limit = (int)$_POST['account_limit'];
    $dev_lim = (int)$_POST['device_limit'];
    $quota = (int)$_POST['quota_limit'];
    $type = $_POST['type'];
    $duration = (int)$_POST['duration_days'];

    $sql = "INSERT INTO servers (name, ip, password, location, flag, account_limit, device_limit, quota_limit, type, date_expired, daily_used) 
            VALUES ('$name', '$ip', '$pass', '$loc', '$flag', '$limit', '$dev_lim', '$quota', '$type', DATE_ADD(CURDATE(), INTERVAL $duration DAY), 0)";
    
    if ($conn->query($sql)) { $_SESSION['msg'] = "Node $name berhasil di-deploy!"; }
    header("Location: setup_server.php?type=$type");
    exit;
}

// D. Update Server
if (isset($_POST['update_node'])) {
    $id = (int)$_POST['node_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $ip = mysqli_real_escape_string($conn, $_POST['ip']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);
    $loc = mysqli_real_escape_string($conn, $_POST['location']); 
    $flag = mysqli_real_escape_string($conn, $_POST['flag']);
    $limit = (int)$_POST['account_limit'];
    $dev_lim = (int)$_POST['device_limit'];
    $quota = (int)$_POST['quota_limit'];
    $renew = (int)$_POST['renew_days'];
    $type = $_POST['type'];

    $conn->query("UPDATE servers SET name='$name', ip='$ip', password='$pass', location='$loc', flag='$flag', account_limit='$limit', device_limit='$dev_lim', quota_limit='$quota', type='$type' WHERE id=$id");
    
    if ($renew > 0) {
        $conn->query("UPDATE servers SET date_expired = DATE_ADD(CURDATE(), INTERVAL $renew DAY) WHERE id=$id");
    }

    $_SESSION['msg'] = "Data Node $name diperbarui!";
    header("Location: setup_server.php?type=$type");
    exit;
}

// --- CONFIG PAGINATION & SEARCH ---
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit_per_page = 7;
$offset = ($page - 1) * $limit_per_page;

$where_clause = "WHERE type = '$tab'";
if (!empty($search)) { $where_clause .= " AND (name LIKE '%$search%' OR ip LIKE '%$search%' OR location LIKE '%$search%')"; }

$total_rows_res = $conn->query("SELECT COUNT(*) as total FROM servers $where_clause");
$total_rows = ($total_rows_res) ? $total_rows_res->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_rows / $limit_per_page);

$tab_colors = ['ssh' => '#3b82f6', 'vmess' => '#10b981', 'vless' => '#f59e0b', 'trojan' => '#ef4444'];
$active_color = isset($tab_colors[$tab]) ? $tab_colors[$tab] : '#3b82f6';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Control Panel</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { 
            --p-admin: <?= $active_color ?>; 
            --bg-site: #f4f7fe; 
            --bg-card: #ffffff; 
            --text-main: #2b3674; 
            --text-muted: #a3aed0; 
            --border-color: #e2e8f0; 
        }

        body { 
            background-color: var(--bg-site); 
            color: var(--text-main); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; padding: 0;
        }

        .admin-wrapper { padding: 25px 15px 100px; max-width: 1000px; margin: 0 auto; }

        /* TOP NAVIGATION MENU */
        .top-nav-menu { 
            display: flex; justify-content: space-between; align-items: center; 
            background: #fff; padding: 15px 25px; border-radius: 20px; 
            margin-bottom: 20px; box-shadow: 0 10px 30px rgba(165,182,209,0.1);
        }
        .nav-brand { font-weight: 800; font-size: 16px; color: var(--text-main); }
        .nav-actions { display: flex; gap: 10px; }

        /* PROTOCOL TABS */
        .nav-infra { display: flex; gap: 8px; margin-bottom: 20px; overflow-x: auto; padding-bottom: 5px; scrollbar-width: none; }
        .nav-infra::-webkit-scrollbar { display: none; }
        .tab-item { 
            padding: 10px 20px; border-radius: 14px; background: #fff; border: 1px solid transparent; 
            color: var(--text-muted); text-decoration: none; font-weight: 700; font-size: 11px; white-space: nowrap; transition: 0.3s; 
        }
        .tab-item.active { background: var(--p-admin); color: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }

        /* SEARCH BAR */
        .search-container { position: relative; margin-bottom: 20px; }
        .search-container input { 
            width: 100%; padding: 12px 20px 12px 50px; border-radius: 18px; border: 1px solid var(--border-color); 
            background: #fff; font-size: 13px; font-weight: 600; color: var(--text-main); transition: 0.3s; 
        }
        .search-container i { position: absolute; left: 20px; top: 14px; color: var(--text-muted); }

        /* INFRA CARDS */
        .infra-card { 
            background: #fff; border-radius: 24px; border: 1px solid var(--border-color); 
            padding: 20px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; transition: 0.3s; 
        }
        .infra-card:hover { transform: translateY(-2px); border-color: var(--p-admin); box-shadow: 0 10px 25px rgba(165,182,209,0.1); }
        .infra-flag { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #f1f5f9; }

        /* PILLS */
        .status-badge { font-size: 8px; font-weight: 900; padding: 5px 12px; border-radius: 50px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 5px; }
        .bg-online { background: #ecfdf5; color: #10b981; }
        .bg-offline { background: #fef2f2; color: #ef4444; }
        .stat-pill { background: #f8fafc; color: var(--text-muted); padding: 5px 12px; border-radius: 12px; font-size: 10px; font-weight: 700; border: 1px solid var(--border-color); display: inline-flex; align-items: center; gap: 6px; }

        /* ACTIONS */
        .btn-action { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: none; transition: 0.2s; cursor: pointer; text-decoration: none; }
        .btn-edit { background: #f0f3ff; color: #4361ee; }
        .btn-del { background: #fff1f2; color: #ef4444; }

        /* NAV BUTTONS */
        .btn-nav-light { background: #f4f7fe; color: var(--text-main); font-size: 11px; font-weight: 800; border-radius: 12px; padding: 8px 15px; text-decoration: none; border: 1px solid #e2e8f0; }
        .btn-nav-primary { background: var(--p-admin); color: #fff; font-size: 11px; font-weight: 800; border-radius: 12px; padding: 8px 15px; border: none; transition: 0.3s; }
        .btn-nav-primary:hover { transform: translateY(-2px); opacity: 0.9; }

        /* MODAL */
        .modal-content { border-radius: 30px; border: none; background: #fff; overflow: hidden; }
        .modal-header { padding: 25px 30px 10px; border: none; }
        .modal-title { color: var(--p-admin); font-weight: 800; font-size: 1.2rem; }
        .form-label-elite { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 0.5px; }
        .input-elite { width: 100%; padding: 14px 18px; border-radius: 14px; border: 1px solid var(--border-color); background: #f8fafc; font-weight: 600; font-size: 14px; color: var(--text-main); margin-bottom: 15px; }
        .btn-submit-node { background: var(--p-admin); color: #fff; border: none; padding: 16px; border-radius: 18px; font-weight: 800; width: 100%; transition: 0.3s; cursor: pointer; }

        /* PAGINATION */
        .pagination-elite { display: flex; justify-content: center; gap: 8px; margin-top: 30px; }
        .page-link-elite { padding: 10px 18px; border-radius: 12px; background: #fff; border: 1px solid var(--border-color); color: var(--text-main); text-decoration: none; font-weight: 800; font-size: 12px; }
        .page-link-elite.active { background: var(--p-admin); color: #fff; }

        @media (max-width: 768px) {
            .top-nav-menu { flex-direction: column; gap: 15px; text-align: center; }
            .infra-card { flex-direction: column; align-items: flex-start; gap: 15px; }
            .right-side { width: 100%; justify-content: space-between; display: flex; flex-wrap: wrap; gap: 8px; border-top: 1px solid var(--border-color); padding-top: 15px; }
        }
    </style>
</head>
<body>

<div class="admin-wrapper">
    <!-- NAVIGATION BAR -->
    <div class="top-nav-menu shadow-sm">
        <div class="nav-brand"><i class="fas fa-network-wired me-2"></i> Infrastructure</div>
        <div class="nav-actions">
            <a href="dashboard.php" class="btn-nav-light">
                <i class="fas fa-arrow-left me-1"></i> DASHBOARD
            </a>
            <button type="button" class="btn-nav-primary" onclick="openAddModal()">
                <i class="fas fa-plus me-1"></i> DEPLOY NODE
            </button>
            <a href="logout.php" class="nav-link-admin text-danger ms-auto"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>
        </div>
    </div>

    <!-- PROTOCOL TABS -->
    <div class="nav-infra">
        <a href="?type=ssh" class="tab-item <?= ($tab == 'ssh') ? 'active' : '' ?>">SSH WEBSOCKET</a>
        <a href="?type=vmess" class="tab-item <?= ($tab == 'vmess') ? 'active' : '' ?>">V2RAY VMESS</a>
        <a href="?type=vless" class="tab-item <?= ($tab == 'vless') ? 'active' : '' ?>">V2RAY VLESS</a>
        <a href="?type=trojan" class="tab-item <?= ($tab == 'trojan') ? 'active' : '' ?>">V2RAY TROJAN</a>
    </div>

    <!-- SEARCH BAR -->
    <form method="GET" class="search-container">
        <input type="hidden" name="type" value="<?= $tab ?>">
        <i class="fas fa-search"></i>
        <input type="text" name="q" placeholder="Cari nama, IP, atau lokasi server..." value="<?= htmlspecialchars($search) ?>" onchange="this.form.submit()">
    </form>

    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4 fw-bold small" style="background:#ecfdf5; color:#10b981;">
            <i class="fas fa-check-circle me-2"></i> <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
        </div>
    <?php endif; ?>

    <!-- MASS ACTION FORM -->
    <form method="POST" onsubmit="return confirm('Hapus semua node yang dipilih?')">
        <div class="d-flex gap-2 mb-3">
            <button type="button" id="selectAllBtn" class="btn btn-sm btn-white border fw-800 rounded-pill px-3 shadow-xs" style="font-size:10px; background:#fff; color:var(--text-main);">SELECT ALL</button>
            <button type="submit" name="mass_delete_srv" class="btn btn-sm btn-danger fw-800 rounded-pill px-3 shadow-sm" style="font-size:10px;">MASS DESTROY</button>
        </div>

        <div class="infra-list">
            <?php
            $servers = $conn->query("SELECT * FROM servers $where_clause ORDER BY id DESC LIMIT $limit_per_page OFFSET $offset");
            if($servers && $servers->num_rows > 0):
            while($s = $servers->fetch_assoc()):
                $online = @fsockopen($s['ip'], 443, $errno, $errstr, 1.5);
                $days_left = (strtotime($s['date_expired'] ?? date('Y-m-d')) - time() > 0) ? ceil((strtotime($s['date_expired']) - time()) / 86400) : 0;
            ?>
            <div class="infra-card shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <input type="checkbox" name="selected_servers[]" value="<?= $s['id'] ?>" class="srv-idx" style="width:18px; height:18px; accent-color:var(--p-admin); cursor:pointer;">
                    <img src="<?= $s['flag'] ?>" class="infra-flag" onerror="this.src='https://flagcdn.com/w80/un.png'">
                    <div>
                        <h6 class="mb-0 fw-bold" style="font-size:13px;"><?= strtoupper(htmlspecialchars($s['name'])) ?></h6>
                        <small class="text-muted d-block" style="font-size:10px;"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($s['location'] ?? 'Unknown') ?></small>
                        <small class="text-muted d-block" style="font-size:10px; font-family:monospace; color:var(--p-admin);"><i class="fas fa-link me-1"></i> <?= htmlspecialchars($s['ip']) ?></small>
                    </div>
                </div>

                <div class="right-side d-flex align-items-center gap-2">
                    <span class="status-badge <?= $online ? 'bg-online' : 'bg-offline' ?>"><?= $online ? 'ONLINE' : 'OFFLINE' ?></span>
                    <span class="stat-pill"><i class="fas fa-users"></i> <?= $s['daily_used'] ?>/<?= $s['account_limit'] ?></span>
                    <span class="stat-pill"><i class="fas fa-mobile-alt"></i> <?= $s['device_limit'] ?> DEV</span>
                    <span class="stat-pill" style="color:<?= ($days_left < 5) ? '#ef4444' : 'inherit' ?>"><i class="fas fa-calendar-day"></i> <?= $days_left ?>D</span>
                    <div class="d-flex gap-2 ms-2">
                        <button type="button" class="btn-action btn-edit" onclick='openEditModal(<?= json_encode($s) ?>)'><i class="fas fa-edit"></i></button>
                        <button type="button" class="btn-action btn-del" onclick="confirmDel(<?= $s['id'] ?>)"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
            </div>
            <?php if($online) fclose($online); endwhile; else: ?>
                <div class="text-center py-5 bg-white rounded-5 border border-dashed opacity-50">Node tidak ditemukan.</div>
            <?php endif; ?>
        </div>
    </form>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination-elite">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?type=<?= $tab ?>&q=<?= $search ?>&p=<?= $i ?>" class="page-link-elite <?= ($page == $i) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL TAMBAH & EDIT -->
<div class="modal fade" id="nodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-800" id="modalTitle">Konfigurasi Node</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" id="nodeForm">
                    <input type="hidden" name="node_id" id="val_id">
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label-elite">Node Name</label><input type="text" name="name" id="val_name" class="input-elite" required></div>
                        <div class="col-6"><label class="form-label-elite">IP / Host</label><input type="text" name="ip" id="val_ip" class="input-elite" required></div>
                        <div class="col-12"><label class="form-label-elite">Root Password</label><input type="text" name="password" id="val_pass" class="input-elite" required></div>
                        <div class="col-12"><label class="form-label-elite">Lokasi</label><input type="text" name="location" id="val_loc" class="input-elite" required></div>
                        <div class="col-12"><label class="form-label-elite">Flag URL</label><input type="text" name="flag" id="val_flag" class="input-elite" required></div>
                        <div class="col-4"><label class="form-label-elite">Slot</label><input type="number" name="account_limit" id="val_slot" class="input-elite" required></div>
                        <div class="col-4"><label class="form-label-elite">Dev Lim</label><input type="number" name="device_limit" id="val_dev" class="input-elite" required></div>
                        <div class="col-4"><label class="form-label-elite">Quota (GB)</label><input type="number" name="quota_limit" id="val_quota" class="input-elite" required></div>
                        <div class="col-12">
                            <label class="form-label-elite">Protokol</label>
                            <select name="type" id="val_type" class="input-elite"><option value="ssh">SSH</option><option value="vmess">VMess</option><option value="vless">Vless</option><option value="trojan">Trojan</option></select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-elite" style="color:#ef4444;">Durasi / Renew (Hari)</label>
                            <input type="number" name="duration_days" id="val_duration" class="input-elite" value="30" required style="border-color:#fecaca;">
                        </div>
                    </div>
                    <button type="submit" id="btnSubmit" class="btn-submit-node shadow-sm mt-2">SIMPAN PERUBAHAN</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JS Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('selectAllBtn').onclick = function() {
    let boxes = document.querySelectorAll('.srv-idx');
    let anyUnchecked = Array.from(boxes).some(b => !b.checked);
    boxes.forEach(box => box.checked = anyUnchecked);
}

function openAddModal() {
    document.getElementById('nodeForm').reset();
    document.getElementById('modalTitle').innerText = "Deploy New Node";
    document.getElementById('btnSubmit').innerText = "DEPLOY NODE SEKARANG";
    document.getElementById('btnSubmit').name = "add_node";
    document.getElementById('val_duration').name = "duration_days";
    document.getElementById('val_duration').value = "30";
    document.getElementById('val_type').value = "<?= $tab ?>";
    new bootstrap.Modal(document.getElementById('nodeModal')).show();
}

function openEditModal(data) {
    document.getElementById('modalTitle').innerText = "Update Node Config";
    document.getElementById('btnSubmit').innerText = "SIMPAN PERUBAHAN";
    document.getElementById('btnSubmit').name = "update_node";
    
    document.getElementById('val_id').value = data.id;
    document.getElementById('val_name').value = data.name;
    document.getElementById('val_ip').value = data.ip;
    document.getElementById('val_pass').value = data.password;
    document.getElementById('val_loc').value = data.location; 
    document.getElementById('val_flag').value = data.flag;
    document.getElementById('val_slot').value = data.account_limit;
    document.getElementById('val_dev').value = data.device_limit;
    document.getElementById('val_quota').value = data.quota_limit;
    document.getElementById('val_type').value = data.type;
    document.getElementById('val_duration').name = "renew_days";
    document.getElementById('val_duration').value = "0";
    new bootstrap.Modal(document.getElementById('nodeModal')).show();
}

function confirmDel(id) { 
    if(confirm('Hapus node permanen? Data akun terhubung mungkin akan terganggu.')) { 
        window.location.href = 'setup_server.php?type=<?= $tab ?>&delete_id=' + id; 
    } 
}
</script>
</body>
</html>