<?php
/**
 * =====================================================================
 * FILE: api/admin/pengaduan_detail.php
 * FUNGSI: Mengambil detail satu pengaduan (dipakai di halaman
 *         "Detail Pengaduan" dan "Berikan Tanggapan" admin).
 * METHOD: GET
 * PARAMETER: ?kode=PGD-00028
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireAdminLogin();

$kode = trim($_GET['kode'] ?? '');
if ($kode === '') {
    kirimResponse(false, 'Kode pengaduan wajib dikirim.', null, 400);
}

$stmt = $koneksi->prepare("
    SELECT p.id, p.kode, s.nama AS pelapor, k.nama AS kategori,
           p.judul, p.deskripsi, p.lampiran, p.status,
           DATE_FORMAT(p.created_at, '%d %M %Y') AS tanggal
    FROM pengaduan p
    JOIN siswa s ON s.id = p.siswa_id
    JOIN kategori k ON k.id = p.kategori_id
    WHERE p.kode = ?
");
$stmt->execute([$kode]);
$pengaduan = $stmt->fetch();

if (!$pengaduan) {
    kirimResponse(false, 'Pengaduan tidak ditemukan.', null, 404);
}

// Ambil juga riwayat tanggapan/percakapan untuk pengaduan ini
$stmtTanggapan = $koneksi->prepare("
    SELECT dari, pesan, DATE_FORMAT(waktu, '%d %M %Y, %H.%i') AS waktu
    FROM tanggapan
    WHERE pengaduan_id = ?
    ORDER BY waktu ASC
");
$stmtTanggapan->execute([$pengaduan['id']]);
$pengaduan['percakapan'] = $stmtTanggapan->fetchAll();

kirimResponse(true, '', $pengaduan);
