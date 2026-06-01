<?php
$baglanti = new mysqli("localhost", "root", "", "akillidolap_db");

if(isset($_GET['id']) && isset($_GET['durum'])) {
    $id = intval($_GET['id']);
    $durum = intval($_GET['durum']);
    
    $sql = "UPDATE dolaplar SET durum = $durum WHERE id = $id";
    
    if ($baglanti->query($sql) === TRUE) {
        echo "Basarili";
    } else {
        echo "Hata: " . $baglanti->error;
    }
}
$baglanti->close();
?>