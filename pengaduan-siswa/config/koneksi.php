<?php
/**
 * =====================================================================
 * FILE: config/koneksi.php
 * FUNGSI: Membuat satu koneksi database (PDO) yang dipakai bersama oleh
 *         SEMUA file backend (api/admin/*, api/siswa/*, seed_data.php, dll).
 *
 * CARA DEBUG KALAU ERROR "Koneksi database gagal":
 * 1. Pastikan service MySQL/MariaDB sudah menyala (xampp/laragon/dll).
 * 2. Cocokkan $host, $user, $pass, $namaDb dengan pengaturan di komputer Anda.
 * 3. Pastikan database "db_pengaduan_siswa" sudah dibuat (import database.sql).
 * =====================================================================
 */

// ---------- PENGATURAN KONEKSI (ubah sesuai server Anda) ----------
$host   = 'localhost';           // alamat server database
$namaDb = 'db_pengaduan_siswa';  // nama database (harus sama dengan database.sql)
$user   = 'root';                // username MySQL (default XAMPP/Laragon: root)
$pass   = '';                    // password MySQL (default XAMPP: kosong)
$charset = 'utf8mb4';            // agar emoji & karakter khusus tersimpan aman

// DSN = Data Source Name, format koneksi yang dipahami PDO
$dsn = "mysql:host=$host;dbname=$namaDb;charset=$charset";

// Opsi PDO supaya lebih aman & mudah di-debug
$opsi = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // error langsung "throw", bukan diam-diam gagal
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // hasil query berbentuk array asosiatif (key => value)
    PDO::ATTR_EMULATE_PREPARES   => false,                   // pakai prepared statement asli dari MySQL (lebih aman)
];

try {
    // $koneksi inilah yang akan dipakai di semua file lain lewat: require '../../config/koneksi.php';
    $koneksi = new PDO($dsn, $user, $pass, $opsi);
} catch (PDOException $e) {
    // Kalau koneksi gagal, hentikan proses dan kirim pesan JSON yang jelas
    // (supaya kalau dipanggil dari fetch() di JS, errornya kelihatan di console/network tab)
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal: ' . $e->getMessage()
    ]);
    exit; // stop total, jangan lanjut ke kode di bawahnya
}
