<?php
require 'config.php';

try {
    // Veritabanındaki mevcut 'admin' rolüne sahip hesabın bilgilerini güncelliyoruz
    $sql = "UPDATE kullanicilar SET kullanici_adi = 'akgun@comu.edu.tr', sifre = '123456' WHERE rol = 'admin'";
    $baglanti->exec($sql);
    
    echo "<h3 style='color: green;'>✅ Basarili! Admin hesabi 'akgun@comu.edu.tr' ve sifresi '123456' olarak guncellendi.</h3>";
    echo "<p>Guvenliginiz icin lutfen simdi GitHub uzerinden bu dosyayi (admin_ayarla.php) silin.</p>";
    echo "<a href='login.php'>Giris Ekranina Git</a>";
    
} catch(PDOException $e) {
    echo "Bir hata olustu: " . $e->getMessage();
}
?>
