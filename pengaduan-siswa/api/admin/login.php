<?php
/**
 * =====================================================================
 * FILE: api/admin/login.php
 * FUNGSI: Endpoint untuk proses login Admin.
 * DIPANGGIL DARI: fetch('api/admin/login.php', { method:'POST', body: JSON })
 * INPUT (JSON)  : { "username": "...", "password": "..." }
 * OUTPUT (JSON) : { success, message, data: { nama, username } }
 * =====================================================================
 */

require __DIR__ . '/../../config/koneksi.php'; // koneksi database ($koneksi)
require __DIR__ . '/../../includes/auth.php';  // session_start() ada di sini
require __DIR__ . '/../../includes/fungsi.php';// kirimResponse(), ambilInputJson()

// Endpoint login hanya boleh diakses dengan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    kirimResponse(false, 'Metode request tidak diizinkan.', null, 405);
}

// Ambil data yang dikirim dari form login (dalam format JSON)
$input = ambilInputJson();
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

if ($username === '' || $password === '') {
    kirimResponse(false, 'Username dan password wajib diisi.', null, 400);
}

// Cari admin dengan username tersebut
$stmt = $koneksi->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

// password_verify() membandingkan password polos dengan hash di database.
// INI PENTING: jangan pernah bandingkan password dengan == atau ===,
// karena password di database selalu dalam bentuk hash.
if (!$admin || !password_verify($password, $admin['password'])) {
    kirimResponse(false, 'Username atau password salah.', null, 401);
}

// Login berhasil -> simpan data penting ke SESSION
// Session inilah yang dicek oleh requireAdminLogin() di file API lainnya.
$_SESSION['admin_id']       = $admin['id'];
$_SESSION['admin_username'] = $admin['username'];
$_SESSION['admin_nama']     = $admin['nama'];

kirimResponse(true, 'Login berhasil.', [
    'nama'     => $admin['nama'],
    'username' => $admin['username'],
]);
