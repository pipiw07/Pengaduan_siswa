<?php
/**
 * =====================================================================
 * FILE: api/siswa/login.php
 * FUNGSI: Endpoint login untuk Panel Siswa.
 * METHOD: POST
 * INPUT (JSON): { "nis": "...", "password": "..." }
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kirimResponse(false, 'Metode request tidak diizinkan.', null, 405);
}

$input = ambilInputJson();
$nis      = trim($input['nis'] ?? '');
$password = trim($input['password'] ?? '');

if ($nis === '' || $password === '') {
    kirimResponse(false, 'NIS dan password wajib diisi.', null, 400);
}

$stmt = $koneksi->prepare("SELECT * FROM siswa WHERE nis = ?");
$stmt->execute([$nis]);
$siswa = $stmt->fetch();

if (!$siswa || !password_verify($password, $siswa['password'])) {
    kirimResponse(false, 'NIS atau password salah.', null, 401);
}

// Simpan data siswa yang login ke SESSION
$_SESSION['siswa_id']    = $siswa['id'];
$_SESSION['siswa_nis']   = $siswa['nis'];
$_SESSION['siswa_nama']  = $siswa['nama'];
$_SESSION['siswa_kelas'] = $siswa['kelas'];

kirimResponse(true, 'Login berhasil.', [
    'nama'  => $siswa['nama'],
    'nis'   => $siswa['nis'],
    'kelas' => $siswa['kelas'],
]);
