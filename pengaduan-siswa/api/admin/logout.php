<?php
/**
 * =====================================================================
 * FILE: api/admin/logout.php
 * FUNGSI: Menghapus session admin (proses logout).
 * =====================================================================
 */

require __DIR__ . '/../../includes/auth.php';
require __DIR__ . '/../../includes/fungsi.php';

// Hapus semua data session yang berkaitan dengan admin
unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_nama']);

// session_destroy() akan menghapus seluruh session di server untuk user ini
session_destroy();

kirimResponse(true, 'Logout berhasil.');
