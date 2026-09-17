# Website Pengaduan Siswa (PHP + MySQL)

Backend PHP + MySQL untuk sistem pengaduan siswa. Tampilan (HTML/CSS) dari
`Admin.html` dan `siswa.html` dipertahankan seutuhnya — yang diganti hanyalah
data palsu (mock data) di dalam `<script>` menjadi data asli dari database
lewat kumpulan endpoint API PHP.

## Struktur Folder

```
pengaduan-siswa/
├── admin.php              -> Halaman Panel Admin (dulu Admin.html)
├── siswa.php              -> Halaman Panel Siswa  (dulu siswa.html)
├── database.sql           -> Skema database (tabel-tabel)
├── seed_data.php          -> Isi data awal (akun admin, siswa, contoh pengaduan)
├── config/
│   └── koneksi.php        -> Pengaturan koneksi database (PDO)
├── includes/
│   ├── auth.php           -> Fungsi cek session login
│   └── fungsi.php         -> Fungsi bantu umum (response JSON, dll)
├── api/
│   ├── admin/              -> Semua endpoint khusus Admin
│   └── siswa/               -> Semua endpoint khusus Siswa
└── uploads/                -> Tempat menyimpan file lampiran pengaduan
```

## Cara Instalasi (di XAMPP / Laragon)

1. **Salin folder** `pengaduan-siswa` ke folder `htdocs` (XAMPP) atau `www` (Laragon).
2. **Buat database**: buka phpMyAdmin, lalu import file `database.sql`
   (ini akan otomatis membuat database `db_pengaduan_siswa` beserta semua tabelnya).
3. **Atur koneksi**: buka `config/koneksi.php`, sesuaikan `$user` dan `$pass`
   jika MySQL Anda tidak memakai default `root` tanpa password.
4. **Isi data awal**: buka lewat browser:
   `http://localhost/pengaduan-siswa/seed_data.php`
   Ini akan membuat:
   - Akun **Admin**: username `admin` / password `admin123`
   - 5 akun **Siswa** contoh, misalnya NIS `12345` / password `siswa123`
   - Beberapa kategori & contoh data pengaduan
5. **PENTING**: setelah seeding selesai, **hapus file `seed_data.php`**
   supaya tidak bisa diakses ulang oleh orang lain.
6. Pastikan folder `uploads/` bisa ditulis oleh server (permission 755/777
   di Linux/Mac; biasanya tidak masalah di Windows/XAMPP).
7. Buka:
   - Panel Admin : `http://localhost/pengaduan-siswa/admin.php`
   - Panel Siswa : `http://localhost/pengaduan-siswa/siswa.php`

## Cara Kerja Singkat

- Semua data (siswa, kategori, pengaduan, tanggapan) disimpan di MySQL,
  diakses lewat **PDO** dengan *prepared statement* (aman dari SQL Injection).
- Login memakai **PHP Session** — sekali login, status tersimpan di server
  sampai logout atau session habis.
- Frontend (JavaScript di dalam `admin.php`/`siswa.php`) memanggil endpoint
  di folder `api/` memakai `fetch()`, mengirim/menerima data berformat JSON.
- Setiap file di `api/*.php` sudah diberi komentar di setiap bagian penting
  supaya mudah ditelusuri kalau terjadi error. Buka tab **Network** di
  Developer Tools (F12) browser untuk melihat request & response tiap fetch.
- Setiap response API selalu berbentuk seragam:
  ```json
  { "success": true, "message": "...", "data": ... }
  ```

## Debug Cepat Kalau Error

| Gejala | Kemungkinan Penyebab |
|---|---|
| "Koneksi database gagal" | Service MySQL belum jalan, atau setting di `config/koneksi.php` salah |
| Tabel kosong / data tidak muncul | Lupa import `database.sql` atau lupa jalankan `seed_data.php` |
| Login selalu gagal | Password di database tersimpan hash, bukan teks biasa — pastikan pakai `seed_data.php`, jangan insert manual tanpa `password_hash()` |
| Upload lampiran gagal | Folder `uploads/` tidak bisa ditulis, atau ukuran file > 5MB, atau format bukan JPG/PNG/PDF |
| Redirect balik ke halaman login terus | Session PHP mati/timeout — cek pengaturan `session.gc_maxlifetime` di `php.ini` kalau perlu |

## Akun Default (setelah `seed_data.php` dijalankan)

| Peran | Username / NIS | Password |
|---|---|---|
| Admin | admin | admin123 |
| Siswa (Andi Pratama) | 12345 | siswa123 |
| Siswa (Siti Aisyah) | 12346 | siti456 |
| Siswa (Budi Santoso) | 12347 | budi789 |
| Siswa (Dewi Lestari) | 12348 | dewi321 |
| Siswa (Rizky Maulana) | 12349 | rizky654 |
