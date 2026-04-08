<?php
include 'config.php';

// Ambil satu server saja untuk tes
$res = $conn->query("SELECT * FROM servers LIMIT 1");
$row = $res->fetch_assoc();

echo "<h3>Hasil Pelacakan Database ZearGames:</h3>";
echo "<pre>";
if ($row) {
    print_r($row);
} else {
    echo "Tabel 'servers' kosong, Bos!";
}
echo "</pre>";
?>
