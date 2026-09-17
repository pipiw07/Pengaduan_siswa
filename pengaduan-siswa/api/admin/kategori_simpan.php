<?php
/**
 * =====================================================================
 * FILE: api/admin/kategori_simpan.php
 * FUNGSI: Menambah kategori baru ATAU mengubah kategori yang sudah ada.
 * METHOD: POST
 * INPUT (JSON): { "id": null atau angka, "nama": "...", "deskripsi": "..." }
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
$id        = $input['id'] ?? null;
$nama      = trim($input['nama'] ?? '');
$deskripsi = trim($input['deskripsi'] ?? '');

if ($nama === '') {
    kirimResponse(false, 'Nama kategori wajib diisi.', null, 400);
}

if (empty($id)) {
    // mode tambah
    $stmt = $koneksi->prepare("INSERT INTO kategori (nama, deskripsi) VALUES (?, ?)");
    $stmt->execute([$nama, $deskripsi]);
    kirimResponse(true, 'Kategori berhasil ditambahkan.');
} else {
    // mode edit
    $stmt = $koneksi->prepare("UPDATE kategori SET nama = ?, deskripsi = ? WHERE id = ?");
    $stmt->execute([$nama, $deskripsi, $id]);
    kirimResponse(true, 'Kategori berhasil disimpan.');
}
