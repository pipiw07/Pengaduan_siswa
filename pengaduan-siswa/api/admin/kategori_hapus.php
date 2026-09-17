<?php
/**
 * =====================================================================
 * FILE: api/admin/kategori_hapus.php
 * FUNGSI: Menghapus satu kategori berdasarkan id.
 * METHOD: POST
 * INPUT (JSON): { "id": angka }
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
    kirimResponse(false, 'ID kategori tidak ditemukan.', null, 400);
}

try {
    $stmt = $koneksi->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->execute([$id]);
    kirimResponse(true, 'Kategori berhasil dihapus.');
} catch (PDOException $e) {
    // Error 23000 muncul jika kategori ini masih dipakai oleh data pengaduan
    // (karena ada FOREIGN KEY kategori_id di tabel pengaduan)
    if ($e->getCode() == 23000) {
        kirimResponse(false, 'Kategori tidak bisa dihapus karena masih dipakai oleh data pengaduan.', null, 409);
    }
    kirimResponse(false, 'Gagal menghapus kategori: ' . $e->getMessage(), null, 500);
}
