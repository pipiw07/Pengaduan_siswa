<?php
/**
 * =====================================================================
 * FILE: api/admin/dashboard_stats.php
 * FUNGSI: Menyediakan data untuk halaman Dashboard admin:
 *         - jumlah total pengaduan per status
 *         - data grafik 6 bulan terakhir
 *         - 5 pengaduan paling baru
 * METHOD: GET
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireAdminLogin(); // hanya admin yang sudah login boleh mengakses data ini

// ---------------------------------------------------------------
// 1. HITUNG JUMLAH PENGADUAN PER STATUS
// ---------------------------------------------------------------
$total    = (int)$koneksi->query("SELECT COUNT(*) AS jml FROM pengaduan")->fetch()['jml'];
$menunggu = (int)$koneksi->query("SELECT COUNT(*) AS jml FROM pengaduan WHERE status = 'Menunggu'")->fetch()['jml'];
$diproses = (int)$koneksi->query("SELECT COUNT(*) AS jml FROM pengaduan WHERE status = 'Diproses'")->fetch()['jml'];
$selesai  = (int)$koneksi->query("SELECT COUNT(*) AS jml FROM pengaduan WHERE status = 'Selesai'")->fetch()['jml'];

// ---------------------------------------------------------------
// 2. DATA GRAFIK: jumlah pengaduan yang dibuat per bulan, 6 bulan terakhir
//    DATE_FORMAT dipakai untuk mengelompokkan tanggal jadi "2024-05" dst.
// ---------------------------------------------------------------
$stmtGrafik = $koneksi->query("
    SELECT DATE_FORMAT(created_at, '%b') AS label,
           DATE_FORMAT(created_at, '%Y-%m') AS urutan,
           COUNT(*) AS jumlah
    FROM pengaduan
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY urutan, label
    ORDER BY urutan ASC
");
$grafik = $stmtGrafik->fetchAll();

// ---------------------------------------------------------------
// 3. 5 PENGADUAN PALING BARU (join ke tabel siswa untuk ambil nama pelapor)
// ---------------------------------------------------------------
$stmtRecent = $koneksi->query("
    SELECT p.kode, p.judul, p.status, DATE_FORMAT(p.created_at, '%d %M %Y') AS tanggal
    FROM pengaduan p
    ORDER BY p.created_at DESC
    LIMIT 5
");
$recent = $stmtRecent->fetchAll();

kirimResponse(true, '', [
    'total'    => $total,
    'menunggu' => $menunggu,
    'diproses' => $diproses,
    'selesai'  => $selesai,
    'grafik'   => $grafik,
    'recent'   => $recent,
]);
