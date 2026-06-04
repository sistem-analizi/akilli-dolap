<?php
require 'config.php';

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
