<?php
require 'config.php';

echo "<h3>Veritabanı Migration İşlemi Başlatılıyor...</h3>";

// 1. Tabloları Oluşturma SQL Komutları
$sql = "
CREATE TABLE IF NOT EXISTS kullanicilar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kullanici_adi TEXT NOT NULL,
    telefon TEXT,
    sifre TEXT NOT NULL,
    rol TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS tahsisler (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    dolap_id INTEGER NOT NULL,
    kullanici_id INTEGER NOT NULL,
    atanan_sifre TEXT NOT NULL,
    baslangic_zamani DATETIME NOT NULL,
    bitis_zamani DATETIME NOT NULL,
    aktif_mi INTEGER DEFAULT 1
);
";

// Komutları çalıştır
try {
    $baglanti->exec($sql);
    echo "<p style='color:green;'>✓ Tablolar başarıyla oluşturuldu veya zaten mevcut.</p>";
} catch (PDOException $e) {
    die("<p style='color:red;'>Tablo oluşturma hatası: " . $e->getMessage() . "</p>");
}

// 2. Varsayılan Admin Hesabını Tohumlama (Seeder)
$adminVarMi = $baglanti->query("SELECT id FROM kullanicilar WHERE kullanici_adi = 'admin'")->fetch(PDO::FETCH_ASSOC);

if (!$adminVarMi) {
    $baglanti->exec("INSERT INTO kullanicilar (kullanici_adi, telefon, sifre, rol) VALUES ('admin', '05550000000', '1234', 'admin')");
    echo "<p style='color:blue;'>✓ Varsayılan Admin hesabı tohumlandı. (Kullanıcı Adı: admin, Şifre: 1234)</p>";
} else {
    echo "<p style='color:orange;'>- Admin hesabı zaten mevcut, tohumlama atlandı.</p>";
}

echo "<h3>Migration Tamamlandı! <a href='login.php'>Sisteme Git</a></h3>";
?>