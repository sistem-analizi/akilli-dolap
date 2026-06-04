<?php

$env_path = __DIR__ . '/.env';
$env = file_exists($env_path) ? parse_ini_file($env_path) : [];

$db_adi = isset($env['DB_DATABASE']) && $env['DB_DATABASE'] !== ''
    ? $env['DB_DATABASE']
    : 'akillidolap.db';

$db_file = __DIR__ . '/' . $db_adi;
$ilk_kurulum = !file_exists($db_file);

try {
    $baglanti = new PDO("sqlite:" . $db_file);
    $baglanti->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    if ($ilk_kurulum) {
        $schema_yolu = __DIR__ . '/schema.sql';
        if (file_exists($schema_yolu)) {
            $sql = file_get_contents($schema_yolu);
            if ($sql !== false) {
                $baglanti->exec($sql);
            }
        }
    }
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
