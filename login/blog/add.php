<?php
include '../../config.php'; 
if (!isset($_SESSION['admin_auth'])) { header("Location: ../index"); exit; }

if (isset($_POST['add_post'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    
    // Buat Slug Otomatis
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    // Logika Upload Gambar
    if (!empty($_FILES['thumbnail']['name'])) {
        $img_name = $_FILES['thumbnail']['name'];
        $img_temp = $_FILES['thumbnail']['tmp_name'];
        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $new_img_name = time() . '_' . rand(100,999) . '.' . $ext;
        
        // JALUR FIX: Naik 2 tingkat ke root, lalu ke uploads/blog/
        $upload_path = '../../uploads/blog/' . $new_img_name;

        if (move_uploaded_file($img_temp, $upload_path)) {
            $conn->query("INSERT INTO blog (title, slug, content, image, category) 
                          VALUES ('$title', '$slug', '$content', '$new_img_name', '$category')");
            echo "<script>alert('Berhasil Publish!'); window.location='index.php';</script>";
        } else {
            echo "<script>alert('Gagal Upload! Pastikan folder uploads/blog sudah dibuat.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Artikel - Zear Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8faff; padding: 20px; }
        .card-post { max-width: 700px; margin: auto; border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .form-control { border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0; margin-bottom: 15px; }
        .btn-publish { background: #4361ee; border: none; border-radius: 12px; padding: 12px; font-weight: 800; }
    </style>
</head>
<body>

<div class="card card-post p-4 p-md-5">
    <h4 class="fw-800 text-primary mb-4 text-center">Buat Artikel Tutorial</h4>
    <form method="POST" enctype="multipart/form-data">
        <label class="small fw-bold text-muted ms-1">JUDUL ARTIKEL</label>
        <input type="text" name="title" class="form-control" placeholder="Contoh: Cara Pakai SSH WebSocket" required>
        
        <label class="small fw-bold text-muted ms-1">KATEGORI</label>
        <input type="text" name="category" class="form-control" placeholder="SSH, VPN, Game..." required>
        
        <label class="small fw-bold text-muted ms-1">ISI KONTEN</label>
        <textarea name="content" class="form-control" rows="10" placeholder="Tulis atau paste artikel di sini..." required></textarea>
        
        <label class="small fw-bold text-muted ms-1">GAMBAR THUMBNAIL</label>
        <input type="file" name="thumbnail" class="form-control" required>
        
        <button type="submit" name="add_post" class="btn btn-primary btn-publish w-100 shadow">Publish Sekarang</button>
        <div class="text-center mt-3">
            <a href="index.php" class="text-muted small text-decoration-none fw-bold">Batal & Kembali</a>
        </div>
    </form>
</div>

</body>
</html>
