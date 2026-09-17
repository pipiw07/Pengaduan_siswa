<?php
/**
 * =====================================================================
 * FILE: api/siswa/dashboard_stats.php
 * FUNGSI: Data untuk halaman Dashboard siswa: jumlah pengaduan MILIK
 *         SISWA YANG SEDANG LOGIN per status, + 5 pengaduan terbarunya.
 * METHOD: GET
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireSiswaLogin();

// $_SESSION['siswa_id'] otomatis tersedia karena requireSiswaLogin()
// sudah memastikan siswa ini sudah login.
$siswaId = $_SESSION['siswa_id'];

$stmt = $koneksi->prepare("
    SELECT
      SUM(status = 'Menunggu') AS menunggu,
      SUM(status = 'Diproses') AS diproses,
      SUM(status = 'Selesai')  AS selesai,
      SUM(status = 'Ditolak')  AS ditolak
    FROM pengaduan WHERE siswa_id = ?
");
$stmt->execute([$siswaId]);
$statistik = $stmt->fetch();

// SUM() pada MySQL menghasilkan NULL jika tidak ada baris sama sekali,
// maka kita pastikan hasilnya tetap angka 0 (bukan null) di JSON.
foreach ($statistik as $key => $val) {
    $statistik[$key] = (int)$val;
}

$stmtRecent = $koneksi->prepare("
    SELECT kode, judul, status, DATE_FORMAT(created_at, '%d %M %Y') AS tanggal
    FROM pengaduan
    WHERE siswa_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$stmtRecent->execute([$siswaId]);
$statistik['recent'] = $stmtRecent->fetchAll();

kirimResponse(true, '', $statistik);
