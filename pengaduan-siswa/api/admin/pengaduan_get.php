<?php
/**
 * =====================================================================
 * FILE: api/admin/pengaduan_get.php
 * FUNGSI: Mengambil daftar SEMUA pengaduan (dari semua siswa) untuk
 *         tabel "Daftar Pengaduan" di panel admin.
 * METHOD: GET
 * PARAMETER (lewat URL):
 *   - cari     : cari berdasarkan judul/pelapor/kode (opsional)
 *   - status   : filter status (Menunggu/Diproses/Selesai/Ditolak), opsional
 *   - kategori : filter nama kategori, opsional
 *   - halaman  : nomor halaman, default 1
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireAdminLogin();

const BARIS_PER_HALAMAN = 5;

$cari      = trim($_GET['cari'] ?? '');
$status    = trim($_GET['status'] ?? '');
$kategori  = trim($_GET['kategori'] ?? '');
$halaman   = max(1, (int)($_GET['halaman'] ?? 1));
$offset    = ($halaman - 1) * BARIS_PER_HALAMAN;
$kataKunci = '%' . $cari . '%';

// Query dasar: gabungkan tabel pengaduan + siswa (untuk nama pelapor) + kategori
// WHERE 1=1 adalah trik supaya mudah menambahkan kondisi AND di bawahnya
$whereSql = "WHERE (p.judul LIKE ? OR s.nama LIKE ? OR p.kode LIKE ?)";
$params   = [$kataKunci, $kataKunci, $kataKunci];

if ($status !== '') {
    $whereSql .= " AND p.status = ?";
    $params[] = $status;
}
if ($kategori !== '') {
    $whereSql .= " AND k.nama = ?";
    $params[] = $kategori;
}

// Hitung total data yang cocok dengan filter (untuk paginasi)
$stmtTotal = $koneksi->prepare("
    SELECT COUNT(*) AS jml
    FROM pengaduan p
    JOIN siswa s ON s.id = p.siswa_id
    JOIN kategori k ON k.id = p.kategori_id
    $whereSql
");
$stmtTotal->execute($params);
$totalData = (int)$stmtTotal->fetch()['jml'];

// Ambil data pengaduan sesuai halaman
$stmt = $koneksi->prepare("
    SELECT p.id, p.kode, s.nama AS pelapor, k.nama AS kategori,
           DATE_FORMAT(p.created_at, '%d %M %Y') AS tanggal, p.status
    FROM pengaduan p
    JOIN siswa s ON s.id = p.siswa_id
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
