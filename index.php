<?php
require 'config.php';

$dolaplar = $baglanti->query("SELECT id, dolu_mu FROM dolaplar ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
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
        if (count($dolaplar) > 0) {
            foreach ($dolaplar as $satir) {
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
        ?>
    </div>
</body>
</html>
