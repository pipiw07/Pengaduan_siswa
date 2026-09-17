<?php
/**
 * =====================================================================
 * FILE: siswa.php
 * FUNGSI: Halaman utama Panel Siswa. Tampilan (HTML/CSS) SAMA PERSIS
 *         dengan siswa.html aslinya, tapi bagian <script> di bawah
 *         sudah diganti total: sekarang mengambil & menyimpan data
 *         lewat fetch() ke file-file di folder api/siswa/*.php yang
 *         terhubung ke database MySQL (bukan lagi array JS/mock data).
 * =====================================================================
 */
require_once __DIR__ . '/includes/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Panel Siswa - Pengaduan Siswa</title>
<style>
  /* =====================================================
     1. VARIABEL WARNA & PENGATURAN DASAR
     ===================================================== */
  :root{
    --navy: #16213E;
    --navy-light: #1E2C52;
    --primary: #2F6FED;
    --primary-dark: #1E54C7;
    --bg: #F3F5F9;
    --white: #FFFFFF;
    --text: #1F2937;
    --muted: #6B7280;
    --border: #E5E7EB;
    --warning-bg:#FEF3C7; --warning-text:#92400E; /* badge Menunggu */
    --info-bg:#DBEAFE;    --info-text:#1E40AF;    /* badge Diproses */
    --success-bg:#D1FAE5; --success-text:#065F46; /* badge Selesai */
    --danger-bg:#FEE2E2;  --danger-text:#991B1B;  /* badge Ditolak */
    --radius: 10px;
    --shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
  }
  *{ box-sizing:border-box; margin:0; padding:0; }
  body{ font-family:'Segoe UI', Roboto, Arial, sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }
  button{ font-family:inherit; cursor:pointer; border:none; }
  input, select, textarea{ font-family:inherit; }
  a{ text-decoration:none; color:inherit; }
  ul{ list-style:none; }
  .hidden{ display:none !important; }

  /* =====================================================
     2. HALAMAN LOGIN SISWA
     ===================================================== */
  #login-page{ min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
  .login-card{ display:flex; max-width:900px; width:100%; background:#fff; border-radius:16px; box-shadow:var(--shadow); overflow:hidden; }
  .login-form-side{ flex:1; padding:48px; display:flex; flex-direction:column; justify-content:center; }
  .login-illustration-side{ flex:1; background:#EEF2FF; display:flex; align-items:center; justify-content:center; padding:32px; }
  .login-illustration-side svg{ width:100%; max-width:280px; }
  .login-logo{ width:56px; height:56px; background:var(--primary); border-radius:14px; display:flex; align-items:center; justify-content:center; margin-bottom:16px; color:#fff; font-size:26px; }
  .login-title{ font-size:22px; font-weight:700; margin-bottom:4px; }
  .login-subtitle{ color:var(--muted); font-size:14px; margin-bottom:28px; }
  .form-group{ margin-bottom:18px; }
  .form-group label{ display:block; font-size:13px; font-weight:600; margin-bottom:6px; }
  .input-wrap{ position:relative; }
  .form-group input, .form-group select, .form-group textarea{ width:100%; padding:11px 14px; border:1px solid var(--border); border-radius:8px; font-size:14px; outline:none; transition:border-color .15s; }
  .form-group input:focus, .form-group select:focus, .form-group textarea:focus{ border-color:var(--primary); }
  .toggle-password{ position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; color:var(--muted); font-size:13px; }
  .remember-row{ display:flex; align-items:center; gap:8px; margin-bottom:20px; font-size:13px; color:var(--muted); }
  .btn-primary{ width:100%; background:var(--primary); color:#fff; padding:12px; border-radius:8px; font-weight:600; font-size:14px; transition:background .15s; }
  .btn-primary:hover{ background:var(--primary-dark); }
  .login-link{ text-align:center; margin-top:14px; font-size:13px; color:var(--primary); }
  .login-footer{ text-align:center; color:var(--muted); font-size:12px; margin-top:24px; }
  .login-error{ background:var(--danger-bg); color:var(--danger-text); padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:16px; display:none; }

  /* =====================================================
     3. LAYOUT UTAMA
     ===================================================== */
  #app{ display:flex; min-height:100vh; }
  .sidebar{ width:250px; background:var(--navy); color:#CBD5E1; display:flex; flex-direction:column; flex-shrink:0; position:sticky; top:0; height:100vh; overflow-y:auto; }
  .sidebar-brand{ display:flex; align-items:center; gap:10px; padding:20px 20px; font-weight:700; color:#fff; font-size:15px; border-bottom:1px solid rgba(255,255,255,.08); }
  .sidebar-brand .icon-box{ width:34px; height:34px; background:var(--primary); border-radius:8px; display:flex; align-items:center; justify-content:center; }
  .sidebar-menu{ margin-top:10px; }
  .sidebar-menu li a{ display:flex; align-items:center; gap:10px; padding:11px 20px; font-size:14px; color:#CBD5E1; border-left:3px solid transparent; transition:background .15s; }
  .sidebar-menu li a:hover{ background:var(--navy-light); }
  .sidebar-menu li a.active{ background:var(--primary); color:#fff; border-left-color:#fff; }
  .sidebar-footer{ margin-top:auto; padding:16px 20px; border-top:1px solid rgba(255,255,255,.08); }
  .btn-logout{ display:flex; align-items:center; gap:8px; color:#FCA5A5; background:none; font-size:14px; width:100%; padding:8px 0; }

  .main-area{ flex:1; display:flex; flex-direction:column; min-width:0; }
  .topbar{ background:#fff; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; padding:14px 28px; }
  .topbar-left{ display:flex; align-items:center; gap:16px; }
  .hamburger{ background:none; font-size:20px; color:var(--muted); }
  .topbar-right{ display:flex; align-items:center; gap:18px; }
  .bell-btn{ background:none; font-size:18px; color:var(--muted); }
  .avatar-mini{ width:38px; height:38px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600; }
  .topbar-user{ display:flex; align-items:center; gap:10px; }
  .topbar-user .name{ font-size:13px; font-weight:600; }
  .topbar-user .role{ font-size:11px; color:var(--muted); }

  .content{ padding:28px; flex:1; }
  .breadcrumb{ font-size:12px; color:var(--muted); margin-bottom:4px; }
  .page-title{ font-size:22px; font-weight:700; margin-bottom:20px; }

  /* =====================================================
     4. KOMPONEN UMUM
     ===================================================== */
  .card{ background:#fff; border-radius:var(--radius); box-shadow:var(--shadow); padding:20px; border:1px solid var(--border); }
  .stat-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px; }
  .stat-card{ border-radius:var(--radius); padding:18px; box-shadow:var(--shadow); }
  .stat-card .stat-value{ font-size:26px; font-weight:700; margin:4px 0 2px; }
  .stat-card .stat-label{ font-size:13px; font-weight:600; }
  .stat-card .stat-sub{ font-size:12px; opacity:.75; }
  .stat-yellow{ background:var(--warning-bg); color:var(--warning-text); }
  .stat-blue{ background:var(--info-bg); color:var(--info-text); }
  .stat-green{ background:var(--success-bg); color:var(--success-text); }
  .stat-red{ background:var(--danger-bg); color:var(--danger-text); }
  .dash-grid{ display:grid; grid-template-columns:1.4fr 1fr; gap:16px; }

  table{ width:100%; border-collapse:collapse; font-size:13.5px; }
  thead th{ text-align:left; padding:10px 12px; color:var(--muted); font-weight:600; border-bottom:1px solid var(--border); font-size:12.5px; }
  tbody td{ padding:12px; border-bottom:1px solid var(--border); }
  tbody tr:hover{ background:#FAFBFF; }
  .table-icon-btn{ background:none; color:var(--muted); font-size:15px; padding:4px 6px; }
  .table-icon-btn:hover{ color:var(--primary); }

  .badge{ display:inline-block; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; }
  .badge-menunggu{ background:var(--warning-bg); color:var(--warning-text); }
  .badge-diproses{ background:var(--info-bg); color:var(--info-text); }
  .badge-selesai{ background:var(--success-bg); color:var(--success-text); }
  .badge-ditolak{ background:var(--danger-bg); color:var(--danger-text); }

  .btn-add{ background:var(--primary); color:#fff; padding:10px 16px; border-radius:8px; font-size:13.5px; font-weight:600; }
  .btn-add:hover{ background:var(--primary-dark); }
  .btn-outline{ background:#fff; border:1px solid var(--border); color:var(--text); padding:10px 16px; border-radius:8px; font-size:13.5px; font-weight:600; }
  .btn-outline:hover{ background:#F3F4F6; }
  .btn-danger{ background:#DC2626; color:#fff; padding:10px 16px; border-radius:8px; font-weight:600; font-size:13.5px; }
  .btn-danger:hover{ background:#B91C1C; }

  /* tab filter di halaman Riwayat Pengaduan */
  .tabs{ display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
  .tab-btn{ background:#fff; border:1px solid var(--border); padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; color:var(--muted); }
  .tab-btn.active{ background:var(--primary); color:#fff; border-color:var(--primary); }

  .pagination{ display:flex; align-items:center; justify-content:flex-end; gap:6px; margin-top:14px; }
  .pagination button{ width:30px; height:30px; border-radius:6px; border:1px solid var(--border); background:#fff; font-size:13px; }
  .pagination button.active{ background:var(--primary); color:#fff; border-color:var(--primary); }
  .pagination-info{ font-size:12.5px; color:var(--muted); margin-right:auto; }

  .file-input-btn{ display:inline-block; padding:9px 14px; border:1px solid var(--border); border-radius:8px; font-size:13px; font-weight:600; background:#fff; }
  .file-hint{ font-size:11.5px; color:var(--muted); margin-top:6px; }

  /* kotak peringatan kuning (dipakai di halaman Edit Pengaduan) */
  .alert-warning{ background:var(--warning-bg); color:var(--warning-text); padding:12px 16px; border-radius:8px; font-size:13px; margin-bottom:18px; }

  /* kotak info di dashboard siswa */
  .info-box{ background:#EEF2FF; border-radius:10px; padding:16px; text-align:center; }
  .info-box .icon{ font-size:26px; margin-bottom:8px; }

  /* bubble percakapan tanggapan */
  .chat-bubble{ display:flex; gap:10px; margin-bottom:16px; }
  .chat-avatar{ width:34px; height:34px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; flex-shrink:0; }
  .chat-content{ background:#F3F5F9; border-radius:10px; padding:10px 14px; font-size:13.5px; max-width:100%; }
  .chat-meta{ font-size:11.5px; color:var(--muted); margin-bottom:3px; }

  /* =====================================================
     5. MODAL & TOAST
     ===================================================== */
  .modal-overlay{ position:fixed; inset:0; background:rgba(15,23,42,.5); display:flex; align-items:center; justify-content:center; z-index:100; }
  .modal-box{ background:#fff; border-radius:14px; padding:26px; width:340px; text-align:center; }
  .modal-icon{ font-size:34px; color:#DC2626; margin-bottom:10px; }
  .modal-title{ font-size:17px; font-weight:700; margin-bottom:6px; }
  .modal-text{ font-size:13.5px; color:var(--muted); margin-bottom:20px; }
  .modal-actions{ display:flex; gap:10px; }
  .modal-actions button{ flex:1; padding:10px; border-radius:8px; font-weight:600; font-size:13.5px; }
  .toast{ position:fixed; top:20px; right:20px; background:#111827; color:#fff; padding:12px 20px; border-radius:8px; font-size:13.5px; z-index:200; opacity:0; transform:translateY(-10px); transition:all .25s; }
  .toast.show{ opacity:1; transform:translateY(0); }

  /* =====================================================
     6. RESPONSIVE
     ===================================================== */
  @media (max-width: 900px){
    .stat-grid{ grid-template-columns:repeat(2,1fr); }
    .dash-grid{ grid-template-columns:1fr; }
    .sidebar{ position:fixed; left:-260px; z-index:50; transition:left .2s; height:100vh; }
    .sidebar.open{ left:0; }
    .login-illustration-side{ display:none; }
  }
</style>
</head>
<body>

  <!-- =====================================================
       HALAMAN 1: LOGIN SISWA
       NIS default: 12345 | Password default: siswa123
       ===================================================== -->
  <div id="login-page" class="<?= isset($_SESSION['siswa_id']) ? 'hidden' : '' ?>">
    <div class="login-card">
      <div class="login-form-side">
        <div class="login-logo">💬</div>
        <div class="login-title">Website Pengaduan Siswa</div>
        <div class="login-subtitle">Sampaikan keluhanmu, kami dengarkan.</div>

        <div class="login-error" id="login-error">NIS atau password salah.</div>

        <form id="login-form">
          <div class="form-group">
            <label>NIS (Nomor Induk Siswa)</label>
            <input type="text" id="login-nis" placeholder="Masukkan NIS Anda" required />
          </div>
          <div class="form-group">
            <label>Password</label>
            <div class="input-wrap">
              <input type="password" id="login-password" placeholder="Masukkan password Anda" required />
              <button type="button" class="toggle-password" onclick="togglePasswordField('login-password', this)">👁</button>
            </div>
          </div>
          <div class="remember-row">
            <input type="checkbox" id="remember-me" style="width:auto;">
            <label for="remember-me" style="margin:0; font-weight:400;">Ingat saya</label>
          </div>
          <button type="submit" class="btn-primary">Masuk</button>
        </form>
        <div class="login-link"><a href="#" onclick="showToast('Hubungi admin sekolah untuk reset password.'); return false;">Lupa password?</a></div>
        <div class="login-footer">© 2024 Website Pengaduan Siswa</div>
      </div>
      <div class="login-illustration-side">
        <svg viewBox="0 0 200 200"><circle cx="100" cy="70" r="35" fill="#2F6FED" opacity=".15"/><rect x="60" y="110" width="80" height="70" rx="14" fill="#2F6FED" opacity=".25"/><circle cx="100" cy="65" r="22" fill="#1E293B" opacity=".8"/></svg>
      </div>
    </div>
  </div>

  <!-- =====================================================
       APLIKASI UTAMA SISWA
       ===================================================== -->
  <div id="app" class="<?= isset($_SESSION['siswa_id']) ? '' : 'hidden' ?>">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-brand"><div class="icon-box">💬</div> PENGADUAN SISWA</div>
      <ul class="sidebar-menu">
        <li><a href="#" data-view="dashboard" class="active">📊 Dashboard</a></li>
        <li><a href="#" data-view="buat-pengaduan">📝 Buat Pengaduan</a></li>
        <li><a href="#" data-view="riwayat">🕘 Riwayat Pengaduan</a></li>
        <li><a href="#" data-view="profil">👤 Profil Saya</a></li>
      </ul>
      <div class="sidebar-footer">
        <button class="btn-logout" onclick="handleLogout()">↩ Logout</button>
      </div>
    </aside>

    <div class="main-area">
      <div class="topbar">
        <div class="topbar-left">
          <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
        </div>
        <div class="topbar-right">
          <button class="bell-btn">🔔</button>
          <div class="topbar-user">
            <div class="avatar-mini" id="topbar-avatar">A</div>
            <div><div class="name" id="topbar-name">Andi Pratama</div><div class="role">Siswa</div></div>
          </div>
        </div>
      </div>

      <div class="content">

        <!-- ============ VIEW: DASHBOARD SISWA ============ -->
        <section id="view-dashboard" class="view">
          <div class="page-title">Dashboard</div>
          <div class="stat-grid">
            <div class="stat-card stat-yellow"><div class="stat-label">Menunggu</div><div class="stat-value" id="stat-menunggu">0</div><div class="stat-sub">Pengaduan</div></div>
            <div class="stat-card stat-blue"><div class="stat-label">Diproses</div><div class="stat-value" id="stat-diproses">0</div><div class="stat-sub">Pengaduan</div></div>
            <div class="stat-card stat-green"><div class="stat-label">Selesai</div><div class="stat-value" id="stat-selesai">0</div><div class="stat-sub">Pengaduan</div></div>
            <div class="stat-card stat-red"><div class="stat-label">Ditolak</div><div class="stat-value" id="stat-ditolak">0</div><div class="stat-sub">Pengaduan</div></div>
          </div>
          <div class="dash-grid">
            <div class="card">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <strong>Pengaduan Terbaru</strong>
                <a href="#" data-view="riwayat" style="font-size:12.5px; color:var(--primary);">Lihat Semua</a>
              </div>
              <div id="recent-list"></div>
            </div>
            <div class="card">
              <strong>Informasi</strong>
              <p style="font-size:13.5px; color:var(--muted); margin:10px 0 18px; line-height:1.6;">Sampaikan pengaduan dengan jelas dan santun. Identitas Anda akan kami jaga.</p>
              <div class="info-box">
                <div class="icon">📣</div>
                <div style="font-weight:600; margin-bottom:4px;">Butuh bantuan?</div>
                <div style="font-size:12.5px; color:var(--muted); margin-bottom:12px;">Hubungi Guru BK atau Admin.</div>
                <button class="btn-outline" style="width:100%;" onclick="showToast('Silakan datang ke ruang BK sekolah.')">Lihat Semua</button>
              </div>
            </div>
          </div>
        </section>

        <!-- ============ VIEW: BUAT PENGADUAN ============ -->
        <section id="view-buat-pengaduan" class="view hidden">
          <div class="breadcrumb">Dashboard / Buat Pengaduan</div>
          <div class="page-title">Buat Pengaduan</div>
          <div class="card" style="max-width:600px;">
            <form id="form-buat-pengaduan">
              <div class="form-group">
                <label>Kategori Pengaduan</label>
                <select id="bp-kategori" required>
                  <option value="">Pilih kategori pengaduan</option>
                  <option>Fasilitas Sekolah</option><option>Perundungan</option>
                  <option>Kedisiplinan</option><option>Kebersihan</option>
                  <option>Akademik</option><option>Lainnya</option>
                </select>
              </div>
              <div class="form-group">
                <label>Judul Pengaduan</label>
                <input type="text" id="bp-judul" placeholder="Masukkan judul pengaduan" required />
              </div>
              <div class="form-group">
                <label>Deskripsi Pengaduan</label>
                <textarea id="bp-deskripsi" rows="5" placeholder="Jelaskan pengaduan Anda secara detail..." required></textarea>
              </div>
              <div class="form-group">
                <label>Lampiran (Opsional)</label><br>
                <label class="file-input-btn">Pilih File<input type="file" id="bp-file" style="display:none;" onchange="updateFileName(this)"></label>
                <span id="bp-filename" style="font-size:13px; color:var(--muted); margin-left:8px;">Tidak ada file dipilih</span>
                <div class="file-hint">Format: JPG, PNG, PDF. Maks. 5MB</div>
              </div>
              <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn-outline" data-view="dashboard">Batal</button>
                <button type="submit" class="btn-add">Kirim Pengaduan</button>
              </div>
            </form>
          </div>
        </section>

        <!-- ============ VIEW: RIWAYAT PENGADUAN ============ -->
        <section id="view-riwayat" class="view hidden">
          <div class="breadcrumb">Dashboard / Riwayat Pengaduan</div>
          <div class="page-title">Riwayat Pengaduan</div>
          <div class="tabs" id="riwayat-tabs">
            <button class="tab-btn active" data-status="Semua">Semua</button>
            <button class="tab-btn" data-status="Menunggu">Menunggu</button>
            <button class="tab-btn" data-status="Diproses">Diproses</button>
            <button class="tab-btn" data-status="Selesai">Selesai</button>
            <button class="tab-btn" data-status="Ditolak">Ditolak</button>
          </div>
          <div class="card">
            <table>
              <thead><tr><th>ID</th><th>Judul</th><th>Kategori</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
              <tbody id="riwayat-table-body"></tbody>
            </table>
            <div class="pagination" id="riwayat-pagination"></div>
          </div>
        </section>

        <!-- ============ VIEW: DETAIL PENGADUAN ============ -->
        <section id="view-detail" class="view hidden">
          <div class="breadcrumb">Dashboard / Riwayat Pengaduan / Detail</div>
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <div class="page-title" style="margin:0;">Detail Pengaduan</div>
            <button class="btn-outline" data-view="riwayat">← Kembali</button>
          </div>
          <div class="card" id="detail-body"></div>
        </section>

        <!-- ============ VIEW: TANGGAPAN PENGADUAN ============ -->
        <section id="view-tanggapan" class="view hidden">
          <div class="breadcrumb">Dashboard / Riwayat Pengaduan / Tanggapan</div>
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <div class="page-title" style="margin:0;">Tanggapan Pengaduan</div>
            <button class="btn-outline" data-view="riwayat">← Kembali</button>
          </div>
          <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
              <div>
                <div style="font-size:12px; color:var(--muted);">ID Pengaduan</div>
                <div style="font-weight:700;" id="tg-id"></div>
              </div>
              <div style="text-align:right;">
                <div style="font-size:12px; color:var(--muted);">Judul</div>
                <div style="font-weight:600;" id="tg-judul"></div>
              </div>
              <div id="tg-status"></div>
            </div>
            <hr style="border:none; border-top:1px solid var(--border); margin-bottom:16px;">
            <div style="font-weight:600; margin-bottom:12px;">Tanggapan dari Admin / Guru BK</div>
            <div id="tg-thread"></div>
            <div class="form-group" style="margin-top:14px;">
              <label>Kirim Balasan (Opsional)</label>
              <textarea id="tg-balasan" rows="3" placeholder="Tulis balasan Anda..."></textarea>
            </div>
            <div style="text-align:right;"><button class="btn-add" onclick="kirimBalasan()">Kirim Balasan</button></div>
          </div>
        </section>

        <!-- ============ VIEW: EDIT PENGADUAN ============ -->
        <section id="view-edit" class="view hidden">
          <div class="breadcrumb">Dashboard / Riwayat Pengaduan / Edit</div>
          <div class="page-title">Edit Pengaduan</div>
          <div class="card" style="max-width:600px;">
            <div class="alert-warning">⚠ Anda hanya dapat mengedit pengaduan dengan status Menunggu.</div>
            <form id="form-edit-pengaduan">
              <div class="form-group">
                <label>Kategori Pengaduan</label>
                <select id="ep-kategori">
                  <option>Fasilitas Sekolah</option><option>Perundungan</option>
                  <option>Kedisiplinan</option><option>Kebersihan</option>
                  <option>Akademik</option><option>Lainnya</option>
                </select>
              </div>
              <div class="form-group"><label>Judul Pengaduan</label><input type="text" id="ep-judul" required></div>
              <div class="form-group"><label>Deskripsi Pengaduan</label><textarea id="ep-deskripsi" rows="5" required></textarea></div>
              <div class="form-group">
                <label>Lampiran (Opsional)</label><br>
                <span id="ep-filename" style="font-size:13px;"></span>
                <button type="button" class="btn-outline" style="padding:4px 10px; font-size:12px; margin-left:8px;" onclick="document.getElementById('ep-filename').textContent='Tidak ada file'; showToast('Lampiran dihapus.')">Hapus</button>
              </div>
              <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn-outline" data-view="riwayat">Batal</button>
                <button type="submit" class="btn-add">Simpan Perubahan</button>
              </div>
            </form>
          </div>
        </section>

        <!-- ============ VIEW: PROFIL SAYA ============ -->
        <section id="view-profil" class="view hidden">
          <div class="breadcrumb">Dashboard / Profil Saya</div>
          <div class="page-title">Profil Saya</div>
          <div class="card" style="max-width:480px; text-align:center;">
            <div style="width:80px; height:80px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:30px; font-weight:700; margin:0 auto 12px;">A</div>
            <div style="font-weight:700; font-size:16px;">Andi Pratama</div>
            <div style="color:var(--muted); font-size:13px; margin-bottom:20px;">Siswa</div>
            <div style="text-align:left; border-top:1px solid var(--border); padding-top:16px;">
              <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:13.5px;"><span style="color:var(--muted);">NIS</span><span style="font-weight:600;">12345</span></div>
              <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:13.5px;"><span style="color:var(--muted);">Email</span><span style="font-weight:600;">andi.pratama@sekolah.sch.id</span></div>
              <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:13.5px;"><span style="color:var(--muted);">No. HP</span><span style="font-weight:600;">0812-3456-7890</span></div>
              <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:13.5px;"><span style="color:var(--muted);">Kelas</span><span style="font-weight:600;">X IPA 1</span></div>
            </div>
            <button class="btn-outline" style="margin-top:16px; width:100%;" onclick="showToast('Fitur ganti password belum tersedia di demo ini.')">Ganti Password</button>
          </div>
        </section>

      </div>
    </div>
  </div>

  <div id="modal-root"></div>
  <div id="toast" class="toast"></div>

<script>
/* =========================================================================
   FILE: (bagian <script>) siswa.php
   FUNGSI: Menghubungkan tampilan Panel Siswa ke database MySQL lewat
   endpoint-endpoint di folder api/siswa/*.php menggunakan fetch().
   ========================================================================= */

const API = 'api/siswa';

let currentPengaduanKode = null; // pengaduan yang sedang dibuka
let activeTab = 'Semua';         // tab status yang aktif di halaman riwayat
let riwayatPage = 1;
const ROWS_PER_PAGE = 5; // HARUS SAMA dengan BARIS_PER_HALAMAN di file PHP

let kategoriCache = []; // daftar kategori {id, nama}, diambil sekali dari server

/* =========================================================
   FUNGSI BANTU: memanggil API dan mengurus error secara seragam
   ========================================================= */
async function panggilApi(url, opsi = {}) {
  try {
    const res = await fetch(url, opsi);
    const json = await res.json();
    if (!json.success) {
      showToast(json.message || 'Terjadi kesalahan.');
      if (res.status === 401) {
        document.getElementById('app').classList.add('hidden');
        document.getElementById('login-page').classList.remove('hidden');
      }
    }
    return json;
  } catch (err) {
    console.error('Gagal memanggil API:', url, err);
    showToast('Tidak bisa terhubung ke server. Cek koneksi/konfigurasi database.');
    return { success: false, data: null };
  }
}

// mencegah HTML/JS liar dari isi data merusak tampilan (XSS sederhana)
function escapeHtml(teks){
  const div = document.createElement('div');
  div.textContent = teks ?? '';
  return div.innerHTML;
}

/* =========================================================
   LOGIN & LOGOUT
   ========================================================= */
document.getElementById('login-form').addEventListener('submit', async function(e){
  e.preventDefault();
  const nis = document.getElementById('login-nis').value.trim();
  const password = document.getElementById('login-password').value.trim();
  const errorBox = document.getElementById('login-error');

  const json = await panggilApi(`${API}/login.php`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ nis, password })
  });

  if (json.success) {
    errorBox.style.display = 'none';
    document.getElementById('login-page').classList.add('hidden');
    document.getElementById('app').classList.remove('hidden');
    initApp(json.data);
  } else {
    errorBox.textContent = json.message || 'NIS atau password salah.';
    errorBox.style.display = 'block';
  }
});

function handleLogout(){
  showModal({
    icon:'↩', title:'Logout', text:'Apakah Anda yakin ingin keluar?',
    confirmLabel:'Logout', confirmClass:'btn-danger',
    onConfirm: async function(){
      await panggilApi(`${API}/logout.php`);
      document.getElementById('app').classList.add('hidden');
      document.getElementById('login-page').classList.remove('hidden');
      document.getElementById('login-form').reset();
    }
  });
}

function togglePasswordField(id, btn){
  const field = document.getElementById(id);
  if(field.type === 'password'){ field.type = 'text'; btn.textContent = '🙈'; }
  else { field.type = 'password'; btn.textContent = '👁'; }
}

/* =========================================================
   NAVIGASI ANTAR HALAMAN
   ========================================================= */
document.addEventListener('click', function(e){
  const target = e.target.closest('[data-view]');
  if(!target) return;
  e.preventDefault();
  navigateTo(target.getAttribute('data-view'));
});

function navigateTo(viewName){
  document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
  const target = document.getElementById('view-' + viewName);
  if(target) target.classList.remove('hidden');

  document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
  const activeLink = document.querySelector('.sidebar-menu a[data-view="'+viewName+'"]');
  if(activeLink) activeLink.classList.add('active');

  if(viewName === 'dashboard') renderDashboard();
  if(viewName === 'riwayat') renderRiwayatTable();
  if(viewName === 'buat-pengaduan') {
    document.getElementById('form-buat-pengaduan').reset();
    document.getElementById('bp-filename').textContent = 'Tidak ada file dipilih';
  }
  if(viewName === 'profil') renderProfil();

  document.getElementById('sidebar').classList.remove('open');
  window.scrollTo(0,0);
}

function badgeHtml(status){
  const map = {Menunggu:'badge-menunggu', Diproses:'badge-diproses', Selesai:'badge-selesai', Ditolak:'badge-ditolak'};
  return `<span class="badge ${map[status]||''}">${status}</span>`;
}

/* =========================================================
   KATEGORI (diambil sekali dari server, dipakai di 2 dropdown)
   ========================================================= */
async function muatKategori(){
  const json = await panggilApi(`${API}/kategori_get.php`);
  if(!json.success) return;
  kategoriCache = json.data;

  const isiOptions = (selectEl, placeholder) => {
    selectEl.innerHTML = `<option value="">${placeholder}</option>` +
      kategoriCache.map(k => `<option value="${k.id}">${escapeHtml(k.nama)}</option>`).join('');
  };
  isiOptions(document.getElementById('bp-kategori'), 'Pilih kategori pengaduan');
  isiOptions(document.getElementById('ep-kategori'), 'Pilih kategori pengaduan');
}

/* =========================================================
   RENDER DASHBOARD
   ========================================================= */
async function renderDashboard(){
  const json = await panggilApi(`${API}/dashboard_stats.php`);
  if(!json.success) return;
  const d = json.data;

  document.getElementById('stat-menunggu').textContent = d.menunggu;
  document.getElementById('stat-diproses').textContent = d.diproses;
  document.getElementById('stat-selesai').textContent  = d.selesai;
  document.getElementById('stat-ditolak').textContent  = d.ditolak;

  document.getElementById('recent-list').innerHTML = d.recent.map(p => `
    <div style="display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid var(--border); font-size:13px;">
      <div><div style="font-weight:600;">${escapeHtml(p.judul)}</div><div style="color:var(--muted); font-size:12px;">${p.tanggal}</div></div>
      ${badgeHtml(p.status)}
    </div>`).join('') || '<p style="color:var(--muted); font-size:13px;">Belum ada pengaduan.</p>';
}

/* =========================================================
   BUAT PENGADUAN (pakai FormData karena ada file lampiran)
   ========================================================= */
function updateFileName(input){
  document.getElementById('bp-filename').textContent = input.files.length ? input.files[0].name : 'Tidak ada file dipilih';
}

document.getElementById('form-buat-pengaduan').addEventListener('submit', async function(e){
  e.preventDefault();

  // FormData otomatis membaca semua <input>/<select>/<textarea> yang punya atribut "name".
  // Karena elemen HTML aslinya belum punya atribut name, kita susun manual di sini.
  const formData = new FormData();
  formData.append('kategori_id', document.getElementById('bp-kategori').value);
  formData.append('judul', document.getElementById('bp-judul').value.trim());
  formData.append('deskripsi', document.getElementById('bp-deskripsi').value.trim());

  const fileInput = document.getElementById('bp-file');
  if (fileInput.files.length) {
    formData.append('lampiran', fileInput.files[0]);
  }

  // PENTING: jangan set header 'Content-Type' secara manual saat body berupa
  // FormData - browser akan mengaturnya sendiri (termasuk "boundary" upload file).
  const json = await panggilApi(`${API}/pengaduan_simpan.php`, {
    method: 'POST',
    body: formData
  });

  if(json.success){
    showToast(json.message);
    navigateTo('dashboard');
  }
});

/* =========================================================
   RIWAYAT PENGADUAN (dengan tab status)
   ========================================================= */
document.getElementById('riwayat-tabs').addEventListener('click', function(e){
  const btn = e.target.closest('.tab-btn');
  if(!btn) return;
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  activeTab = btn.getAttribute('data-status');
  riwayatPage = 1;
  renderRiwayatTable();
});

async function renderRiwayatTable(){
  const json = await panggilApi(`${API}/pengaduan_get.php?status=${encodeURIComponent(activeTab)}&halaman=${riwayatPage}`);
  if(!json.success) return;

  document.getElementById('riwayat-table-body').innerHTML = json.data.items.map(p => `
    <tr>
      <td>${p.kode}</td>
      <td>${escapeHtml(p.judul)}</td>
      <td>${escapeHtml(p.kategori)}</td>
      <td>${p.tanggal}</td>
      <td>${badgeHtml(p.status)}</td>
      <td><button class="table-icon-btn" onclick="openDetail('${p.kode}')">👁️</button></td>
    </tr>`).join('') || `<tr><td colspan="6" style="text-align:center; color:var(--muted); padding:24px;">Belum ada pengaduan.</td></tr>`;

  renderPagination(json.data.total);
}

function renderPagination(totalRows){
  const totalPages = Math.max(1, Math.ceil(totalRows / ROWS_PER_PAGE));
  let html = `<div class="pagination-info">Menampilkan ${Math.min(ROWS_PER_PAGE,totalRows)} dari ${totalRows} data</div>`;
  for(let i=1;i<=totalPages;i++){
    html += `<button class="${i===riwayatPage?'active':''}" onclick="riwayatPage=${i}; renderRiwayatTable();">${i}</button>`;
  }
  document.getElementById('riwayat-pagination').innerHTML = html;
}

/* =========================================================
   DETAIL PENGADUAN
   ========================================================= */
async function openDetail(kode){
  currentPengaduanKode = kode;
  const json = await panggilApi(`${API}/pengaduan_detail.php?kode=${encodeURIComponent(kode)}`);
  if(!json.success) return;
  const p = json.data;
  window._pengaduanAktif = p; // simpan untuk dipakai tombol Edit/Hapus/Lihat Tanggapan
  navigateTo('detail');

  document.getElementById('detail-body').innerHTML = `
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:20px;">
      <div>
        <div style="font-size:12px; color:var(--muted);">ID Pengaduan</div>
        <div style="font-weight:600; margin-bottom:14px;">${p.kode}</div>
        <div style="font-size:12px; color:var(--muted);">Kategori</div>
        <div style="font-weight:600; margin-bottom:14px;">${escapeHtml(p.kategori)}</div>
        <div style="font-size:12px; color:var(--muted);">Tanggal</div>
        <div style="font-weight:600;">${p.tanggal}</div>
      </div>
      <div>
        <div style="font-size:12px; color:var(--muted);">Judul Pengaduan</div>
        <div style="font-weight:700; font-size:16px; margin-bottom:10px;">${escapeHtml(p.judul)}</div>
        <div style="font-size:12px; color:var(--muted);">Deskripsi</div>
        <p style="margin:6px 0 14px; line-height:1.6;">${escapeHtml(p.deskripsi)}</p>
        ${p.lampiran ? `<div style="font-size:12px; color:var(--muted);">Lampiran</div><div style="font-weight:600; margin-bottom:10px;"><a href="uploads/${encodeURIComponent(p.lampiran)}" target="_blank">📎 ${escapeHtml(p.lampiran)}</a></div>` : ''}
      </div>
    </div>
    <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border); padding-top:16px;">
      <div>
        <div style="font-size:12px; color:var(--muted); margin-bottom:4px;">Tanggapan</div>
        <div style="font-size:13.5px;">${p.percakapan.length ? p.percakapan.length+' balasan tersedia' : 'Belum ada tanggapan.'}</div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:12px; color:var(--muted); margin-bottom:4px;">Status Pengaduan</div>
        ${badgeHtml(p.status)}
      </div>
    </div>
    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
      ${p.percakapan.length || p.status !== 'Menunggu' ? `<button class="btn-outline" onclick="openTanggapan('${p.kode}')">Lihat Tanggapan</button>` : ''}
      ${p.status === 'Menunggu' ? `
        <button class="btn-outline" onclick="openEdit('${p.kode}')">✏️ Edit</button>
        <button class="btn-danger" onclick="confirmHapus('${p.kode}')">🗑️ Hapus</button>` : ''}
    </div>`;
}

/* =========================================================
   TANGGAPAN PENGADUAN (percakapan admin <-> siswa)
   ========================================================= */
async function openTanggapan(kode){
  currentPengaduanKode = kode;
  const json = await panggilApi(`${API}/tanggapan_get.php?kode=${encodeURIComponent(kode)}`);
  if(!json.success) return;
  const p = json.data;
  navigateTo('tanggapan');

  document.getElementById('tg-id').textContent = p.kode;
  document.getElementById('tg-judul').textContent = p.judul;
  document.getElementById('tg-status').innerHTML = badgeHtml(p.status);
  renderTanggapanThread(p.percakapan);
  document.getElementById('tg-balasan').value = '';
}

function renderTanggapanThread(percakapan){
  document.getElementById('tg-thread').innerHTML = percakapan.length ? percakapan.map(c => `
    <div class="chat-bubble">
      <div class="chat-avatar">${c.dari === 'Admin' ? 'A' : window._namaSiswa.charAt(0)}</div>
      <div>
        <div class="chat-meta">${c.dari} • ${c.waktu}</div>
        <div class="chat-content">${escapeHtml(c.pesan)}</div>
      </div>
    </div>`).join('') : `<p style="color:var(--muted); font-size:13.5px;">Belum ada tanggapan.</p>`;
}

async function kirimBalasan(){
  const teks = document.getElementById('tg-balasan').value.trim();
  if(!teks) return showToast('Tulis balasan terlebih dahulu.');

  const json = await panggilApi(`${API}/tanggapan_kirim.php`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ kode: currentPengaduanKode, pesan: teks })
  });

  if(json.success){
    document.getElementById('tg-balasan').value = '';
    // ambil ulang thread supaya balasan baru langsung muncul
    const ulang = await panggilApi(`${API}/tanggapan_get.php?kode=${encodeURIComponent(currentPengaduanKode)}`);
    if(ulang.success) renderTanggapanThread(ulang.data.percakapan);
    showToast('Balasan terkirim.');
  }
}

/* =========================================================
   EDIT PENGADUAN (hanya boleh saat status Menunggu)
   ========================================================= */
function openEdit(kode){
  currentPengaduanKode = kode;
  const p = window._pengaduanAktif; // hasil openDetail() sebelumnya
  navigateTo('edit');
  document.getElementById('ep-kategori').value = p.kategori_id;
  document.getElementById('ep-judul').value = p.judul;
  document.getElementById('ep-deskripsi').value = p.deskripsi;
  document.getElementById('ep-filename').textContent = p.lampiran || 'Tidak ada file';
}

document.getElementById('form-edit-pengaduan').addEventListener('submit', async function(e){
  e.preventDefault();
  const data = {
    kode: currentPengaduanKode,
    kategori_id: document.getElementById('ep-kategori').value,
    judul: document.getElementById('ep-judul').value.trim(),
    deskripsi: document.getElementById('ep-deskripsi').value.trim(),
  };

  const json = await panggilApi(`${API}/pengaduan_update.php`, {
    method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data)
  });

  if(json.success){
    showToast(json.message);
    navigateTo('riwayat');
  }
});

/* =========================================================
   HAPUS PENGADUAN (dengan modal konfirmasi)
   ========================================================= */
function confirmHapus(kode){
  showModal({
    icon:'⚠', title:'Hapus Pengaduan', text:'Apakah Anda yakin ingin menghapus pengaduan ini? Tindakan ini tidak dapat dibatalkan.',
    confirmLabel:'Hapus', confirmClass:'btn-danger',
    onConfirm: async function(){
      const json = await panggilApi(`${API}/pengaduan_hapus.php`, {
        method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ kode })
      });
      if(json.success){
        showToast(json.message);
        navigateTo('riwayat');
      }
    }
  });
}

/* =========================================================
   PROFIL SAYA
   ========================================================= */
async function renderProfil(){
  const json = await panggilApi(`${API}/profil_get.php`);
  if(!json.success) return;
  const p = json.data;
  const view = document.getElementById('view-profil');
  view.querySelector('div[style*="font-weight:700; font-size:16px"]').textContent = p.nama;

  const baris = view.querySelectorAll('.card > div[style*="border-top"] > div');
  // urutan baris tetap: NIS, Email, No. HP, Kelas (sesuai HTML asli)
  if (baris[0]) baris[0].querySelector('span:last-child').textContent = p.nis;
  if (baris[1]) baris[1].querySelector('span:last-child').textContent = p.email || '-';
  if (baris[2]) baris[2].querySelector('span:last-child').textContent = p.no_hp || '-';
  if (baris[3]) baris[3].querySelector('span:last-child').textContent = p.kelas;

  view.querySelector('.card > div[style*="border-radius:50%"]').textContent = p.nama.charAt(0).toUpperCase();
}

/* =========================================================
   MODAL & TOAST
   ========================================================= */
function showModal({icon, title, text, confirmLabel, confirmClass, onConfirm}){
  const root = document.getElementById('modal-root');
  root.innerHTML = `
    <div class="modal-overlay">
      <div class="modal-box">
        <div class="modal-icon">${icon}</div>
        <div class="modal-title">${title}</div>
        <div class="modal-text">${text}</div>
        <div class="modal-actions">
          <button class="btn-outline" id="modal-cancel">Batal</button>
          <button class="${confirmClass}" id="modal-confirm">${confirmLabel}</button>
        </div>
      </div>
    </div>`;
  document.getElementById('modal-cancel').onclick = closeModal;
  document.getElementById('modal-confirm').onclick = function(){ onConfirm(); closeModal(); };
}
function closeModal(){ document.getElementById('modal-root').innerHTML = ''; }

function showToast(message){
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.add('show');
  setTimeout(()=> toast.classList.remove('show'), 2500);
}

/* =========================================================
   INISIALISASI SETELAH LOGIN
   ========================================================= */
function initApp(dataSiswa){
  // dataSiswa dikirim dari respons login.php: { nama, nis, kelas }.
  // Jika halaman baru saja di-reload dan session MASIH aktif, dataSiswa
  // tidak ada -> kita pakai teks yang sudah tercetak PHP di topbar (lihat siswa.php).
  if (dataSiswa) {
    document.getElementById('topbar-name').textContent = dataSiswa.nama;
    document.getElementById('topbar-avatar').textContent = dataSiswa.nama.charAt(0).toUpperCase();
    window._namaSiswa = dataSiswa.nama;
  } else {
    window._namaSiswa = document.getElementById('topbar-name').textContent;
  }
  muatKategori();
  navigateTo('dashboard');
}

// Jika saat halaman dimuat session siswa MASIH AKTIF (server sudah menampilkan
// #app, bukan #login-page), langsung jalankan initApp() supaya data terisi.
document.addEventListener('DOMContentLoaded', function(){
  if(!document.getElementById('app').classList.contains('hidden')){
    initApp(null);
  }
});
</script>
</body>
</html>
