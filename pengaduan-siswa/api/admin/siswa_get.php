<?php
/**
 * =====================================================================
 * FILE: api/admin/siswa_get.php
 * FUNGSI: Mengambil daftar akun siswa untuk tabel "Data Siswa" di admin.
 * METHOD: GET
 * PARAMETER (lewat URL, contoh: siswa_get.php?cari=andi&halaman=1):
 *   - cari    : kata kunci pencarian nama/NIS (opsional)
 *   - halaman : nomor halaman untuk paginasi (default 1)
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireAdminLogin();

const BARIS_PER_HALAMAN = 5; // samakan dengan ROWS_PER_PAGE di frontend lama

// Ambil parameter dari URL, kasih nilai default kalau tidak dikirim
$cari    = trim($_GET['cari'] ?? '');
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$offset  = ($halaman - 1) * BARIS_PER_HALAMAN;

// %...% dipakai untuk pencarian "mengandung kata kunci" (LIKE)
$kataKunci = '%' . $cari . '%';

// Hitung dulu total data yang cocok (dipakai untuk menghitung jumlah halaman di frontend)
$stmtTotal = $koneksi->prepare("
    SELECT COUNT(*) AS jml FROM siswa
    WHERE nama LIKE ? OR nis LIKE ?
");
$stmtTotal->execute([$kataKunci, $kataKunci]);
$totalData = (int)$stmtTotal->fetch()['jml'];

// Ambil data sesuai halaman yang diminta.
// Catatan: $offset sudah dipaksa jadi integer di atas (int)(...), jadi aman
// digabung langsung ke query walau tidak lewat placeholder "?".
$stmt = $koneksi->prepare("
    SELECT id, nis, nama, username, kelas FROM siswa
    WHERE nama LIKE ? OR nis LIKE ?
    ORDER BY id DESC
    LIMIT " . BARIS_PER_HALAMAN . " OFFSET " . $offset . "
");
$stmt->execute([$kataKunci, $kataKunci]);
$daftarSiswa = $stmt->fetchAll();

kirimResponse(true, '', [
    'items'        => $daftarSiswa,
    'total'        => $totalData,
    'halaman'      => $halaman,
    'total_halaman'=> max(1, (int)ceil($totalData / BARIS_PER_HALAMAN)),
]);
