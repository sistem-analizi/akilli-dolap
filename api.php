<?php
require 'config.php';

date_default_timezone_set('Europe/Istanbul');
$suan = date('Y-m-d H:i:s');
$cikti = "";

for ($i = 1; $i <= 6; $i++) {
    $sql = "SELECT atanan_sifre FROM tahsisler WHERE dolap_id = $i AND bitis_zamani > '$suan' ORDER BY id DESC LIMIT 1";
    $sonuc = $baglanti->query($sql);
    

    $row = $sonuc->fetch(PDO::FETCH_ASSOC); 
    
    if ($row) { 
        $sifre = $row['atanan_sifre']; 
        $durum = 1; 
    } else {
        $sifre = "BOS"; 
        $durum = 0; 
    }
    $cikti .= $sifre . "-" . $durum . ",";
}
echo $cikti;
?>
