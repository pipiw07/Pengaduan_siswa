<?php
/**
 * =====================================================================
 * FILE: api/siswa/tanggapan_kirim.php
 * FUNGSI: Menyimpan balasan yang ditulis SISWA di halaman "Tanggapan
 *         Pengaduan" (bukan tanggapan dari admin).
 * METHOD: POST
 * INPUT (JSON): { "kode": "PGD-00027", "pesan": "..." }
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
$pesan   = trim($input['pesan'] ?? '');

if ($pesan === '') {
    kirimResponse(false, 'Tulis balasan terlebih dahulu.', null, 400);
}

$stmt = $koneksi->prepare("SELECT id FROM pengaduan WHERE kode = ? AND siswa_id = ?");
$stmt->execute([$kode, $siswaId]);
$pengaduan = $stmt->fetch();

if (!$pengaduan) {
    kirimResponse(false, 'Pengaduan tidak ditemukan.', null, 404);
}

$stmtSimpan = $koneksi->prepare("INSERT INTO tanggapan (pengaduan_id, dari, pesan) VALUES (?, 'Siswa', ?)");
$stmtSimpan->execute([$pengaduan['id'], $pesan]);

kirimResponse(true, 'Balasan terkirim.');
