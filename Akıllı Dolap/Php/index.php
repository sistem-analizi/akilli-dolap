<?php
$host = "localhost";
$kullanici = "root"; 
$sifre = "";         
$db_adi = "akillidolap_db";

$baglanti = new mysqli($host, $kullanici, $sifre, $db_adi);

if ($baglanti->connect_error) {
    die("Veritabanı bağlantı hatası: " . $baglanti->connect_error);
}

$sql = "SELECT id, dolu_mu FROM dolaplar";
$sonuc = $baglanti->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5"> 
    
    <title>Akıllı Dolap Kontrol</title>
    <style>
        body { font-family: sans-serif; text-align: center; background-color: #f4f4f4; }
        .konteyner { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: 50px; }
        
        .dolap {
            width: 150px; height: 150px; color: white; display: flex; flex-direction: column;
            align-items: center; justify-content: center; border-radius: 10px; 
            font-size: 20px; font-weight: bold; box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
        }
        
        .dolu { background-color: #d9534f; } /* Kırmızı */
        .bos { background-color: #5cb85c; }  /* Yeşil */
        
        .durum-yazi { font-size: 14px; margin-top: 10px; }
    </style>
</head>
<body>
    <h2>Akıllı Dolap Kontrol Paneli</h2>
    
    <div class="konteyner">
        <?php
        if ($sonuc->num_rows > 0) {
            while($satir = $sonuc->fetch_assoc()) {
                $renkSinifi = $satir["dolu_mu"] ? "dolu" : "bos";
                $durumYazisi = $satir["dolu_mu"] ? "DOLU" : "BOŞ";
                
                echo "<div class='dolap " . $renkSinifi . "'>";
                echo "<span>Dolap " . $satir["id"] . "</span>";
                echo "<span class='durum-yazi'>" . $durumYazisi . "</span>";
                echo "</div>";
            }
        } else {
            echo "<p>Veritabanında dolap bulunamadı.</p>";
        }
        $baglanti->close(); 
        ?>
    </div>
</body>
</html>