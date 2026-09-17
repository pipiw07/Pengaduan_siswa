<?php
/**
 * =====================================================================
 * FILE: api/admin/kategori_get.php
 * FUNGSI: Mengambil semua data kategori pengaduan.
 * METHOD: GET
 * PARAMETER: ?cari=kata_kunci (opsional, mencari berdasarkan nama)
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireAdminLogin();

$cari = trim($_GET['cari'] ?? '');
$kataKunci = '%' . $cari . '%';

$stmt = $koneksi->prepare("SELECT id, nama, deskripsi FROM kategori WHERE nama LIKE ? ORDER BY id ASC");
$stmt->execute([$kataKunci]);
$daftarKategori = $stmt->fetchAll();

kirimResponse(true, '', $daftarKategori);
