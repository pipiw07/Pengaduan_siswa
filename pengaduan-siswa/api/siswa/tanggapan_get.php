<?php
/**
 * =====================================================================
 * FILE: api/siswa/tanggapan_get.php
 * FUNGSI: Mengambil seluruh percakapan/tanggapan untuk satu pengaduan
 *         milik siswa yang login (dipakai di halaman "Tanggapan Pengaduan").
 * METHOD: GET
 * PARAMETER: ?kode=PGD-00027
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireSiswaLogin();

$siswaId = $_SESSION['siswa_id'];
$kode    = trim($_GET['kode'] ?? '');

// Pastikan pengaduan ini benar milik siswa yang sedang login
$stmt = $koneksi->prepare("SELECT id, kode, judul, status FROM pengaduan WHERE kode = ? AND siswa_id = ?");
$stmt->execute([$kode, $siswaId]);
$pengaduan = $stmt->fetch();

if (!$pengaduan) {
    kirimResponse(false, 'Pengaduan tidak ditemukan.', null, 404);
}

$stmtThread = $koneksi->prepare("
    SELECT dari, pesan, DATE_FORMAT(waktu, '%d %M %Y, %H.%i') AS waktu
    FROM tanggapan WHERE pengaduan_id = ? ORDER BY waktu ASC
");
$stmtThread->execute([$pengaduan['id']]);
$pengaduan['percakapan'] = $stmtThread->fetchAll();

kirimResponse(true, '', $pengaduan);
