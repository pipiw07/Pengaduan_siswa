<?php
/**
 * =====================================================================
 * FILE: includes/fungsi.php
 * FUNGSI: Kumpulan fungsi bantu (helper) yang dipakai berulang-ulang
 *         di banyak file api/*.php, supaya kode tidak duplikat.
 * =====================================================================
 */

/**
 * Mengirim response dalam format JSON yang seragam ke frontend (JS),
 * lalu menghentikan eksekusi PHP.
 *
 * Format yang dikirim selalu sama:
 * { "success": true/false, "message": "...", "data": ... }
 * Ini memudahkan JS di frontend karena bentuknya selalu konsisten.
 *
 * @param bool  $success  true jika proses berhasil, false jika gagal
 * @param string $message pesan singkat (ditampilkan lewat showToast di frontend)
 * @param mixed $data     data tambahan (array/objek), boleh dikosongkan
 * @param int   $kodeHttp kode status HTTP, default 200 (OK)
 */
function kirimResponse(bool $success, string $message = '', $data = null, int $kodeHttp = 200): void
{
    http_response_code($kodeHttp);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ]);
    exit; // penting: hentikan script supaya tidak ada output lain yang merusak JSON
}

/**
 * Membuat kode unik untuk pengaduan baru, formatnya: PGD-00001, PGD-00002, dst.
 * Caranya: ambil id pengaduan TERBESAR yang ada di tabel, lalu tambah 1.
 *
 * @param PDO $koneksi koneksi database aktif
 * @return string kode baru, contoh "PGD-00029"
 */
function buatKodePengaduan(PDO $koneksi): string
{
    // Ambil angka terbesar yang pernah dipakai di kolom "kode" (kolom bertipe PGD-xxxxx)
    $stmt = $koneksi->query("SELECT MAX(CAST(SUBSTRING(kode, 5) AS UNSIGNED)) AS angka_terbesar FROM pengaduan");
    $baris = $stmt->fetch();

    $angkaBerikutnya = ($baris && $baris['angka_terbesar']) ? ((int)$baris['angka_terbesar'] + 1) : 1;

    // str_pad menambahkan angka 0 di depan supaya selalu 5 digit, contoh: 00029
    return 'PGD-' . str_pad((string)$angkaBerikutnya, 5, '0', STR_PAD_LEFT);
}

/**
 * Mengambil input JSON yang dikirim lewat fetch() (body: JSON.stringify(...))
 * dan mengubahnya jadi array PHP biasa.
 *
 * @return array data yang dikirim dari frontend
 */
function ambilInputJson(): array
{
    $mentah = file_get_contents('php://input'); // ambil "raw body" dari request
    $data = json_decode($mentah, true);         // ubah teks JSON jadi array PHP
    return is_array($data) ? $data : [];
}
