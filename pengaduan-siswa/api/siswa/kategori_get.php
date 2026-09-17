<?php
/**
 * =====================================================================
 * FILE: api/siswa/kategori_get.php
 * FUNGSI: Mengambil semua kategori untuk dropdown pilihan pada form
 *         "Buat Pengaduan" dan "Edit Pengaduan" di sisi siswa.
 * METHOD: GET
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireSiswaLogin();

$daftarKategori = $koneksi->query("SELECT id, nama FROM kategori ORDER BY nama ASC")->fetchAll();

kirimResponse(true, '', $daftarKategori);
