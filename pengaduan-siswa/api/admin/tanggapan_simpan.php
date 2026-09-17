<?php
/**
 * =====================================================================
 * FILE: api/admin/tanggapan_simpan.php
 * FUNGSI: Menyimpan tanggapan dari Admin untuk sebuah pengaduan, sekaligus
 *         mengubah status pengaduan tersebut (dipakai di form
 *         "Berikan Tanggapan").
 * METHOD: POST
 * INPUT (JSON): { "kode": "PGD-00028", "tanggapan": "...", "status": "Diproses" }
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kirimResponse(false, 'Metode request tidak diizinkan.', null, 405);
}

$input     = ambilInputJson();
$kode      = trim($input['kode'] ?? '');
$tanggapan = trim($input['tanggapan'] ?? '');
$status    = trim($input['status'] ?? '');

$statusValid = ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'];
if ($kode === '' || !in_array($status, $statusValid, true)) {
    kirimResponse(false, 'Data tidak lengkap atau status tidak valid.', null, 400);
}

// Cari id pengaduan berdasarkan kode
$stmt = $koneksi->prepare("SELECT id FROM pengaduan WHERE kode = ?");
$stmt->execute([$kode]);
$pengaduan = $stmt->fetch();

if (!$pengaduan) {
    kirimResponse(false, 'Pengaduan tidak ditemukan.', null, 404);
}

// Gunakan TRANSACTION supaya dua perintah (insert tanggapan + update status)
// berjalan sebagai satu kesatuan: kalau salah satu gagal, semuanya dibatalkan.
try {
    $koneksi->beginTransaction();

    // 1. Simpan pesan tanggapan (hanya jika admin benar-benar menulis sesuatu)
    if ($tanggapan !== '') {
        $stmtTanggapan = $koneksi->prepare(
            "INSERT INTO tanggapan (pengaduan_id, dari, pesan) VALUES (?, 'Admin', ?)"
        );
        $stmtTanggapan->execute([$pengaduan['id'], $tanggapan]);
    }

    // 2. Update status pengaduan
    $stmtStatus = $koneksi->prepare("UPDATE pengaduan SET status = ? WHERE id = ?");
    $stmtStatus->execute([$status, $pengaduan['id']]);

    $koneksi->commit(); // simpan permanen kedua perubahan di atas
    kirimResponse(true, 'Tanggapan berhasil dikirim.');
} catch (PDOException $e) {
    $koneksi->rollBack(); // batalkan semua perubahan jika terjadi error
    kirimResponse(false, 'Gagal menyimpan tanggapan: ' . $e->getMessage(), null, 500);
}
