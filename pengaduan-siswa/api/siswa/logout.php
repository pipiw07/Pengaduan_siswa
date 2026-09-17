<?php
/**
 * =====================================================================
 * FILE: api/siswa/logout.php
 * FUNGSI: Menghapus session siswa (proses logout).
 * =====================================================================
 */

require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

unset($_SESSION['siswa_id'], $_SESSION['siswa_nis'], $_SESSION['siswa_nama'], $_SESSION['siswa_kelas']);
session_destroy();

kirimResponse(true, 'Logout berhasil.');
