<?php
/**
 * =====================================================================
 * FILE: api/siswa/pengaduan_hapus.php
 * FUNGSI: Menghapus pengaduan milik siswa sendiri. Hanya boleh selama
 *         status masih "Menunggu".
 * METHOD: POST
 * INPUT (JSON): { "kode": "PGD-00028" }
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
$kode    = trim($input['kode'] ?? '');

if ($kode === '') {
    kirimResponse(false, 'Kode pengaduan wajib dikirim.', null, 400);
}

$stmt = $koneksi->prepare("SELECT id, status, lampiran FROM pengaduan WHERE kode = ? AND siswa_id = ?");
$stmt->execute([$kode, $siswaId]);
$pengaduan = $stmt->fetch();

if (!$pengaduan) {
    kirimResponse(false, 'Pengaduan tidak ditemukan.', null, 404);
}
if ($pengaduan['status'] !== 'Menunggu') {
    kirimResponse(false, 'Pengaduan hanya bisa dihapus selama berstatus Menunggu.', null, 403);
}

// Hapus juga file lampiran fisik di server (kalau ada), supaya tidak jadi sampah
if ($pengaduan['lampiran']) {
    $pathFile = __DIR__ . '/../../uploads/' . $pengaduan['lampiran'];
    if (file_exists($pathFile)) {
        unlink($pathFile);
    }
}

$stmtHapus = $koneksi->prepare("DELETE FROM pengaduan WHERE id = ?");
$stmtHapus->execute([$pengaduan['id']]);

kirimResponse(true, 'Pengaduan berhasil dihapus.');
