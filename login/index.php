<?php
// Pastikan session dimulai di awal file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../config.php';

// 1. Cek jika sudah login, langsung lempar ke dashboard
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    header("Location: dashboard");
    exit;
}

if (isset($_POST['login'])) {
    // Gunakan trim untuk hapus spasi tak sengaja
    $u = trim($_POST['u']);
    $p = $_POST['p'];
    
    // AMANKAN: Gunakan Prepared Statement untuk cegah SQL Injection
    $stmt = $conn->prepare("SELECT password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    $result = $stmt->get_result();
    $adm = $result->fetch_assoc();

    // Verifikasi password dengan Hash (Keamanan Standar)
    if ($adm && password_verify($p, $adm['password'])) {
        $_SESSION['admin_auth'] = true;
        $_SESSION['admin_user'] = $u; // Opsional: Simpan nama admin
        
        session_regenerate_id(true); // Keamanan tambahan agar session tidak dicuri
        header("Location: dashboard");
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - ZearGames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: radial-gradient(circle at top right, #e3f2fd, #fff);
            height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0;
        }
        .login-card {
            width: 100%; max-width: 420px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            backdrop-filter: blur(10px); overflow: hidden;
        }
        .card-header-login {
            background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
            color: white; padding: 30px; text-align: center;
        }
        .input-group-text {
            background: white; border-right: none; color: #ccc;
            border-radius: 12px 0 0 12px; padding-left: 15px;
        }
        .form-control-login {
            border-radius: 0 12px 12px 0; padding: 12px 15px;
            border-left: none; border: 1px solid #eee;
        }
        .form-control-login:focus { box-shadow: none; border-color: #0d6efd; }
        .input-group:focus-within .input-group-text { border-color: #0d6efd; color: #0d6efd; }
        .btn-main {
            background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%);
            border: none; border-radius: 12px; font-weight: 700; padding: 12px;
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="card-header-login">
        <h4 class="fw-bold mb-1"><i class="fas fa-bolt me-2"></i>ZEARGAMES</h4>
        <p class="small mb-0 opacity-75">Secure Admin Panel Access</p>
    </div>
    
    <div class="card-body p-4 pt-5">
        <?php if(isset($error)): ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 text-danger border-0 small rounded-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="small fw-bold text-muted mb-1">USERNAME</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" name="u" class="form-control form-control-login" placeholder="admin" required autocomplete="username">
                </div>
            </div>

            <div class="mb-4">
                <label class="small fw-bold text-muted mb-1">PASSWORD</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="p" class="form-control form-control-login" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <button name="login" type="submit" class="btn btn-primary btn-main w-100">
                MASUK KE DASHBOARD <i class="fas fa-sign-in-alt ms-2 small"></i>
            </button>
        </form>
    </div>
</div>

</body>
</html>
