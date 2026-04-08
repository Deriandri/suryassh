<?php
/**
 * ZEARGAMES CONNECTION DIAGNOSTIC
 * Gunakan file ini untuk cek kenapa server terbaca OFFLINE
 */

include_once 'config.php'; // Pastikan path benar

// Ambil 1 server untuk test
$res = $conn->query("SELECT ip FROM servers LIMIT 1");
$s = $res->fetch_assoc();

if (!$s) {
    die("Database kosong atau belum ada server yang ditambahkan.");
}

$target_ip = $s['ip'];
$port = 22;

echo "<h2>ZearGames Diagnostic Tool</h2>";
echo "Mencoba koneksi ke: <b>$target_ip</b> di Port: <b>$port</b>...<br><br>";

// TEST 1: SOCKET CONNECTION (Cek apakah Port terbuka di hosting)
$start = microtime(true);
$socket = @fsockopen($target_ip, $port, $errno, $errstr, 5);
$end = microtime(true);

if ($socket) {
    echo "<span style='color:green;'>[SUCCESS]</span> Socket terbuka (Jalur Port 22 di hosting AMAN).<br>";
    fclose($socket);
} else {
    echo "<span style='color:red;'>[FAILED]</span> Socket Error ($errno): $errstr<br>";
    echo "<b>Penyebab:</b> Hosting Bos memblokir koneksi keluar ke Port $port. Hubungi Admin Hosting untuk buka Outbound Port 22.<br>";
}

echo "Waktu respon: " . round(($end - $start), 4) . " detik.<br><hr>";

// TEST 2: SSH2 EXTENSION TEST
if (function_exists('ssh2_connect')) {
    echo "<span style='color:green;'>[SUCCESS]</span> Ekstensi SSH2 Aktif di PHP.<br>";
    
    $connection = @ssh2_connect($target_ip, $port);
    if ($connection) {
        echo "<span style='color:green;'>[SUCCESS]</span> SSH2 Berhasil jabat tangan (Handshake) dengan VPS.<br>";
    } else {
        echo "<span style='color:red;'>[FAILED]</span> SSH2 gagal koneksi ke VPS.<br>";
        echo "<b>Penyebab:</b> VPS Bos menolak koneksi dari IP Hosting ini. Jalankan 'ufw allow from IP_HOSTING' di VPS.<br>";
    }
} else {
    echo "<span style='color:red;'>[FAILED]</span> Ekstensi SSH2 tidak terbaca oleh skrip PHP.<br>";
}
?>