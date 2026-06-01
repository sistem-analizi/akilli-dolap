<?php
require 'config.php';

// Arduino'dan gelen istek: update.php?id=X&durum=0|1
// Eski sürümde sütun adı `durum` idi ama index.php `dolu_mu` okuyor —
// schema'da `dolu_mu`'da birleştik, parametre adı geriye dönük uyum için
// `durum` olarak kaldı (Arduino kodunu kırmamak için).
if (isset($_GET['id']) && isset($_GET['durum'])) {
    $id = intval($_GET['id']);
    $durum = intval($_GET['durum']);

    try {
        $stmt = $baglanti->prepare("UPDATE dolaplar SET dolu_mu = :durum WHERE id = :id");
        $stmt->execute([':durum' => $durum, ':id' => $id]);
        echo "Basarili";
    } catch (PDOException $e) {
        echo "Hata: " . $e->getMessage();
    }
}
?>
