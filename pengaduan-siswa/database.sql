-- =====================================================================
-- FILE: database.sql
-- FUNGSI: Membuat struktur database untuk Website Pengaduan Siswa
-- CARA PAKAI:
--   1. Buka phpMyAdmin (atau client MySQL lain)
--   2. Buat/pilih database kosong, lalu import file ini
--      ATAU jalankan lewat CLI: mysql -u root -p < database.sql
--   3. Setelah ini selesai, jalankan seed_data.php sekali lewat browser
--      untuk mengisi akun admin & siswa default (passwordnya di-hash
--      oleh PHP, makanya tidak ditulis manual di sini).
-- =====================================================================

-- Membuat database baru (kalau belum ada) dengan dukungan emoji/unicode
CREATE DATABASE IF NOT EXISTS db_pengaduan_siswa
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE db_pengaduan_siswa;

-- ---------------------------------------------------------------------
-- TABEL: admin
-- Menyimpan akun untuk login Panel Admin (Admin.html)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,      -- disimpan dalam bentuk HASH (password_hash), bukan teks biasa
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: siswa
-- Menyimpan data akun siswa. Dipakai untuk login di siswa.html DAN
-- untuk data master "Akun Siswa" yang dikelola admin.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nis VARCHAR(20) NOT NULL UNIQUE,     -- Nomor Induk Siswa, dipakai untuk login
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,      -- disimpan dalam bentuk HASH
  kelas VARCHAR(30) NOT NULL,
  email VARCHAR(100) DEFAULT NULL,
  no_hp VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: kategori
-- Kategori pengaduan (Fasilitas Sekolah, Perundungan, dst)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kategori (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  deskripsi VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: pengaduan
-- Data utama pengaduan yang dibuat siswa
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pengaduan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(20) NOT NULL UNIQUE,    -- kode tampilan, contoh: PGD-00001
  siswa_id INT NOT NULL,               -- FK ke siswa yang melapor
  kategori_id INT NOT NULL,            -- FK ke kategori
  judul VARCHAR(150) NOT NULL,
  deskripsi TEXT NOT NULL,
  lampiran VARCHAR(255) DEFAULT NULL,  -- nama file yang diupload (ada di folder /uploads)
  status ENUM('Menunggu','Diproses','Selesai','Ditolak') NOT NULL DEFAULT 'Menunggu',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_pengaduan_siswa FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
  CONSTRAINT fk_pengaduan_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: tanggapan
-- Percakapan balasan antara Admin dan Siswa untuk satu pengaduan
-- (dulu di JS namanya "percakapan")
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tanggapan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pengaduan_id INT NOT NULL,
  dari ENUM('Admin','Siswa') NOT NULL,  -- penanda siapa yang mengirim pesan
  pesan TEXT NOT NULL,
  waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_tanggapan_pengaduan FOREIGN KEY (pengaduan_id) REFERENCES pengaduan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- DATA AWAL: kategori (aman diisi langsung lewat SQL, tidak ada password)
-- ---------------------------------------------------------------------
INSERT INTO kategori (nama, deskripsi) VALUES
('Fasilitas Sekolah', 'Keluhan terkait fasilitas sekolah'),
('Perundungan',       'Keluhan terkait perundungan'),
('Kedisiplinan',      'Keluhan terkait kedisiplinan'),
('Kebersihan',        'Keluhan terkait kebersihan'),
('Akademik',          'Keluhan terkait akademik'),
('Lainnya',           'Keluhan lainnya');

-- Catatan: data admin, siswa, dan pengaduan contoh diisi lewat file
-- "seed_data.php" (dijalankan sekali lewat browser) karena passwordnya
-- perlu di-hash memakai fungsi password_hash() bawaan PHP.
