<?php
session_start();

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}


require 'config.php';

date_default_timezone_set('Europe/Istanbul');
$mesaj = "";
$suan = date('Y-m-d H:i:s');

if (isset($_POST['dolap_ata'])) {
    $kullanici_id = $_POST['kullanici_id'];
    $dolap_id = $_POST['dolap_id']; 
    $dolap_sifre = $_POST['dolap_sifresi'];
    $sure_dakika = (int)$_POST['sure_dakika']; 
    
    $bitis = date('Y-m-d H:i:s', strtotime("+$sure_dakika minutes", strtotime($suan)));
    

    $k_kontrol = $baglanti->query("SELECT dolap_id FROM tahsisler WHERE kullanici_id = '$kullanici_id' AND bitis_zamani > '$suan' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $d_kontrol = $baglanti->query("SELECT id FROM tahsisler WHERE dolap_id = '$dolap_id' AND bitis_zamani > '$suan' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if ($k_kontrol) {
        $mesaj = "<div class='alert alert-danger'>Müşterinin halihazırda <b>DOLAP 0".$k_kontrol['dolap_id']."</b> üzerinde aktif kullanımı var!</div>";
    } elseif ($d_kontrol) {
        $mesaj = "<div class='alert alert-danger'>Dolap 0$dolap_id zaten kullanımda!</div>";
    } else {
        $sql = "INSERT INTO tahsisler (dolap_id, kullanici_id, atanan_sifre, baslangic_zamani, bitis_zamani, aktif_mi) 
                VALUES ('$dolap_id', '$kullanici_id', '$dolap_sifre', '$suan', '$bitis', 1)";
        if ($baglanti->exec($sql)) {
            $mesaj = "<div class='alert alert-success'>Dolap 0$dolap_id için şifre atandı!</div>";
        }
    }
}

if (isset($_POST['dolabi_bosalt'])) {
    $iptal = $_POST['iptal_dolap_id'];
    $baglanti->exec("UPDATE tahsisler SET bitis_zamani = '$suan', aktif_mi = 0 WHERE dolap_id = '$iptal' AND bitis_zamani > '$suan'");
    $mesaj = "<div class='alert alert-info'>Dolap 0$iptal boşaltıldı!</div>";
}

if (isset($_POST['kullanici_ekle'])) {
    $kadi = $_POST['kullanici_adi'];
    $telefon = $_POST['telefon']; 
    $sifre = $_POST['sifre'];
    
    $var_mi = $baglanti->query("SELECT id FROM kullanicilar WHERE kullanici_adi = '$kadi'")->fetch(PDO::FETCH_ASSOC);
    if($var_mi) {
        $mesaj = "<div class='alert alert-danger'>Bu kullanıcı adı zaten mevcut!</div>";
    } else {
        $sql = "INSERT INTO kullanicilar (kullanici_adi, telefon, sifre, rol) VALUES ('$kadi', '$telefon', '$sifre', 'kullanici')";
        if ($baglanti->exec($sql)) {
            $mesaj = "<div class='alert alert-success'>Yeni üye eklendi!</div>";
        }
    }
}

if (isset($_POST['kullanici_sil'])) {
    $sil_id = (int)$_POST['sil_id'];
    if ($sil_id == $_SESSION['kullanici_id']) {
        $mesaj = "<div class='alert alert-danger'>Kendi hesabınızı silemezsiniz!</div>";
    } else {
        $baglanti->exec("DELETE FROM tahsisler WHERE kullanici_id = $sil_id");
        $baglanti->exec("DELETE FROM kullanicilar WHERE id = $sil_id");
        $mesaj = "<div class='alert alert-success'>Kullanıcı silindi.</div>";
    }
}

$dolap_durumlari = [];
for ($i = 1; $i <= 6; $i++) {
    $tahsis = $baglanti->query("SELECT t.*, k.kullanici_adi FROM tahsisler t JOIN kullanicilar k ON t.kullanici_id = k.id WHERE t.dolap_id = $i AND t.bitis_zamani > '$suan' ORDER BY t.id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($tahsis) {
        $dolap_durumlari[$i] = ['durum' => 'dolu', 'bitis' => $tahsis['bitis_zamani'], 'kullanici' => $tahsis['kullanici_adi']];
    } else {
        $dolap_durumlari[$i] = ['durum' => 'bos'];
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Akıllı Dolap Yönetim Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; }
        .navbar-custom { background-color: #1e3a8a; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .navbar-custom .nav-tabs { border-bottom: none; }
        .nav-link { color: #cbd5e1 !important; font-weight: 500; font-size: 1.1rem; padding: 15px 20px !important; transition: 0.3s; border: none !important; background-color: transparent !important; }
        .nav-link:hover { color: #fff !important; background-color: rgba(255,255,255,0.05) !important; }
        .nav-link.active { color: #fff !important; border-bottom: 3px solid #3b82f6 !important; background-color: transparent !important; }
        
        .locker-box { border-radius: 12px; padding: 50px 20px; text-align: center; color: white; font-size: 1.4rem; font-weight: bold; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .locker-bos { background-color: #16a34a; }
        .locker-dolu { background-color: #dc2626; } 
        .locker-box:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.2); }
        .timer { font-size: 1.1rem; margin-top: 10px; font-weight: normal; background: rgba(0,0,0,0.2); padding: 5px 10px; border-radius: 5px; display: inline-block; }
        
        .card { border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-radius: 10px; }
        .card-header { font-weight: bold; text-transform: uppercase; border-radius: 10px 10px 0 0 !important; }
        
        .search-icon-box { position: relative; }
        .search-icon-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d; }
        .search-icon-box input { padding-left: 40px; border-radius: 20px; }
        .filter-select { border-radius: 20px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand text-white fw-bold me-5" href="#"><i class="fas fa-dumbbell text-info me-2"></i> GYM LOCKER</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto nav-tabs border-0" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active border-0" id="dolaplar-tab" data-bs-toggle="tab" data-bs-target="#dolaplar" type="button" role="tab">
                            <i class="fas fa-th-large me-1"></i> Dolaplar
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0" id="kullanicilar-tab" data-bs-toggle="tab" data-bs-target="#kullanicilar" type="button" role="tab">
                            <i class="fas fa-users me-1"></i> Kullanıcı Yönetimi
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0" id="loglar-tab" data-bs-toggle="tab" data-bs-target="#loglar" type="button" role="tab">
                            <i class="fas fa-history me-1"></i> Geçmiş Loglar
                        </button>
                    </li>
                </ul>
                <div class="d-flex align-items-center text-white">
                    <span class="me-3"><i class="fas fa-user-shield text-warning"></i> <?php echo $_SESSION['kullanici_adi']; ?></span>
                    <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        <?php if($mesaj != "") echo $mesaj; ?>
        
        <div class="tab-content" id="myTabContent">
            
            <div class="tab-pane fade show active" id="dolaplar" role="tabpanel">
                <div class="row g-4"> 
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <div class="col-md-4">
                            <?php if ($dolap_durumlari[$i]['durum'] == 'bos'): ?>
                                <div class="locker-box locker-bos" onclick="openAtamaModal(<?= $i ?>)">
                                    DOLAP 0<?= $i ?><br><span class="fs-5 fw-normal">BOŞ</span>
                                </div>
                            <?php else: ?>
                                <div class="locker-box locker-dolu" onclick="openBilgiModal(<?= $i ?>, '<?= $dolap_durumlari[$i]['kullanici'] ?>')">
                                    DOLAP 0<?= $i ?><br><span class="fs-5 fw-normal">DOLU</span><br>
                                    <div class="timer" data-bitis="<?= $dolap_durumlari[$i]['bitis'] ?>">Yükleniyor...</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="tab-pane fade" id="kullanicilar" role="tabpanel">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white"><i class="fas fa-user-plus"></i> Yeni Üye Ekle</div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label text-muted fw-bold">Kullanıcı Adı</label>
                                        <input type="text" name="kullanici_adi" class="form-control" required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label text-muted fw-bold">Sisteme Giriş Şifresi</label>
                                        <input type="text" name="sifre" class="form-control" required>
                                    </div>
                                    <button type="submit" name="kullanici_ekle" class="btn btn-success w-100">KAYDET</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-users-cog"></i> Mevcut Üyeler</span>
                                <div class="search-icon-box" style="width: 250px;">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="kullaniciArama" class="form-control form-control-sm" placeholder="Kullanıcı Ara...">
                                </div>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-hover align-middle" id="kullaniciTablosu">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Kullanıcı Adı</th>
                                            <th>Giriş Şifresi</th>
                                            <th>Rol</th>
                                            <th class="text-end">İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $uyeler = $baglanti->query("SELECT * FROM kullanicilar ORDER BY rol ASC, id DESC");
                                        while($uye = $uyeler->fetch(PDO::FETCH_ASSOC)) {
                                            $rol_badge = ($uye['rol'] == 'admin') ? "<span class='badge bg-warning text-dark'>Admin</span>" : "<span class='badge bg-secondary'>Üye</span>";
                                            echo "<tr>";
                                            echo "<td>".$uye['id']."</td>";
                                            echo "<td><b class='text-primary'>".strtoupper($uye['kullanici_adi'])."</b></td>";
                                            echo "<td>".$uye['sifre']."</td>";
                                            echo "<td>".$rol_badge."</td>";
                                            echo "<td class='text-end'>";
                                            if ($uye['rol'] != 'admin') {
                                                echo "<form method='POST' action='' style='display:inline;' onsubmit='return confirm(\"Bu kullanıcıyı ve tüm geçmiş kayıtlarını silmek istediğinize emin misiniz?\");'>
                                                        <input type='hidden' name='sil_id' value='".$uye['id']."'>
                                                        <button type='submit' name='kullanici_sil' class='btn btn-danger btn-sm'><i class='fas fa-trash-alt'></i> SİL</button>
                                                      </form>";
                                            }
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="loglar" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-info text-dark pb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3 mt-1">
                            <span class="fs-5"><i class="fas fa-list-alt"></i> Tüm Tahsis Kayıtları</span>
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="search-icon-box">
                                    <i class="fas fa-search"></i>
                                    <input type="text" id="logKullaniciArama" class="form-control filter-select" placeholder="Kullanıcı Adına Göre Ara...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select id="logDolapFiltresi" class="form-select filter-select">
                                    <option value="">Tüm Dolaplar</option>
                                    <option value="Dolap 1">Dolap 01</option>
                                    <option value="Dolap 2">Dolap 02</option>
                                    <option value="Dolap 3">Dolap 03</option>
                                    <option value="Dolap 4">Dolap 04</option>
                                    <option value="Dolap 5">Dolap 05</option>
                                    <option value="Dolap 6">Dolap 06</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select id="logDurumFiltresi" class="form-select filter-select">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="Aktif">Sadece Aktif Olanlar</option>
                                    <option value="Süresi Doldu">Süresi Dolanlar</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body table-responsive">
                        <table class="table table-striped text-center" id="logTablosu">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Kullanıcı</th>
                                    <th>Dolap No</th>
                                    <th>Atanan Şifre</th>
                                    <th>Başlangıç Zamanı</th>
                                    <th>Bitiş Zamanı</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_log = "SELECT t.*, k.kullanici_adi FROM tahsisler t LEFT JOIN kullanicilar k ON t.kullanici_id = k.id ORDER BY t.id DESC";
                                $loglar = $baglanti->query($sql_log);
                                
                                while($log = $loglar->fetch(PDO::FETCH_ASSOC)) {
                                    $durumBadge = ($log['bitis_zamani'] > $suan) ? "<span class='badge bg-success'>Aktif</span>" : "<span class='badge bg-danger'>Süresi Doldu</span>";
                                    $kullaniciAdi = $log['kullanici_adi'] ? $log['kullanici_adi'] : "(Silinmiş Kullanıcı)";
                                    
                                    echo "<tr>";
                                    echo "<td>".$log['id']."</td>";
                                    echo "<td><b>".$kullaniciAdi."</b></td>";
                                    echo "<td>Dolap ".$log['dolap_id']."</td>";
                                    echo "<td><code>".$log['atanan_sifre']."</code></td>";
                                    echo "<td>".$log['baslangic_zamani']."</td>";
                                    echo "<td>".$log['bitis_zamani']."</td>";
                                    echo "<td>".$durumBadge."</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> 
    </div> 

    <div class="modal fade" id="atamaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">DOLAP ŞİFRE ATA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="dolap_id" id="modalDolapId">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Kullanıcı Seç:</label>
                            <select name="kullanici_id" class="form-select" required>
    <?php
    $k_sec = $baglanti->query("SELECT * FROM kullanicilar WHERE rol='kullanici'");
    while($k = $k_sec->fetch(PDO::FETCH_ASSOC)) { 
        echo "<option value='".$k['id']."'>".strtoupper($k['kullanici_adi'])." - ".$k['telefon']."</option>"; 
    }
    ?>
</select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Geçici Şifre:</label>
                            <input type="text" name="dolap_sifresi" class="form-control" placeholder="[1234]" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Kullanım Süresi (Dk):</label>
                            <input type="number" name="sure_dakika" class="form-control" placeholder="[90]" min="1" required>
                        </div>
                        <button type="submit" name="dolap_ata" class="btn btn-primary w-100 py-2 fw-bold">ONAYLA</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bilgiModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center pt-0">
                    <i class="fas fa-user-lock fa-3x text-danger mb-3 mt-2"></i>
                    <h6 class="text-muted mb-1">Mevcut Kullanıcı</h6>
                    <h3 class="fw-bold text-primary mb-4" id="modalKullaniciAdi"></h3>
                    <form method="POST" action="">
                        <input type="hidden" name="iptal_dolap_id" id="modalIptalDolapId">
                        <button type="submit" name="dolabi_bosalt" class="btn btn-outline-danger w-100 py-2 fw-bold">
                            <i class="fas fa-sign-out-alt"></i> SÜREYİ SONLANDIR VE BOŞALT
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function(){
            var activeTab = localStorage.getItem('aktifSekme');
            if(activeTab){
                var tab = new bootstrap.Tab(document.querySelector('#' + activeTab));
                tab.show();
            }
            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(btn) {
                btn.addEventListener('shown.bs.tab', function (e) {
                    localStorage.setItem('aktifSekme', e.target.id);
                });
            });
        });

        function openAtamaModal(dolapNo) {
            document.getElementById('modalDolapId').value = dolapNo;
            new bootstrap.Modal(document.getElementById('atamaModal')).show();
        }
        function openBilgiModal(dolapNo, kullaniciAdi) {
            document.getElementById('modalIptalDolapId').value = dolapNo;
            document.getElementById('modalKullaniciAdi').innerText = kullaniciAdi.toUpperCase(); 
            new bootstrap.Modal(document.getElementById('bilgiModal')).show();
        }

        function sayaclariGuncelle() {
            document.querySelectorAll('.timer').forEach(function(el) {
                var bitisMetni = el.getAttribute('data-bitis').replace(/-/g, "/"); 
                var bitisTarihi = new Date(bitisMetni).getTime();
                var fark = bitisTarihi - new Date().getTime();

                if (fark <= 0) {
                    el.innerHTML = "Süre Bitti!";
                } else {
                    var saat = Math.floor((fark % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var dakika = Math.floor((fark % (1000 * 60 * 60)) / (1000 * 60));
                    var saniye = Math.floor((fark % (1000 * 60)) / 1000);
                    el.innerHTML = String(saat).padStart(2, '0') + ":" + String(dakika).padStart(2, '0') + ":" + String(saniye).padStart(2, '0');
                }
            });
        }
        setInterval(sayaclariGuncelle, 1000); sayaclariGuncelle();


        document.getElementById('kullaniciArama').addEventListener('keyup', function() {
            let arananKelime = this.value.toLowerCase();
            let satirlar = document.querySelectorAll('#kullaniciTablosu tbody tr');
            
            satirlar.forEach(satir => {
                let isim = satir.cells[1].innerText.toLowerCase();
                if(isim.includes(arananKelime)) {
                    satir.style.display = '';
                } else {
                    satir.style.display = 'none';
                }
            });
        });

        function logFiltrele() {
            let arananKullanici = document.getElementById('logKullaniciArama').value.toLowerCase();
            let secilenDolap = document.getElementById('logDolapFiltresi').value;
            let secilenDurum = document.getElementById('logDurumFiltresi').value;
            
            let satirlar = document.querySelectorAll('#logTablosu tbody tr');

            satirlar.forEach(satir => {
                let isimMetni = satir.cells[1].innerText.toLowerCase();
                let dolapMetni = satir.cells[2].innerText;
                let durumMetni = satir.cells[6].innerText;

                let isimUyuyorMu = isimMetni.includes(arananKullanici);
                let dolapUyuyorMu = (secilenDolap === "" || dolapMetni.includes(secilenDolap));
                let durumUyuyorMu = (secilenDurum === "" || durumMetni.includes(secilenDurum));

                if (isimUyuyorMu && dolapUyuyorMu && durumUyuyorMu) {
                    satir.style.display = '';
                } else {
                    satir.style.display = 'none';
                }
            });
        }

        document.getElementById('logKullaniciArama').addEventListener('keyup', logFiltrele);
        document.getElementById('logDolapFiltresi').addEventListener('change', logFiltrele);
        document.getElementById('logDurumFiltresi').addEventListener('change', logFiltrele);
        
    </script>
</body>
</html>
