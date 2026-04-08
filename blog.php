<?php 
include 'config.php'; // Narik koneksi database
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog & Tutorial - ZearGames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8faff; color: #2b3674; }
        .blog-card { border: none; border-radius: 20px; transition: 0.3s; background: #fff; overflow: hidden; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .blog-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
        .img-box { height: 200px; background: #eee; overflow: hidden; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }
        .category-badge { font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 5px 12px; border-radius: 50px; background: rgba(67, 97, 238, 0.1); color: #4361ee; margin-bottom: 10px; display: inline-block; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-800 text-primary">ZEARGAMES BLOG</h2>
        <p class="text-muted">Tutorial, Berita, dan Update Server Terbaru</p>
    </div>

    <div class="row g-4">
        <?php
        $res = $conn->query("SELECT * FROM blog ORDER BY id DESC");
        if($res->num_rows > 0):
            while($b = $res->fetch_assoc()):
        ?>
        <div class="col-md-4">
            <div class="blog-card">
                <div class="img-box">
                    <img src="uploads/blog/<?= $b['image'] ?>" alt="<?= $b['title'] ?>">
                </div>
                <div class="p-4">
                    <span class="category-badge"><?= $b['category'] ?></span>
                    <h5 class="fw-800 mb-2"><?= $b['title'] ?></h5>
                    <p class="small text-muted mb-4"><?= substr(strip_tags($b['content']), 0, 100) ?>...</p>
                    <a href="read.php?slug=<?= $b['slug'] ?>" class="btn btn-primary btn-sm rounded-pill px-4 fw-700">Baca Tutorial</a>
                </div>
            </div>
        </div>
        <?php 
            endwhile; 
        else:
            echo "<div class='col-12 text-center py-5'><p class='text-muted'>Belum ada artikel yang diposting.</p></div>";
        endif;
        ?>
    </div>
</div>

</body>
</html>
