<?php
/**
 * =====================================================================
 * FILE: api/siswa/profil_get.php
 * FUNGSI: Mengambil data profil siswa yang sedang login, dipakai di
 *         halaman "Profil Saya".
 * METHOD: GET
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php';
require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

requireSiswaLogin();

$stmt = $koneksi->prepare("SELECT nis, nama, kelas, email, no_hp FROM siswa WHERE id = ?");
$stmt->execute([$_SESSION['siswa_id']]);
$profil = $stmt->fetch();

kirimResponse(true, '', $profil);
