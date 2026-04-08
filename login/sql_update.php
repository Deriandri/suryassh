<?php
include_once '../config.php';

// Proteksi Admin (Hanya admin login yang bisa jalankan update)
if (!isset($_SESSION['admin_auth'])) {
    die("Akses dilarang! Silakan login admin dulu, Bos.");
}

echo "<div style='font-family: sans-serif; padding: 20px; background: #f8fafc; min-height: 100vh;'>";
echo "<h2 style='color: #4361ee;'>ZearGames Database Updater</h2>";
echo "<hr>";

// 1. Tambah kolom type di tabel accounts (Untuk membedakan SSH, Vmess, Vless, Trojan)
$q1 = "ALTER TABLE accounts ADD COLUMN IF NOT EXISTS protocol VARCHAR(50) DEFAULT 'ssh' AFTER device_limit";
if ($conn->query($q1)) {
    echo "<p style='color: green;'>✔ Kolom 'protocol' berhasil ditambahkan ke tabel accounts.</p>";
} else {
    echo "<p style='color: red;'>✘ Gagal update tabel accounts: " . $conn->error . "</p>";
}

// 2. Tambah kolom v2ray_uuid jika belum ada (Kadang installer lama belum ada)
$q2 = "ALTER TABLE servers ADD COLUMN IF NOT EXISTS v2ray_uuid VARCHAR(255) DEFAULT NULL";
$conn->query($q2);

// 3. Tambah kolom v2ray_path
$q3 = "ALTER TABLE servers ADD COLUMN IF NOT EXISTS v2ray_path VARCHAR(100) DEFAULT '/zear'";
$conn->query($q3);

// 4. Tambah kolom v2ray_transport (WS/GRPC)
$q4 = "ALTER TABLE servers ADD COLUMN IF NOT EXISTS v2ray_transport VARCHAR(50) DEFAULT 'ws'";
$conn->query($q4);

// 5. Membuat tabel Blog jika belum ada
$q5 = "CREATE TABLE IF NOT EXISTS `blog` (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    title VARCHAR(255), 
    slug VARCHAR(255), 
    content TEXT, 
    image VARCHAR(255), 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($q5)) {
    echo "<p style='color: green;'>✔ Tabel 'blog' siap digunakan.</p>";
}

echo "<hr>";
echo "<p><b>Update Selesai!</b> Database Bos sekarang sudah mendukung fitur terbaru v4.0.8.</p>";
echo "<a href='dashboard.php' style='display: inline-block; padding: 10px 20px; background: #4361ee; color: #fff; text-decoration: none; border-radius: 10px;'>Kembali ke Dashboard</a>";
echo "</div>";
?>
