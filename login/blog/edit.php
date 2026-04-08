<?php
include '../../config.php';
if (!isset($_SESSION['admin_auth'])) { header("Location: ../index"); exit; }

$id = (int)$_GET['id'];
$res = $conn->query("SELECT * FROM blog WHERE id = $id");
$data = $res->fetch_assoc();

if (isset($_POST['edit_post'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    
    // Update data saja
    $conn->query("UPDATE blog SET title='$title', category='$category', content='$content' WHERE id=$id");
    
    // Jika ada upload gambar baru
    if (!empty($_FILES['thumbnail']['name'])) {
        $img_name = time() . '_' . $_FILES['thumbnail']['name'];
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], '../../uploads/blog/' . $img_name);
        $conn->query("UPDATE blog SET image='$img_name' WHERE id=$id");
    }
    echo "<script>alert('Update Berhasil!'); window.location='index.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Artikel - Zear Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm border-0 p-4 mx-auto" style="max-width: 700px;">
        <h4 class="fw-bold mb-4">Edit Artikel</h4>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" class="form-control mb-3" value="<?= $data['title'] ?>" required>
            <input type="text" name="category" class="form-control mb-3" value="<?= $data['category'] ?>" required>
            <textarea name="content" class="form-control mb-3" rows="10" required><?= $data['content'] ?></textarea>
            <p class="small text-muted">Gambar saat ini: <?= $data['image'] ?></p>
            <input type="file" name="thumbnail" class="form-control mb-4">
            <button name="edit_post" class="btn btn-success w-100 fw-bold">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-link w-100 text-muted mt-2">Batal</a>
        </form>
    </div>
</div>
</body>
</html>
