<?php 
include 'config.php';
$slug = mysqli_real_escape_string($conn, $_GET['slug']);
$res = $conn->query("SELECT * FROM blog WHERE slug = '$slug'");
$data = $res->fetch_assoc();

if(!$data) { header("Location: blog.php"); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?> - ZearGames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; font-family: sans-serif; line-height: 1.8; color: #334155; }
        .article-content img { max-width: 100%; border-radius: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container py-5" style="max-width: 800px;">
        <a href="blog.php" class="btn btn-light mb-4">← Kembali ke Blog</a>
        <img src="uploads/blog/<?= $data['image'] ?>" class="w-100 rounded-4 shadow-sm mb-4">
        <h1 class="fw-bold mb-3"><?= $data['title'] ?></h1>
        <div class="small text-muted mb-4">Kategori: <?= $data['category'] ?> | <?= date('d M Y', strtotime($data['created_at'])) ?></div>
        <div class="article-content">
            <?= nl2br($data['content']) ?>
        </div>
    </div>
</body>
</html>
