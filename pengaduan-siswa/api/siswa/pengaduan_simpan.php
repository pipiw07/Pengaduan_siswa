<?php
/**
 * =====================================================================
 * FILE: api/siswa/pengaduan_simpan.php
 * FUNGSI: Menyimpan pengaduan BARU dari siswa yang sedang login,
 *         termasuk upload file lampiran (opsional).
 * METHOD: POST
 * INPUT: dikirim sebagai FormData (BUKAN JSON) karena ada file:
 *   - kategori_id  (angka, id dari tabel kategori)
 *   - judul        (teks)
 *   - deskripsi    (teks)
 *   - lampiran     (file, opsional)
 *
 * KENAPA FormData, bukan JSON?
 * File tidak bisa dikirim lewat JSON.stringify(), jadi frontend memakai
 * `new FormData(form)` lalu fetch(url, { method:'POST', body: formData })
 * TANPA header Content-Type manual (browser yang mengatur otomatis).
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireSiswaLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kirimResponse(false, 'Metode request tidak diizinkan.', null, 405);
}

$siswaId    = $_SESSION['siswa_id'];
$kategoriId = trim($_POST['kategori_id'] ?? '');
$judul      = trim($_POST['judul'] ?? '');
$deskripsi  = trim($_POST['deskripsi'] ?? '');

if ($kategoriId === '' || $judul === '' || $deskripsi === '') {
    kirimResponse(false, 'Kategori, judul, dan deskripsi wajib diisi.', null, 400);
}

// ---------------------------------------------------------------
// PROSES UPLOAD FILE LAMPIRAN (jika siswa memilih file)
// ---------------------------------------------------------------
$namaFileTersimpan = null;

if (isset($_FILES['lampiran']) && $_FILES['lampiran']['error'] === UPLOAD_ERR_OK) {
    $fileAsli   = $_FILES['lampiran'];
    $ekstensi   = strtolower(pathinfo($fileAsli['name'], PATHINFO_EXTENSION));
    $ekstensiOk = ['jpg', 'jpeg', 'png', 'pdf']; // sesuai hint di form: "Format: JPG, PNG, PDF"
    $maksUkuran = 5 * 1024 * 1024; // maksimal 5MB, sesuai hint di form

    if (!in_array($ekstensi, $ekstensiOk, true)) {
        kirimResponse(false, 'Format file tidak didukung. Gunakan JPG, PNG, atau PDF.', null, 400);
    }
    if ($fileAsli['size'] > $maksUkuran) {
        kirimResponse(false, 'Ukuran file maksimal 5MB.', null, 400);
    }

    // Buat nama file unik supaya tidak bentrok antar siswa, contoh:
    // 1717000000_foto_toilet.jpg
    $namaFileTersimpan = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $fileAsli['name']);
    $folderTujuan = __DIR__ . '/../../uploads/';

    if (!move_uploaded_file($fileAsli['tmp_name'], $folderTujuan . $namaFileTersimpan)) {
        kirimResponse(false, 'Gagal menyimpan file lampiran ke server.', null, 500);
    }
}

// ---------------------------------------------------------------
// SIMPAN DATA PENGADUAN KE DATABASE
// ---------------------------------------------------------------
$kodeBaru = buatKodePengaduan($koneksi); // fungsi dari includes/fungsi.php

$stmt = $koneksi->prepare("
    INSERT INTO pengaduan (kode, siswa_id, kategori_id, judul, deskripsi, lampiran, status)
    VALUES (?, ?, ?, ?, ?, ?, 'Menunggu')
");
$stmt->execute([$kodeBaru, $siswaId, $kategoriId, $judul, $deskripsi, $namaFileTersimpan]);

kirimResponse(true, 'Pengaduan berhasil dikirim.', ['kode' => $kodeBaru]);
