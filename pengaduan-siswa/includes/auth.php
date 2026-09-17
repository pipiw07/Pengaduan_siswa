<?php
/**
 * =====================================================================
 * FILE: includes/auth.php
 * FUNGSI: Kumpulan fungsi untuk mengatur SESSION login, baik untuk
 *         Admin maupun Siswa. Session dipakai supaya setelah login,
 *         server "ingat" siapa yang sedang mengakses tanpa perlu
 *         mengirim ulang username/password di setiap request.
 *
 * CARA PAKAI:
 *   require_once __DIR__ . '/../../includes/auth.php';
 *   requireAdminLogin();   // taruh di paling atas API khusus admin
 *   requireSiswaLogin();   // taruh di paling atas API khusus siswa
 * =====================================================================
 */

// session_start() WAJIB dipanggil sebelum ada output apapun ke browser.
// Kita cek dulu status session supaya tidak error jika file ini di-include 2x.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Menghentikan request dan mengirim JSON error jika ADMIN belum login.
 * Dipanggil di baris paling atas setiap file api/admin/*.php (kecuali login.php).
 */
function requireAdminLogin(): void
{
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401); // 401 = Unauthorized (belum login)
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Sesi admin habis atau belum login. Silakan login kembali.'
        ]);
        exit;
    }
}

/**
 * Menghentikan request dan mengirim JSON error jika SISWA belum login.
 * Dipanggil di baris paling atas setiap file api/siswa/*.php (kecuali login.php).
 */
function requireSiswaLogin(): void
{
    if (!isset($_SESSION['siswa_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Sesi siswa habis atau belum login. Silakan login kembali.'
        ]);
        exit;
    }
}
