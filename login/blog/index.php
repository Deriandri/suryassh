<?php
session_start();
include '../../config.php';
if (!isset($_SESSION['admin_auth'])) { header("Location: ../index"); exit; }

// Hapus artikel
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $res = $conn->query("SELECT image FROM blog WHERE id=$id");
    $data = $res->fetch_assoc();
    
    if ($data && !empty($data['image'])) {
        $path = '../../uploads/blog/'.$data['image'];
        if (file_exists($path)) { unlink($path); }
    }
    
    $conn->query("DELETE FROM blog WHERE id=$id");
    header("Location: index.php?status=deleted");
    exit;
}

$posts = $conn->query("SELECT * FROM blog ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Blog - Zear Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --bg: #f8faff; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg); color: #2b3674; }
        
        .header-section { margin-bottom: 30px; position: relative; }
        .btn-tulis { border-radius: 15px; padding: 12px 20px; font-weight: 800; box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2); }
        
        /* Tablet/Desktop Table Style */
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); background: #fff; overflow: hidden; }
        .table thead th { background: var(--primary); color: #fff; border: none; padding: 15px; font-size: 12px; text-transform: uppercase; }
        
        /* Responsive Android Style */
        @media (max-width: 768px) {
            .header-section { text-align: center; }
            .btn-tulis { width: 100%; margin-top: 15px; }
            
            /* Sembunyikan tabel asli di HP */
            .table-responsive { border: none; }
            .table thead { display: none; }
            .table tbody, .table tr, .table td { display: block; width: 100%; }
            
            .table tr { 
                margin-bottom: 20px; 
                background: #fff; 
                border-radius: 20px; 
                padding: 15px; 
                box-shadow: 0 5px 15px rgba(0,0,0,0.02);
                border: 1px solid #f0f3f9;
            }
            
            .table td { 
                border: none; 
                padding: 8px 0; 
                text-align: left; 
                display: flex; 
                align-items: center;
                justify-content: space-between;
            }
            
            /* Preview Gambar Lebih Besar di HP */
            .td-img { justify-content: center !important; padding-bottom: 15px !important; border-bottom: 1px solid #f0f3f9 !important; margin-bottom: 10px; }
            .td-img img { width: 100% !important; height: 150px !important; border-radius: 15px; }
            
            /* Label bantuan untuk data di HP */
            .table td::before { content: attr(data-label); font-weight: 800; font-size: 11px; color: #a3aed0; text-transform: uppercase; }
            
            .btn-action { width: 45% !important; height: 45px !important; margin: 0 2% !important; }
            .td-aksi { justify-content: center !important; margin-top: 10px; border-top: 1px solid #f0f3f9 !important; padding-top: 15px !important; }
        }

        .btn-action { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; transition: 0.3s; }
        .badge-cat { background: rgba(67, 97, 238, 0.1); color: var(--primary); font-weight: 800; font-size: 10px; padding: 6px 12px; border-radius: 8px; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="header-section d-md-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-800 mb-1">Manajemen Blog</h3>
            <p class="text-muted small mb-0">Kelola artikel tutorial website Bos.</p>
        </div>
        <a href="add.php" class="btn btn-primary btn-tulis">
            <i class="fas fa-plus me-2"></i> TULIS ARTIKEL
        </a>
    </div>
    
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Judul Artikel</th>
                        <th class="text-center">Kategori</th>
                        <th>Tanggal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($posts && $posts->num_rows > 0): ?>
                        <?php while($p = $posts->fetch_assoc()): ?>
                        <tr>
                            <td class="td-img" data-label="Banner">
                                <img src="../../uploads/blog/<?= $p['image'] ?>" class="rounded-3 shadow-sm" width="60" height="45" style="object-fit: cover;" onerror="this.src='https://placehold.co/200x150?text=No+Image'">
                            </td>
                            <td data-label="Judul">
                                <div>
                                    <span class="fw-700 d-block"><?= htmlspecialchars($p['title']) ?></span>
                                    <small class="text-muted d-none d-md-block">Slug: <?= htmlspecialchars($p['slug']) ?></small>
                                </div>
                            </td>
                            <td class="text-center" data-label="Kategori">
                                <span class="badge-cat"><?= htmlspecialchars($p['category']) ?></span>
                            </td>
                            <td data-label="Rilis">
                                <!-- FIX: Mengganti created_at menjadi date_created agar sesuai Database -->
                                <small class="fw-600"><?= date('d M Y', strtotime($p['date_created'] ?? 'now')) ?></small>
                            </td>
                            <td class="text-end td-aksi">
                                <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-action text-white shadow-sm">
                                    <i class="fas fa-edit"></i> <span class="d-md-none ms-2 fw-800">EDIT</span>
                                </a>
                                <a href="?del=<?= $p['id'] ?>" class="btn btn-danger btn-action shadow-sm" onclick="return confirm('Hapus?')">
                                    <i class="fas fa-trash"></i> <span class="d-md-none ms-2 fw-800">HAPUS</span>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Belum ada artikel yang dipublikasikan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 text-center text-md-start">
        <a href="../admin_dashboard.php" class="text-decoration-none text-muted small fw-800">
            <i class="fas fa-arrow-left me-2"></i> KEMBALI KE DASHBOARD
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>