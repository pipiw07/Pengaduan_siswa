<?php
/**
 * =====================================================================
 * FILE: api/siswa/pengaduan_detail.php
 * FUNGSI: Mengambil detail satu pengaduan MILIK SISWA YANG LOGIN.
 * METHOD: GET
 * PARAMETER: ?kode=PGD-00028
 *
 * PENTING (KEAMANAN): query di bawah selalu menambahkan
 * "AND siswa_id = $_SESSION['siswa_id']" supaya siswa A TIDAK BISA
 * membuka detail pengaduan milik siswa B hanya dengan menebak kode.
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireSiswaLogin();

$kode    = trim($_GET['kode'] ?? '');
$siswaId = $_SESSION['siswa_id'];

if ($kode === '') {
    kirimResponse(false, 'Kode pengaduan wajib dikirim.', null, 400);
}

$stmt = $koneksi->prepare("
    SELECT p.id, p.kode, p.kategori_id, k.nama AS kategori, p.judul, p.deskripsi,
           p.lampiran, p.status, DATE_FORMAT(p.created_at, '%d %M %Y') AS tanggal
    FROM pengaduan p
    JOIN kategori k ON k.id = p.kategori_id
    WHERE p.kode = ? AND p.siswa_id = ?
");
$stmt->execute([$kode, $siswaId]);
$pengaduan = $stmt->fetch();

if (!$pengaduan) {
    // Pesan sengaja dibuat umum (tidak bilang "punya siswa lain") demi keamanan
    kirimResponse(false, 'Pengaduan tidak ditemukan.', null, 404);
}

$stmtTanggapan = $koneksi->prepare("
    SELECT dari, pesan, DATE_FORMAT(waktu, '%d %M %Y, %H.%i') AS waktu
    FROM tanggapan WHERE pengaduan_id = ? ORDER BY waktu ASC
");
$stmtTanggapan->execute([$pengaduan['id']]);
$pengaduan['percakapan'] = $stmtTanggapan->fetchAll();

kirimResponse(true, '', $pengaduan);
