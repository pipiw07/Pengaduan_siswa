<?php
/**
 * =====================================================================
 * FILE: api/admin/siswa_simpan.php
 * FUNGSI: Menyimpan data siswa BARU, atau meng-update siswa yang sudah ada.
 * METHOD: POST
 * INPUT (JSON):
 *   { "id": null atau angka, "nis", "nama", "username", "password", "kelas" }
 *   - Jika "id" kosong/null  -> mode TAMBAH data baru
 *   - Jika "id" ada isinya   -> mode EDIT data yang sudah ada
 *   - "password" boleh dikosongkan saat EDIT (artinya password tidak diubah)
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

$id       = $input['id'] ?? null;
$nis      = trim($input['nis'] ?? '');
$nama     = trim($input['nama'] ?? '');
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');
$kelas    = trim($input['kelas'] ?? '');

// ---------------------------------------------------------------
// VALIDASI DASAR
// ---------------------------------------------------------------
if ($nis === '' || $nama === '' || $username === '' || $kelas === '') {
    kirimResponse(false, 'NIS, Nama, Username, dan Kelas wajib diisi.', null, 400);
}

if (empty($id) && $password === '') {
    // password wajib diisi HANYA saat menambah akun baru
    kirimResponse(false, 'Password wajib diisi untuk akun siswa baru.', null, 400);
}

try {
    if (empty($id)) {
        // ------------------- MODE TAMBAH DATA BARU -------------------
        $stmt = $koneksi->prepare(
            "INSERT INTO siswa (nis, nama, username, password, kelas) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nis, $nama, $username, password_hash($password, PASSWORD_DEFAULT), $kelas]);
        kirimResponse(true, 'Akun siswa berhasil ditambahkan.');
    } else {
        // ------------------- MODE EDIT DATA -------------------
        if ($password !== '') {
            // admin mengisi password baru -> ikut diupdate
            $stmt = $koneksi->prepare(
                "UPDATE siswa SET nis=?, nama=?, username=?, password=?, kelas=? WHERE id=?"
            );
            $stmt->execute([$nis, $nama, $username, password_hash($password, PASSWORD_DEFAULT), $kelas, $id]);
        } else {
            // password dikosongkan -> jangan ubah password lama
            $stmt = $koneksi->prepare(
                "UPDATE siswa SET nis=?, nama=?, username=?, kelas=? WHERE id=?"
            );
            $stmt->execute([$nis, $nama, $username, $kelas, $id]);
        }
        kirimResponse(true, 'Data siswa berhasil diperbarui.');
    }
} catch (PDOException $e) {
    // Kode error 23000 = duplikat data (NIS/username sudah dipakai siswa lain)
    if ($e->getCode() == 23000) {
        kirimResponse(false, 'NIS atau Username sudah dipakai oleh siswa lain.', null, 409);
    }
    kirimResponse(false, 'Gagal menyimpan data: ' . $e->getMessage(), null, 500);
}
