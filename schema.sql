
CREATE TABLE kullanicilar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kullanici_adi TEXT NOT NULL UNIQUE,
    telefon TEXT,
    sifre TEXT NOT NULL,
    rol TEXT NOT NULL DEFAULT 'kullanici'
);

INSERT INTO kullanicilar (id, kullanici_adi, telefon, sifre, rol) VALUES
(1, 'admin', '05550000000', '1234', 'admin');

CREATE TABLE tahsisler (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    dolap_id INTEGER NOT NULL,
    kullanici_id INTEGER NOT NULL,
    atanan_sifre TEXT NOT NULL,
    baslangic_zamani DATETIME NOT NULL,
    bitis_zamani DATETIME NOT NULL,
    aktif_mi INTEGER DEFAULT 1
);

CREATE INDEX idx_tahsisler_kullanici ON tahsisler(kullanici_id);
CREATE INDEX idx_tahsisler_dolap ON tahsisler(dolap_id);
CREATE INDEX idx_tahsisler_bitis ON tahsisler(bitis_zamani);

CREATE TABLE dolaplar (
    id INTEGER PRIMARY KEY,
    dolu_mu INTEGER NOT NULL DEFAULT 0
);

INSERT INTO dolaplar (id, dolu_mu) VALUES
(1, 0), (2, 0), (3, 0), (4, 0), (5, 0), (6, 0);
