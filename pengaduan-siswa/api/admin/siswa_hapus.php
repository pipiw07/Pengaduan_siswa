<?php
/**
 * =====================================================================
 * FILE: api/admin/siswa_hapus.php
 * FUNGSI: Menghapus satu akun siswa berdasarkan id.
 * METHOD: POST
 * INPUT (JSON): { "id": angka }
 *
 * CATATAN: Karena tabel pengaduan punya
 *   FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
 * maka jika siswa dihapus, SEMUA pengaduan milik siswa itu juga
 * ikut terhapus otomatis oleh database. Ini sesuai perilaku demo asli.
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kirimResponse(false, 'Metode request tidak diizinkan.', null, 405);
}

$input = ambilInputJson();
$id = $input['id'] ?? null;

if (empty($id)) {
    kirimResponse(false, 'ID siswa tidak ditemukan.', null, 400);
}

$stmt = $koneksi->prepare("DELETE FROM siswa WHERE id = ?");
$stmt->execute([$id]);

kirimResponse(true, 'Akun siswa berhasil dihapus.');
