<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'kullanici') {
    header("Location: login.php");
    exit();
}

require 'config.php';
$kullanici_id = $_SESSION['kullanici_id'];
date_default_timezone_set('Europe/Istanbul');
$suan = date('Y-m-d H:i:s');

$stmt = $baglanti->prepare("SELECT * FROM tahsisler
    WHERE kullanici_id = :uid
      AND baslangic_zamani <= :suan
      AND bitis_zamani >= :suan
    ORDER BY id DESC LIMIT 1");
$stmt->execute([':uid' => $kullanici_id, ':suan' => $suan]);
$tahsis = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Müşteri Paneli - Akıllı Dolap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; }
        .kutu { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); text-align: center; width: 90%; max-width: 400px; }
        .sifre-gosterge { font-size: 40px; letter-spacing: 5px; font-weight: bold; color: #0d6efd; background: #e9ecef; padding: 15px; border-radius: 10px; margin: 20px 0; }
    </style>
</head>
<body>

<div class="kutu">
    <h4>Merhaba, <?php echo $_SESSION['kullanici_adi']; ?> 👋</h4>
    <p class="text-muted">Aktif Dolap Bilgileriniz Aşağıdadır</p>

    <?php if ($tahsis): ?>
        <h5 class="mt-4">Kilitli Dolabınız: <b>NO <?php echo $tahsis['dolap_id']; ?></b></h5>
        <div class="sifre-gosterge">
            <?php echo $tahsis['atanan_sifre']; ?>
        </div>
        <p class="text-danger small">Dikkat: Bu şifre <b><?php echo $tahsis['bitis_zamani']; ?></b> tarihine kadar geçerlidir.</p>
    <?php else: ?>
        <div class="alert alert-warning mt-4">Şu an adınıza tahsis edilmiş aktif bir dolap bulunmamaktadır veya süreniz dolmuştur.</div>
    <?php endif; ?>

    <a href="logout.php" class="btn btn-outline-danger mt-3 w-100">Güvenli Çıkış</a>
</div>

</body>
</html>
