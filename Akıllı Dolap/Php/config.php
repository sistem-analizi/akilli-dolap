<?php
// .env dosyasını oku
$env_path = __DIR__ . '/.env';
if (!file_exists($env_path)) {
    die("HATA: .env dosyası bulunamadı! Lütfen .env.example dosyasını kopyalayarak bir .env dosyası oluşturun.");
}

$env = parse_ini_file($env_path);

// SQLite veritabanı yolunu belirle
$db_file = __DIR__ . '/' . $env['DB_DATABASE'];

// PDO ile bağlan
try {
    $baglanti = new PDO("sqlite:" . $db_file);
    $baglanti->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>