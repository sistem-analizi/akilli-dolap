<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kadi = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre'];

    $sql = "SELECT * FROM kullanicilar WHERE kullanici_adi='$kadi' AND sifre='$sifre'";
    $sonuc = $baglanti->query($sql);
    $kullanici = $sonuc->fetch(PDO::FETCH_ASSOC);

    if ($kullanici) {
        $_SESSION['kullanici_id'] = $kullanici['id'];
        $_SESSION['rol'] = $kullanici['rol'];
        $_SESSION['kullanici_adi'] = $kullanici['kullanici_adi'];

        if ($kullanici['rol'] == 'admin') {
            header("Location: admin.php"); 
        } else {
            header("Location: user.php"); 
        }
        exit();
    } else {
        $hata = "Hatalı kullanıcı adı veya şifre!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sisteme Giriş - GYM LOCKER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); 
            display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container { 
            background: rgba(255, 255, 255, 0.95); padding: 40px 30px; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); width: 100%; max-width: 400px; text-align: center;
        }
        .logo-icon { font-size: 50px; color: #1e3a8a; margin-bottom: 15px; }
        
        .input-wrapper { position: relative; margin-bottom: 20px; }
        .form-control { padding-left: 45px; border-radius: 8px; height: 50px; border: 1px solid #ced4da; }
        .form-control:focus { box-shadow: none; border-color: #3b82f6; }
        .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d; z-index: 10; }
        
        .btn-login { background: #1e3a8a; border: none; padding: 12px; font-size: 1.1rem; font-weight: bold; border-radius: 8px; transition: 0.3s; color: white; }
        .btn-login:hover { background: #152c6a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(30, 58, 138, 0.4); color: white; }
    </style>
</head>
<body>

<div class="login-container">
    <i class="fas fa-dumbbell logo-icon"></i>
    <h3 class="mb-4 fw-bold text-dark">GYM LOCKER</h3>
    
    <?php if(isset($hata)) echo "<div class='alert alert-danger p-2'><i class='fas fa-exclamation-circle'></i> $hata</div>"; ?>

    <form method="POST" action="">
        <div class="input-wrapper">
            <i class="fas fa-user input-icon"></i>
            <input type="text" name="kullanici_adi" class="form-control" placeholder="Kullanıcı Adı" required autocomplete="off">
        </div>
        <div class="input-wrapper">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" name="sifre" class="form-control" placeholder="Şifre" required>
        </div>
        <button type="submit" class="btn w-100 btn-login">GİRİŞ YAP</button>
    </form>
</div>

</body>
</html>