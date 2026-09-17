<?php
/**
 * =====================================================================
 * FILE: api/siswa/pengaduan_get.php
 * FUNGSI: Mengambil daftar pengaduan MILIK SISWA YANG SEDANG LOGIN saja
 *         (dipakai di halaman "Riwayat Pengaduan").
 * METHOD: GET
 * PARAMETER:
 *   - status  : "Semua" atau salah satu status, default "Semua"
 *   - halaman : nomor halaman, default 1
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireSiswaLogin();

const BARIS_PER_HALAMAN = 5;

$siswaId = $_SESSION['siswa_id'];
$status  = trim($_GET['status'] ?? 'Semua');
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$offset  = ($halaman - 1) * BARIS_PER_HALAMAN;

// Filter status HANYA ditambahkan kalau bukan "Semua"
$whereSql = "WHERE p.siswa_id = ?";
$params   = [$siswaId];

if ($status !== 'Semua' && $status !== '') {
    $whereSql .= " AND p.status = ?";
    $params[] = $status;
}

$stmtTotal = $koneksi->prepare("SELECT COUNT(*) AS jml FROM pengaduan p $whereSql");
$stmtTotal->execute($params);
$totalData = (int)$stmtTotal->fetch()['jml'];

$stmt = $koneksi->prepare("
    SELECT p.kode, p.judul, k.nama AS kategori, p.status,
           DATE_FORMAT(p.created_at, '%d %M %Y') AS tanggal
    FROM pengaduan p
    JOIN kategori k ON k.id = p.kategori_id
    $whereSql
    ORDER BY p.created_at DESC
    LIMIT " . BARIS_PER_HALAMAN . " OFFSET " . $offset . "
");
$stmt->execute($params);
$daftarPengaduan = $stmt->fetchAll();

kirimResponse(true, '', [
    'items'         => $daftarPengaduan,
    'total'         => $totalData,
    'halaman'       => $halaman,
    'total_halaman' => max(1, (int)ceil($totalData / BARIS_PER_HALAMAN)),
]);
