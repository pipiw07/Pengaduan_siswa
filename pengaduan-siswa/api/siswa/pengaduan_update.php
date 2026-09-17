<?php
/**
 * =====================================================================
 * FILE: api/siswa/pengaduan_update.php
 * FUNGSI: Mengubah pengaduan milik siswa sendiri. Hanya boleh dilakukan
 *         SELAMA status pengaduan masih "Menunggu" (sama seperti aturan
 *         di frontend asli).
 * METHOD: POST
 * INPUT (JSON): { "kode", "kategori_id", "judul", "deskripsi" }
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireSiswaLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kirimResponse(false, 'Metode request tidak diizinkan.', null, 405);
}

$siswaId = $_SESSION['siswa_id'];
$input   = ambilInputJson();

$kode       = trim($input['kode'] ?? '');
$kategoriId = trim($input['kategori_id'] ?? '');
$judul      = trim($input['judul'] ?? '');
$deskripsi  = trim($input['deskripsi'] ?? '');

if ($kode === '' || $kategoriId === '' || $judul === '' || $deskripsi === '') {
    kirimResponse(false, 'Semua kolom wajib diisi.', null, 400);
}

// Pastikan pengaduan ini benar-benar milik siswa yang login DAN masih "Menunggu"
$stmt = $koneksi->prepare("SELECT id, status FROM pengaduan WHERE kode = ? AND siswa_id = ?");
$stmt->execute([$kode, $siswaId]);
$pengaduan = $stmt->fetch();

if (!$pengaduan) {
    kirimResponse(false, 'Pengaduan tidak ditemukan.', null, 404);
}
if ($pengaduan['status'] !== 'Menunggu') {
    kirimResponse(false, 'Pengaduan hanya bisa diedit selama berstatus Menunggu.', null, 403);
}

$stmtUpdate = $koneksi->prepare("
    UPDATE pengaduan SET kategori_id = ?, judul = ?, deskripsi = ? WHERE id = ?
");
$stmtUpdate->execute([$kategoriId, $judul, $deskripsi, $pengaduan['id']]);

kirimResponse(true, 'Perubahan berhasil disimpan.');
