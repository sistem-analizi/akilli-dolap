<?php
session_start();

// Sadece normal kullanıcıların girmesine izin ver
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'kullanici') {
    header("Location: login.php");
    exit();
}

// Merkezi bağlantı dosyamızı çağırıyoruz
require 'config.php';

date_default_timezone_set('Europe/Istanbul');
$suan = date('Y-m-d H:i:s');
$kullanici_id = $_SESSION['kullanici_id'];

// Kullanıcının aktif olan dolap tahsislerini çekiyoruz
$sql = "SELECT * FROM tahsisler WHERE kullanici_id = '$kullanici_id' AND bitis_zamani > '$suan' AND aktif_mi = 1 ORDER BY id DESC";
$aktif_dolaplar = $baglanti->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Paneli - GYM LOCKER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; }
        .navbar-custom { background-color: #1e3a8a; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .locker-card { border: none; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); overflow: hidden; background: white; text-align: center; padding: 30px; margin-bottom: 20px; transition: 0.3s; }
        .locker-card:hover { transform: translateY(-5px); box-shadow: 0 15px 25px rgba(0,0,0,0.15); }
        .locker-icon { font-size: 60px; color: #1e3a8a; margin-bottom: 15px; }
        .locker-number { font-size: 2rem; font-weight: bold; color: #333; }
        .locker-pass { font-size: 2.5rem; letter-spacing: 5px; color: #dc2626; font-weight: bold; margin: 20px 0; background: #f8d7da; padding: 10px; border-radius: 10px; }
        .timer-box { font-size: 1.5rem; font-weight: bold; color: #16a34a; background: #d1fae5; padding: 10px; border-radius: 10px; display: inline-block; width: 100%; }
        .empty-state { text-align: center; padding: 50px 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <nav class="navbar navbar-custom sticky-top mb-5">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-white fw-bold" href="#"><i class="fas fa-dumbbell text-info me-2"></i> GYM LOCKER</a>
            <div class="text-white">
                <span class="me-3 fw-bold"><i class="fas fa-user-circle"></i> Merhaba, <?php echo strtoupper($_SESSION['kullanici_adi']); ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h4 class="mb-4 text-secondary"><i class="fas fa-key"></i> Aktif Dolaplarım</h4>
        
        <div class="row justify-content-center">
            <?php 
            $dolapBulundu = false;
            while($dolap = $aktif_dolaplar->fetch(PDO::FETCH_ASSOC)) { 
                $dolapBulundu = true;
            ?>
                <div class="col-md-5">
                    <div class="locker-card border-top border-primary border-5">
                        <i class="fas fa-lock locker-icon"></i>
                        <div class="locker-number">DOLAP 0<?php echo $dolap['dolap_id']; ?></div>
                        <p class="text-muted mt-2">Cihaz üzerinden girmek için şifreniz:</p>
                        <div class="locker-pass"><?php echo $dolap['atanan_sifre']; ?></div>
                        
                        <p class="text-muted mt-3 mb-1">Kalan Kullanım Süreniz:</p>
                        <div class="timer-box timer" data-bitis="<?php echo $dolap['bitis_zamani']; ?>">Hesaplanıyor...</div>
                    </div>
                </div>
            <?php } ?>

            <?php if(!$dolapBulundu) { ?>
                <div class="col-md-8">
                    <div class="empty-state">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h4 class="text-secondary">Şu an size tahsis edilmiş aktif bir dolap bulunmuyor.</h4>
                        <p class="text-muted">Lütfen resepsiyon ile iletişime geçin veya admin panelinden dolap ataması yapın.</p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Geri sayım sayacı
        function sayaclariGuncelle() {
            document.querySelectorAll('.timer').forEach(function(el) {
                var bitisMetni = el.getAttribute('data-bitis').replace(/-/g, "/"); 
                var bitisTarihi = new Date(bitisMetni).getTime();
                var fark = bitisTarihi - new Date().getTime();

                if (fark <= 0) {
                    el.innerHTML = "Süre Bitti!";
                    el.style.backgroundColor = "#fee2e2";
                    el.style.color = "#dc2626";
                } else {
                    var saat = Math.floor((fark % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var dakika = Math.floor((fark % (1000 * 60 * 60)) / (1000 * 60));
                    var saniye = Math.floor((fark % (1000 * 60)) / 1000);
                    el.innerHTML = String(saat).padStart(2, '0') + ":" + String(dakika).padStart(2, '0') + ":" + String(saniye).padStart(2, '0');
                }
            });
        }
        setInterval(sayaclariGuncelle, 1000); 
        sayaclariGuncelle();
    </script>
</body>
</html>
