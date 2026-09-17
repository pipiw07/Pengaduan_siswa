<?php
/**
 * =====================================================================
 * FILE: admin.php
 * FUNGSI: Halaman utama Panel Admin. Tampilan (HTML/CSS) SAMA PERSIS
 *         dengan Admin.html aslinya, tapi bagian <script> di bawah
 *         sudah diganti total: sekarang mengambil & menyimpan data
 *         lewat fetch() ke file-file di folder api/admin/*.php yang
 *         terhubung ke database MySQL (bukan lagi array JS/mock data).
 *
 * CATATAN UNTUK DEBUG:
 * - Buka Developer Tools (F12) -> tab "Network" untuk melihat request
 *   fetch() yang dikirim & response JSON yang diterima dari server.
 * - Semua endpoint api/admin/*.php mengembalikan format yang sama:
 *   { success: true/false, message: "...", data: ... }
 * =====================================================================
 */
require_once __DIR__ . '/includes/auth.php'; // supaya $_SESSION bisa dibaca di halaman ini
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Panel Admin - Pengaduan Siswa</title>
<style>
  /* =====================================================
     1. VARIABEL WARNA & PENGATURAN DASAR
     Semua warna dikumpulkan di sini supaya mudah diubah
     ===================================================== */
  :root{
    --navy: #16213E;          /* warna dasar sidebar (biru navy gelap) */
    --navy-light: #1E2C52;    /* warna hover/aktif di sidebar */
    --primary: #2F6FED;       /* warna aksen utama (tombol, link aktif) */
    --primary-dark: #1E54C7;  /* warna tombol saat dihover */
    --bg: #F3F5F9;            /* warna latar belakang halaman */
    --white: #FFFFFF;
    --text: #1F2937;          /* warna teks utama */
    --muted: #6B7280;         /* warna teks sekunder/abu-abu */
    --border: #E5E7EB;        /* warna garis pembatas */
    --warning-bg:#FEF3C7; --warning-text:#92400E; /* badge "Menunggu" */
    --info-bg:#DBEAFE;    --info-text:#1E40AF;    /* badge "Diproses" */
    --success-bg:#D1FAE5; --success-text:#065F46; /* badge "Selesai" */
    --danger-bg:#FEE2E2;  --danger-text:#991B1B;  /* badge "Ditolak" */
    --radius: 10px;            /* radius sudut membulat standar */
    --shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
  }

  /* reset dasar supaya tampilan konsisten di semua browser */
  *{ box-sizing:border-box; margin:0; padding:0; }
  body{
    font-family:'Segoe UI', Roboto, Arial, sans-serif; /* font utama */
    background:var(--bg);
    color:var(--text);
    min-height:100vh;
  }
  button{ font-family:inherit; cursor:pointer; border:none; }
  input, select, textarea{ font-family:inherit; }
  a{ text-decoration:none; color:inherit; }
  ul{ list-style:none; }
  .hidden{ display:none !important; } /* class bantu untuk sembunyikan elemen */

  /* =====================================================
     2. HALAMAN LOGIN
     ===================================================== */
  #login-page{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:var(--bg);
    padding:20px;
  }
  .login-card{
    display:flex;
    max-width:900px;
    width:100%;
    background:var(--white);
    border-radius:16px;
    box-shadow:var(--shadow);
    overflow:hidden;
  }
  .login-form-side{
    flex:1;
    padding:48px;
    display:flex;
    flex-direction:column;
    justify-content:center;
  }
  .login-illustration-side{
    flex:1;
    background:#EEF2FF;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:32px;
  }
  .login-illustration-side svg{ width:100%; max-width:280px; }
  .login-logo{
    width:56px; height:56px;
    background:var(--primary);
    border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    margin-bottom:16px;
    color:#fff; font-size:26px;
  }
  .login-title{ font-size:22px; font-weight:700; margin-bottom:4px; }
  .login-subtitle{ color:var(--primary); font-weight:600; font-size:14px; margin-bottom:8px; }
  .login-desc{ color:var(--muted); font-size:14px; margin-bottom:28px; }
  .form-group{ margin-bottom:18px; }
  .form-group label{ display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:var(--text); }
  .input-wrap{ position:relative; }
  .form-group input, .form-group select, .form-group textarea{
    width:100%;
    padding:11px 14px;
    border:1px solid var(--border);
    border-radius:8px;
    font-size:14px;
    outline:none;
    transition:border-color .15s;
  }
  .form-group input:focus, .form-group select:focus, .form-group textarea:focus{
    border-color:var(--primary);
  }
  .toggle-password{
    position:absolute; right:12px; top:50%; transform:translateY(-50%);
    background:none; color:var(--muted); font-size:13px;
  }
  .btn-primary{
    width:100%;
    background:var(--primary);
    color:#fff;
    padding:12px;
    border-radius:8px;
    font-weight:600;
    font-size:14px;
    transition:background .15s;
  }
  .btn-primary:hover{ background:var(--primary-dark); }
  .login-footer{ text-align:center; color:var(--muted); font-size:12px; margin-top:24px; }
  .login-error{
    background:var(--danger-bg); color:var(--danger-text);
    padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:16px;
    display:none; /* muncul hanya saat login gagal */
  }

  /* =====================================================
     3. LAYOUT UTAMA (SIDEBAR + TOPBAR + KONTEN)
     ===================================================== */
  #app{ display:flex; min-height:100vh; }

  /* --- Sidebar kiri --- */
  .sidebar{
    width:250px;
    background:var(--navy);
    color:#CBD5E1;
    display:flex;
    flex-direction:column;
    flex-shrink:0;
    position:sticky; top:0; height:100vh; overflow-y:auto;
  }
  .sidebar-brand{
    display:flex; align-items:center; gap:10px;
    padding:20px 20px; font-weight:700; color:#fff; font-size:15px;
    border-bottom:1px solid rgba(255,255,255,.08);
  }
  .sidebar-brand .icon-box{
    width:34px; height:34px; background:var(--primary);
    border-radius:8px; display:flex; align-items:center; justify-content:center;
  }
  .sidebar-section-label{
    font-size:11px; color:#64748B; text-transform:uppercase;
    padding:18px 20px 8px; letter-spacing:.03em;
  }
  .sidebar-menu li a{
    display:flex; align-items:center; gap:10px;
    padding:11px 20px; font-size:14px; color:#CBD5E1;
    border-left:3px solid transparent;
    transition:background .15s;
  }
  .sidebar-menu li a:hover{ background:var(--navy-light); }
  .sidebar-menu li a.active{
    background:var(--primary); color:#fff; border-left-color:#fff;
  }
  .sidebar-footer{ margin-top:auto; padding:16px 20px; border-top:1px solid rgba(255,255,255,.08); }
  .btn-logout{
    display:flex; align-items:center; gap:8px;
    color:#FCA5A5; background:none; font-size:14px; width:100%; padding:8px 0;
  }

  /* --- Area konten kanan --- */
  .main-area{ flex:1; display:flex; flex-direction:column; min-width:0; }
  .topbar{
    background:#fff; border-bottom:1px solid var(--border);
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 28px;
  }
  .topbar-left{ display:flex; align-items:center; gap:16px; }
  .hamburger{ background:none; font-size:20px; color:var(--muted); }
  .topbar-right{ display:flex; align-items:center; gap:18px; }
  .bell-btn{ background:none; font-size:18px; color:var(--muted); position:relative; }
  .avatar-mini{
    width:38px; height:38px; border-radius:50%; background:var(--primary);
    color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600;
  }
  .topbar-user{ display:flex; align-items:center; gap:10px; }
  .topbar-user .name{ font-size:13px; font-weight:600; }
  .topbar-user .role{ font-size:11px; color:var(--muted); }

  .content{ padding:28px; flex:1; }
  .breadcrumb{ font-size:12px; color:var(--muted); margin-bottom:4px; }
  .page-title{ font-size:22px; font-weight:700; margin-bottom:20px; }

  /* =====================================================
     4. KOMPONEN UMUM: KARTU, TABEL, BADGE, TOMBOL
     ===================================================== */
  .card{
    background:#fff; border-radius:var(--radius); box-shadow:var(--shadow);
    padding:20px; border:1px solid var(--border);
  }

  /* baris kartu statistik di dashboard */
  .stat-grid{
    display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px;
  }
  .stat-card{
    border-radius:var(--radius); padding:18px; box-shadow:var(--shadow);
  }
  .stat-card .stat-value{ font-size:26px; font-weight:700; margin:4px 0 2px; }
  .stat-card .stat-label{ font-size:13px; font-weight:600; }
  .stat-card .stat-sub{ font-size:12px; opacity:.75; }
  .stat-blue{ background:#EAF1FF; color:#1E3A8A; }
  .stat-yellow{ background:var(--warning-bg); color:var(--warning-text); }
  .stat-green{ background:#E0F2E9; color:#065F46; }
  .stat-teal{ background:#DCFCE7; color:#065F46; }

  .dash-grid{ display:grid; grid-template-columns:1.4fr 1fr; gap:16px; }

  /* grafik garis sederhana pakai svg */
  .chart-wrap{ margin-top:10px; }

  table{ width:100%; border-collapse:collapse; font-size:13.5px; }
  thead th{
    text-align:left; padding:10px 12px; color:var(--muted); font-weight:600;
    border-bottom:1px solid var(--border); font-size:12.5px;
  }
  tbody td{ padding:12px; border-bottom:1px solid var(--border); }
  tbody tr:hover{ background:#FAFBFF; }
  .table-icon-btn{ background:none; color:var(--muted); font-size:15px; padding:4px 6px; }
  .table-icon-btn:hover{ color:var(--primary); }

  .badge{
    display:inline-block; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600;
  }
  .badge-menunggu{ background:var(--warning-bg); color:var(--warning-text); }
  .badge-diproses{ background:var(--info-bg); color:var(--info-text); }
  .badge-selesai{ background:var(--success-bg); color:var(--success-text); }
  .badge-ditolak{ background:var(--danger-bg); color:var(--danger-text); }

  .btn-add{
    background:var(--primary); color:#fff; padding:10px 16px; border-radius:8px;
    font-size:13.5px; font-weight:600; display:flex; align-items:center; gap:6px;
  }
  .btn-add:hover{ background:var(--primary-dark); }
  .btn-outline{
    background:#fff; border:1px solid var(--border); color:var(--text);
    padding:10px 16px; border-radius:8px; font-size:13.5px; font-weight:600;
  }
  .btn-outline:hover{ background:#F3F4F6; }
  .btn-danger{ background:#DC2626; color:#fff; padding:10px 16px; border-radius:8px; font-weight:600; font-size:13.5px; }
  .btn-danger:hover{ background:#B91C1C; }

  .toolbar{ display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; gap:12px; flex-wrap:wrap; }
  .search-box{
    display:flex; align-items:center; gap:8px; border:1px solid var(--border);
    border-radius:8px; padding:9px 12px; background:#fff; min-width:220px; flex:1; max-width:320px;
  }
  .search-box input{ border:none; outline:none; font-size:13.5px; width:100%; }
  .filter-select{
    padding:9px 12px; border:1px solid var(--border); border-radius:8px; font-size:13px; background:#fff;
  }

  .pagination{ display:flex; align-items:center; justify-content:flex-end; gap:6px; margin-top:14px; }
  .pagination button{
    width:30px; height:30px; border-radius:6px; border:1px solid var(--border); background:#fff; font-size:13px;
  }
  .pagination button.active{ background:var(--primary); color:#fff; border-color:var(--primary); }
  .pagination-info{ font-size:12.5px; color:var(--muted); margin-right:auto; }

  /* =====================================================
     5. MODAL (POPUP KONFIRMASI)
     ===================================================== */
  .modal-overlay{
    position:fixed; inset:0; background:rgba(15,23,42,.5);
    display:flex; align-items:center; justify-content:center; z-index:100;
  }
  .modal-box{
    background:#fff; border-radius:14px; padding:26px; width:340px; text-align:center;
  }
  .modal-icon{ font-size:34px; color:#DC2626; margin-bottom:10px; }
  .modal-title{ font-size:17px; font-weight:700; margin-bottom:6px; }
  .modal-text{ font-size:13.5px; color:var(--muted); margin-bottom:20px; }
  .modal-actions{ display:flex; gap:10px; }
  .modal-actions button{ flex:1; padding:10px; border-radius:8px; font-weight:600; font-size:13.5px; }

  .toast{
    position:fixed; top:20px; right:20px; background:#111827; color:#fff;
    padding:12px 20px; border-radius:8px; font-size:13.5px; z-index:200;
    opacity:0; transform:translateY(-10px); transition:all .25s;
  }
  .toast.show{ opacity:1; transform:translateY(0); }

  /* =====================================================
     6. RESPONSIVE (layar kecil)
     ===================================================== */
  @media (max-width: 900px){
    .stat-grid{ grid-template-columns:repeat(2,1fr); }
    .dash-grid{ grid-template-columns:1fr; }
    .sidebar{ position:fixed; left:-260px; z-index:50; transition:left .2s; }
    .sidebar.open{ left:0; }
    .login-illustration-side{ display:none; }
  }
</style>
</head>
<body>

  <!-- =====================================================
       HALAMAN 1: LOGIN
       Username default: admin | Password default: admin123
       ===================================================== -->
  <div id="login-page" class="<?= isset($_SESSION['admin_id']) ? 'hidden' : '' ?>">
    <div class="login-card">
      <div class="login-form-side">
        <div class="login-logo">🔒</div>
        <div class="login-title">Website Pengaduan Siswa</div>
        <div class="login-subtitle">Panel Admin</div>
        <div class="login-desc">Masuk untuk mengelola pengaduan siswa</div>

        <!-- pesan error muncul jika username/password salah -->
        <div class="login-error" id="login-error">Username atau password salah.</div>

        <form id="login-form">
          <div class="form-group">
            <label>Username</label>
            <input type="text" id="login-username" placeholder="Masukkan username" required />
          </div>
          <div class="form-group">
            <label>Password</label>
            <div class="input-wrap">
              <input type="password" id="login-password" placeholder="Masukkan password" required />
              <button type="button" class="toggle-password" onclick="togglePasswordField('login-password', this)">👁</button>
            </div>
          </div>
          <button type="submit" class="btn-primary">Masuk</button>
        </form>
        <div class="login-footer">© 2024 Website Pengaduan Siswa</div>
      </div>
      <div class="login-illustration-side">
        <!-- ilustrasi sederhana pengganti gambar orang -->
        <svg viewBox="0 0 200 200"><circle cx="100" cy="70" r="35" fill="#2F6FED" opacity=".15"/><rect x="60" y="110" width="80" height="70" rx="14" fill="#2F6FED" opacity=".25"/><circle cx="100" cy="65" r="22" fill="#1E293B" opacity=".8"/></svg>
      </div>
    </div>
  </div>

  <!-- =====================================================
       APLIKASI UTAMA (setelah login berhasil)
       ===================================================== -->
  <div id="app" class="<?= isset($_SESSION['admin_id']) ? '' : 'hidden' ?>">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-brand">
        <div class="icon-box">🔒</div>
        PENGADUAN SISWA
      </div>

      <div class="sidebar-section-label" style="padding-top:14px;">MENU</div>
      <ul class="sidebar-menu">
        <li><a href="#" data-view="dashboard" class="active">📊 Dashboard</a></li>
      </ul>

      <div class="sidebar-section-label">DATA MASTER</div>
      <ul class="sidebar-menu">
        <li><a href="#" data-view="akun-siswa">👥 Akun Siswa</a></li>
        <li><a href="#" data-view="kategori">🏷️ Kategori</a></li>
      </ul>

      <div class="sidebar-section-label">PENGADUAN</div>
      <ul class="sidebar-menu">
        <li><a href="#" data-view="daftar-pengaduan">📋 Daftar Pengaduan</a></li>
      </ul>

      <div class="sidebar-section-label">PENGATURAN</div>
      <ul class="sidebar-menu">
        <li><a href="#" data-view="profil">👤 Profil</a></li>
      </ul>

      <div class="sidebar-footer">
        <button class="btn-logout" onclick="handleLogout()">↩ Logout</button>
      </div>
    </aside>

    <!-- AREA KONTEN -->
    <div class="main-area">
      <div class="topbar">
        <div class="topbar-left">
          <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
        </div>
        <div class="topbar-right">
          <button class="bell-btn">🔔</button>
          <div class="topbar-user">
            <div class="avatar-mini" id="topbar-avatar">A</div>
            <div>
              <div class="name" id="topbar-name">Admin</div>
              <div class="role">Administrator</div>
            </div>
          </div>
        </div>
      </div>

      <div class="content">

        <!-- ============ VIEW: DASHBOARD ============ -->
        <section id="view-dashboard" class="view">
          <div class="page-title">Dashboard</div>

          <div class="stat-grid">
            <div class="stat-card stat-blue">
              <div class="stat-label">Total Pengaduan</div>
              <div class="stat-value" id="stat-total">0</div>
              <div class="stat-sub">Semua</div>
            </div>
            <div class="stat-card stat-yellow">
              <div class="stat-label">Menunggu</div>
              <div class="stat-value" id="stat-menunggu">0</div>
              <div class="stat-sub">Menunggu</div>
            </div>
            <div class="stat-card stat-green">
              <div class="stat-label">Diproses</div>
              <div class="stat-value" id="stat-diproses">0</div>
              <div class="stat-sub">Diproses</div>
            </div>
            <div class="stat-card stat-teal">
              <div class="stat-label">Selesai</div>
              <div class="stat-value" id="stat-selesai">0</div>
              <div class="stat-sub">Selesai</div>
            </div>
          </div>

          <div class="dash-grid">
            <div class="card">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong>Grafik Pengaduan</strong>
                <span style="font-size:12px; color:var(--muted);">6 Bulan Terakhir</span>
              </div>
              <div class="chart-wrap" id="chart-container"></div>
            </div>
            <div class="card">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <strong>Pengaduan Terbaru</strong>
                <a href="#" data-view="daftar-pengaduan" class="nav-link" style="font-size:12.5px; color:var(--primary);">Lihat Semua</a>
              </div>
              <div id="recent-list"></div>
            </div>
          </div>
        </section>

        <!-- ============ VIEW: AKUN SISWA (data master) ============ -->
        <section id="view-akun-siswa" class="view hidden">
          <div class="breadcrumb">Dashboard / Akun Siswa</div>
          <div class="toolbar">
            <div class="page-title" style="margin:0;">Data Siswa</div>
            <div style="display:flex; gap:10px; flex:1; justify-content:flex-end; flex-wrap:wrap;">
              <div class="search-box">🔍<input type="text" id="siswa-search" placeholder="Cari siswa..." oninput="renderSiswaTable()"></div>
              <button class="btn-add" data-view="buat-akun">+ Buat Akun Siswa</button>
            </div>
          </div>
          <div class="card">
            <table>
              <thead><tr><th>No</th><th>NIS</th><th>Nama Lengkap</th><th>Username</th><th>Kelas</th><th>Aksi</th></tr></thead>
              <tbody id="siswa-table-body"></tbody>
            </table>
            <div class="pagination" id="siswa-pagination"></div>
          </div>
        </section>

        <!-- ============ VIEW: FORM BUAT AKUN SISWA ============ -->
        <section id="view-buat-akun" class="view hidden">
          <div class="breadcrumb">Dashboard / Akun Siswa / Buat Akun</div>
          <div class="page-title">Buat Akun Siswa</div>
          <div class="card" style="max-width:520px;">
            <form id="form-buat-akun">
              <div class="form-group">
                <label>NIS (Nomor Induk Siswa)</label>
                <input type="text" id="f-nis" placeholder="Masukkan NIS" required />
              </div>
              <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" id="f-nama" placeholder="Masukkan nama lengkap" required />
              </div>
              <div class="form-group">
                <label>Username</label>
                <input type="text" id="f-username" placeholder="Masukkan username" required />
              </div>
              <div class="form-group">
                <label>Password</label>
                <input type="password" id="f-password" placeholder="Masukkan password" required />
              </div>
              <div class="form-group">
                <label>Kelas</label>
                <select id="f-kelas" required>
                  <option value="">Pilih Kelas</option>
                  <option>X IPA 1</option><option>X IPA 2</option><option>X IPS 1</option>
                  <option>XI IPA 1</option><option>XI IPS 2</option>
                  <option>XII IPA 1</option><option>XII IPS 1</option>
                </select>
              </div>
              <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" class="btn-outline" data-view="akun-siswa">Batal</button>
                <button type="submit" class="btn-add">Simpan</button>
              </div>
            </form>
          </div>
        </section>

        <!-- ============ VIEW: KATEGORI ============ -->
        <section id="view-kategori" class="view hidden">
          <div class="breadcrumb">Dashboard / Kategori</div>
          <div class="toolbar">
            <div class="page-title" style="margin:0;">Kategori Pengaduan</div>
            <div style="display:flex; gap:10px; flex:1; justify-content:flex-end; flex-wrap:wrap;">
              <div class="search-box">🔍<input type="text" id="kategori-search" placeholder="Cari kategori..." oninput="renderKategoriTable()"></div>
              <button class="btn-add" onclick="openKategoriForm()">+ Tambah Kategori</button>
            </div>
          </div>
          <div class="card">
            <table>
              <thead><tr><th>No</th><th>Nama Kategori</th><th>Deskripsi</th><th>Aksi</th></tr></thead>
              <tbody id="kategori-table-body"></tbody>
            </table>
          </div>
        </section>

        <!-- ============ VIEW: DAFTAR PENGADUAN ============ -->
        <section id="view-daftar-pengaduan" class="view hidden">
          <div class="breadcrumb">Dashboard / Daftar Pengaduan</div>
          <div class="toolbar">
            <div class="page-title" style="margin:0;">Daftar Pengaduan</div>
            <div style="display:flex; gap:10px; flex:1; justify-content:flex-end; flex-wrap:wrap;">
              <div class="search-box">🔍<input type="text" id="pengaduan-search" placeholder="Cari pengaduan..." oninput="renderPengaduanTable()"></div>
              <select class="filter-select" id="filter-status" onchange="renderPengaduanTable()">
                <option value="">Semua Status</option>
                <option value="Menunggu">Menunggu</option>
                <option value="Diproses">Diproses</option>
                <option value="Selesai">Selesai</option>
                <option value="Ditolak">Ditolak</option>
              </select>
              <select class="filter-select" id="filter-kategori" onchange="renderPengaduanTable()">
                <option value="">Semua Kategori</option>
              </select>
            </div>
          </div>
          <div class="card">
            <table>
              <thead><tr><th>No</th><th>ID Pengaduan</th><th>Pelapor</th><th>Kategori</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
              <tbody id="pengaduan-table-body"></tbody>
            </table>
            <div class="pagination" id="pengaduan-pagination"></div>
          </div>
        </section>

        <!-- ============ VIEW: DETAIL PENGADUAN ============ -->
        <section id="view-detail-pengaduan" class="view hidden">
          <div class="breadcrumb">Dashboard / Daftar Pengaduan / Detail</div>
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <div class="page-title" style="margin:0;">Detail Pengaduan</div>
            <div style="display:flex; gap:10px;">
              <button class="btn-outline" data-view="daftar-pengaduan">← Kembali</button>
              <button class="btn-add" id="btn-berikan-tanggapan">Berikan Tanggapan</button>
            </div>
          </div>
          <div class="card" id="detail-pengaduan-body"></div>
        </section>

        <!-- ============ VIEW: BERIKAN TANGGAPAN ============ -->
        <section id="view-tanggapan" class="view hidden">
          <div class="breadcrumb">Dashboard / Daftar Pengaduan / Berikan Tanggapan</div>
          <div class="page-title">Berikan Tanggapan</div>
          <div class="dash-grid" style="grid-template-columns:1fr 1fr;">
            <div class="card" id="tanggapan-info"></div>
            <div class="card">
              <form id="form-tanggapan">
                <div class="form-group">
                  <label>Tanggapan</label>
                  <textarea id="t-tanggapan" rows="5" placeholder="Tulis tanggapan Anda..."></textarea>
                </div>
                <div class="form-group">
                  <label>Ubah Status</label>
                  <select id="t-status">
                    <option value="Menunggu">Menunggu</option>
                    <option value="Diproses">Diproses</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Ditolak">Ditolak</option>
                  </select>
                </div>
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                  <button type="button" class="btn-outline" data-view="daftar-pengaduan">Batal</button>
                  <button type="submit" class="btn-add">Simpan</button>
                </div>
              </form>
            </div>
          </div>
        </section>

        <!-- ============ VIEW: PROFIL ADMIN ============ -->
        <section id="view-profil" class="view hidden">
          <div class="breadcrumb">Dashboard / Profil</div>
          <div class="page-title">Profil Admin</div>
          <div class="card" style="max-width:520px; display:flex; gap:22px; align-items:flex-start;">
            <div style="width:80px; height:80px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:30px; font-weight:700; flex-shrink:0;">A</div>
            <div style="flex:1;">
              <div class="form-group"><label>Nama Lengkap</label><input type="text" id="p-nama" value="Administrator"></div>
              <div class="form-group"><label>Username</label><input type="text" id="p-username" value="admin"></div>
              <div class="form-group"><label>Email</label><input type="text" id="p-email" value="admin@sekolah.sch.id"></div>
              <button class="btn-outline" onclick="showToast('Fitur ubah password belum tersedia di demo ini.')">Ubah Password</button>
            </div>
          </div>
        </section>

      </div>
    </div>
  </div>

  <!-- overlay modal & notifikasi ditempel lewat JS -->
  <div id="modal-root"></div>
  <div id="toast" class="toast"></div>

<script>
/* =========================================================================
   FILE: (bagian <script>) admin.php
   FUNGSI: Menghubungkan tampilan Panel Admin ke database MySQL lewat
   endpoint-endpoint di folder api/admin/*.php menggunakan fetch().

   POLA YANG DIPAKAI DI SETIAP FUNGSI RENDER:
   1. Ambil data dari server: const res = await fetch(url); const json = await res.json();
   2. Jika json.success === false -> tampilkan pesan error lewat showToast()
   3. Jika berhasil -> pakai json.data untuk mengisi HTML tabel/form
   ========================================================================= */

// URL dasar semua endpoint admin, supaya kalau folder dipindah cukup ubah di sini
const API = 'api/admin';

// variabel bantu: halaman aktif pada tabel (paginasi), sama seperti versi lama
let siswaPage = 1, pengaduanPage = 1;
const ROWS_PER_PAGE = 5; // HARUS SAMA dengan BARIS_PER_HALAMAN di file PHP

// cache data terakhir yang diambil dari server, dipakai supaya tombol
// "edit" tidak perlu fetch ulang satu-satu ke server
let siswaListCache = [];
let kategoriListCache = [];
let kategoriUntukFilter = []; // daftar kategori lengkap, dipakai di dropdown filter pengaduan
let currentPengaduanKode = null; // kode pengaduan yang sedang dibuka di halaman detail/tanggapan

/* =========================================================
   FUNGSI BANTU: memanggil API dan mengurus error secara seragam
   ========================================================= */
async function panggilApi(url, opsi = {}) {
  try {
    const res = await fetch(url, opsi);
    const json = await res.json();
    if (!json.success) {
      showToast(json.message || 'Terjadi kesalahan.');
      // kalau sesi habis (401), lempar balik ke halaman login
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

/* =========================================================
   FUNGSI LOGIN & LOGOUT
   ========================================================= */
document.getElementById('login-form').addEventListener('submit', async function(e){
  e.preventDefault(); // mencegah form reload halaman
  const username = document.getElementById('login-username').value.trim();
  const password = document.getElementById('login-password').value.trim();
  const errorBox = document.getElementById('login-error');

  const json = await panggilApi(`${API}/login.php`, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ username, password })
  });

  if (json.success) {
    errorBox.style.display = 'none';
    document.getElementById('login-page').classList.add('hidden');
    document.getElementById('app').classList.remove('hidden');
    document.getElementById('topbar-name').textContent = json.data.nama;
    document.getElementById('topbar-avatar').textContent = json.data.nama.charAt(0).toUpperCase();
    initApp();
  } else {
    errorBox.textContent = json.message || 'Username atau password salah.';
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

// menampilkan/menyembunyikan isi password saat ikon mata diklik
function togglePasswordField(id, btn){
  const field = document.getElementById(id);
  if(field.type === 'password'){ field.type = 'text'; btn.textContent = '🙈'; }
  else { field.type = 'password'; btn.textContent = '👁'; }
}

/* =========================================================
   NAVIGASI ANTAR HALAMAN (SPA sederhana tanpa reload)
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

  // panggil fungsi render yang sesuai supaya data selalu terbaru dari database
  if(viewName === 'dashboard') renderDashboard();
  if(viewName === 'akun-siswa') renderSiswaTable();
  if(viewName === 'kategori') renderKategoriTable();
  if(viewName === 'daftar-pengaduan') { muatKategoriUntukFilter(); renderPengaduanTable(); }
  if(viewName === 'buat-akun') {
    document.getElementById('form-buat-akun').reset();
    delete document.getElementById('form-buat-akun').dataset.editId;
  }

  document.getElementById('sidebar').classList.remove('open'); // tutup sidebar mobile
  window.scrollTo(0,0);
}

/* =========================================================
   RENDER DASHBOARD (ambil data dari api/admin/dashboard_stats.php)
   ========================================================= */
async function renderDashboard(){
  const json = await panggilApi(`${API}/dashboard_stats.php`);
  if(!json.success) return;
  const d = json.data;

  document.getElementById('stat-total').textContent = d.total;
  document.getElementById('stat-menunggu').textContent = d.menunggu;
  document.getElementById('stat-diproses').textContent = d.diproses;
  document.getElementById('stat-selesai').textContent = d.selesai;

  // gambar grafik garis dari data 6 bulan terakhir (hasil query GROUP BY bulan)
  const chartContainer = document.getElementById('chart-container');
  if (d.grafik.length >= 2) {
    const chartData = d.grafik.map(g => g.jumlah);
    const labels = d.grafik.map(g => g.label);
    chartContainer.innerHTML = buildLineChart(chartData, labels);
  } else {
    chartContainer.innerHTML = '<p style="color:var(--muted); font-size:13px; padding:20px 0;">Belum cukup data untuk menampilkan grafik.</p>';
  }

  // tampilkan pengaduan paling baru
  document.getElementById('recent-list').innerHTML = d.recent.map(p => `
    <div style="display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid var(--border); font-size:13px;">
      <div>
        <div style="font-weight:600;">${escapeHtml(p.judul)}</div>
        <div style="color:var(--muted); font-size:12px;">${p.tanggal}</div>
      </div>
      ${badgeHtml(p.status)}
    </div>`).join('') || '<p style="color:var(--muted); font-size:13px;">Belum ada pengaduan.</p>';
}

// fungsi membuat grafik garis pakai SVG murni (tanpa library luar) - TIDAK BERUBAH dari versi asli
function buildLineChart(data, labels){
  const w = 480, h = 190, pad = 30;
  const max = Math.max(...data) * 1.2 || 1;
  const stepX = (w - pad*2) / (data.length - 1);
  const points = data.map((val,i) => {
    const x = pad + i*stepX;
    const y = h - pad - (val/max)*(h - pad*2);
    return `${x},${y}`;
  }).join(' ');
  const labelEls = labels.map((l,i) => `<text x="${pad+i*stepX}" y="${h-6}" font-size="11" fill="#6B7280" text-anchor="middle">${l}</text>`).join('');
  const dotEls = data.map((val,i) => {
    const x = pad + i*stepX;
    const y = h - pad - (val/max)*(h - pad*2);
    return `<circle cx="${x}" cy="${y}" r="4" fill="#2F6FED"/>`;
  }).join('');
  return `<svg viewBox="0 0 ${w} ${h}" style="width:100%; height:200px;">
    <polyline points="${points}" fill="none" stroke="#2F6FED" stroke-width="2.5"/>
    ${dotEls}${labelEls}
  </svg>`;
}

// fungsi bantu untuk menghasilkan HTML badge status - TIDAK BERUBAH
function badgeHtml(status){
  const map = {Menunggu:'badge-menunggu', Diproses:'badge-diproses', Selesai:'badge-selesai', Ditolak:'badge-ditolak'};
  return `<span class="badge ${map[status]||''}">${status}</span>`;
}

// mencegah HTML/JS liar dari isi data (misal judul pengaduan) merusak tampilan (XSS sederhana)
function escapeHtml(teks){
  const div = document.createElement('div');
  div.textContent = teks ?? '';
  return div.innerHTML;
}

/* =========================================================
   RENDER TABEL: AKUN SISWA (ambil dari api/admin/siswa_get.php)
   ========================================================= */
async function renderSiswaTable(){
  const keyword = document.getElementById('siswa-search').value || '';
  const json = await panggilApi(`${API}/siswa_get.php?cari=${encodeURIComponent(keyword)}&halaman=${siswaPage}`);
  if(!json.success) return;

  siswaListCache = json.data.items; // simpan untuk keperluan tombol edit

  document.getElementById('siswa-table-body').innerHTML = siswaListCache.map((s,i) => `
    <tr>
      <td>${(siswaPage-1)*ROWS_PER_PAGE + i + 1}</td>
      <td>${escapeHtml(s.nis)}</td>
      <td>${escapeHtml(s.nama)}</td>
      <td>${escapeHtml(s.username)}</td>
      <td>${escapeHtml(s.kelas)}</td>
      <td>
        <button class="table-icon-btn" onclick="editSiswa(${s.id})">✏️</button>
        <button class="table-icon-btn" onclick="deleteSiswa(${s.id})">🗑️</button>
      </td>
    </tr>`).join('') || `<tr><td colspan="6" style="text-align:center; color:var(--muted); padding:24px;">Tidak ada data siswa.</td></tr>`;

  renderPagination('siswa-pagination', json.data.total, siswaPage, (p)=>{ siswaPage = p; renderSiswaTable(); });
}

function editSiswa(id){
  // ambil data dari cache hasil render terakhir (tidak perlu fetch baru ke server)
  const s = siswaListCache.find(x => x.id === id);
  if(!s) return;
  navigateTo('buat-akun');
  document.getElementById('f-nis').value = s.nis;
  document.getElementById('f-nama').value = s.nama;
  document.getElementById('f-username').value = s.username;
  document.getElementById('f-password').value = '';
  document.getElementById('f-password').placeholder = 'Kosongkan jika tidak ingin mengubah password';
  document.getElementById('f-kelas').value = s.kelas;
  // tandai form sedang mode edit dengan menyimpan id di dataset form
  document.getElementById('form-buat-akun').dataset.editId = id;
}

function deleteSiswa(id){
  showModal({
    icon:'🗑️', title:'Hapus Akun Siswa', text:'Apakah Anda yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan.',
    confirmLabel:'Hapus', confirmClass:'btn-danger',
    onConfirm: async function(){
      const json = await panggilApi(`${API}/siswa_hapus.php`, {
        method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id })
      });
      if(json.success){ renderSiswaTable(); showToast(json.message); }
    }
  });
}

// submit form buat/edit akun siswa
document.getElementById('form-buat-akun').addEventListener('submit', async function(e){
  e.preventDefault();
  const editId = this.dataset.editId || null;
  const data = {
    id: editId,
    nis: document.getElementById('f-nis').value.trim(),
    nama: document.getElementById('f-nama').value.trim(),
    username: document.getElementById('f-username').value.trim(),
    password: document.getElementById('f-password').value.trim(),
    kelas: document.getElementById('f-kelas').value,
  };

  const json = await panggilApi(`${API}/siswa_simpan.php`, {
    method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data)
  });

  if(json.success){
    delete this.dataset.editId;
    showToast(json.message);
    navigateTo('akun-siswa');
  }
});

/* =========================================================
   RENDER TABEL: KATEGORI (ambil dari api/admin/kategori_get.php)
   ========================================================= */
async function renderKategoriTable(){
  const keyword = document.getElementById('kategori-search').value || '';
  const json = await panggilApi(`${API}/kategori_get.php?cari=${encodeURIComponent(keyword)}`);
  if(!json.success) return;

  kategoriListCache = json.data;

  document.getElementById('kategori-table-body').innerHTML = kategoriListCache.map((k,i) => `
    <tr>
      <td>${i+1}</td>
      <td>${escapeHtml(k.nama)}</td>
      <td>${escapeHtml(k.deskripsi)}</td>
      <td>
        <button class="table-icon-btn" onclick="openKategoriForm(${k.id})">✏️</button>
        <button class="table-icon-btn" onclick="deleteKategori(${k.id})">🗑️</button>
      </td>
    </tr>`).join('') || `<tr><td colspan="4" style="text-align:center; color:var(--muted); padding:24px;">Tidak ada kategori.</td></tr>`;
}

// buka modal tambah/edit kategori. id dikosongkan (undefined) berarti mode tambah baru
function openKategoriForm(id){
  const existing = id ? kategoriListCache.find(k => k.id === id) : {nama:'', deskripsi:''};
  showModal({
    custom:true,
    title: id ? 'Edit Kategori' : 'Tambah Kategori',
    bodyHtml:`
      <div class="form-group" style="text-align:left;"><label>Nama Kategori</label><input type="text" id="modal-kategori-nama" value="${escapeHtml(existing.nama)}"></div>
      <div class="form-group" style="text-align:left;"><label>Deskripsi</label><input type="text" id="modal-kategori-deskripsi" value="${escapeHtml(existing.deskripsi || '')}"></div>`,
    confirmLabel:'Simpan', confirmClass:'btn-add',
    onConfirm: async function(){
      const nama = document.getElementById('modal-kategori-nama').value.trim();
      const deskripsi = document.getElementById('modal-kategori-deskripsi').value.trim();
      if(!nama) return;
      const json = await panggilApi(`${API}/kategori_simpan.php`, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id, nama, deskripsi })
      });
      if(json.success){ renderKategoriTable(); showToast(json.message); }
    }
  });
}

function deleteKategori(id){
  showModal({
    icon:'🗑️', title:'Hapus Kategori', text:'Apakah Anda yakin ingin menghapus kategori ini?',
    confirmLabel:'Hapus', confirmClass:'btn-danger',
    onConfirm: async function(){
      const json = await panggilApi(`${API}/kategori_hapus.php`, {
        method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id })
      });
      if(json.success){ renderKategoriTable(); showToast(json.message); }
    }
  });
}

// dipanggil saat halaman daftar-pengaduan dibuka: isi dropdown filter kategori
async function muatKategoriUntukFilter(){
  const json = await panggilApi(`${API}/kategori_get.php`);
  if(!json.success) return;
  kategoriUntukFilter = json.data;
  const filterSelect = document.getElementById('filter-kategori');
  const nilaiTerpilih = filterSelect.value; // simpan pilihan sebelumnya (kalau ada)
  filterSelect.innerHTML = '<option value="">Semua Kategori</option>' +
    kategoriUntukFilter.map(k => `<option value="${escapeHtml(k.nama)}">${escapeHtml(k.nama)}</option>`).join('');
  filterSelect.value = nilaiTerpilih;
}

/* =========================================================
   RENDER TABEL: DAFTAR PENGADUAN (ambil dari api/admin/pengaduan_get.php)
   ========================================================= */
async function renderPengaduanTable(){
  const keyword = document.getElementById('pengaduan-search').value || '';
  const statusFilter = document.getElementById('filter-status').value;
  const kategoriFilter = document.getElementById('filter-kategori').value;

  const params = new URLSearchParams({
    cari: keyword, status: statusFilter, kategori: kategoriFilter, halaman: pengaduanPage
  });
  const json = await panggilApi(`${API}/pengaduan_get.php?${params.toString()}`);
  if(!json.success) return;

  const items = json.data.items;
  document.getElementById('pengaduan-table-body').innerHTML = items.map((p,i) => `
    <tr>
      <td>${(pengaduanPage-1)*ROWS_PER_PAGE + i + 1}</td>
      <td>${p.kode}</td>
      <td>${escapeHtml(p.pelapor)}</td>
      <td>${escapeHtml(p.kategori)}</td>
      <td>${p.tanggal}</td>
      <td>${badgeHtml(p.status)}</td>
      <td><button class="table-icon-btn" onclick="openDetailPengaduan('${p.kode}')">👁️</button></td>
    </tr>`).join('') || `<tr><td colspan="7" style="text-align:center; color:var(--muted); padding:24px;">Tidak ada pengaduan.</td></tr>`;

  renderPagination('pengaduan-pagination', json.data.total, pengaduanPage, (p)=>{ pengaduanPage = p; renderPengaduanTable(); });
}

// fungsi umum untuk menggambar tombol paginasi (dipakai tabel siswa & pengaduan) - TIDAK BERUBAH
function renderPagination(containerId, totalRows, currentPage, onChange){
  const totalPages = Math.max(1, Math.ceil(totalRows / ROWS_PER_PAGE));
  let html = `<div class="pagination-info">Menampilkan ${Math.min(ROWS_PER_PAGE,totalRows)} dari ${totalRows} data</div>`;
  for(let i=1;i<=totalPages;i++){
    html += `<button class="${i===currentPage?'active':''}" onclick="(${onChange.toString()})(${i})">${i}</button>`;
  }
  document.getElementById(containerId).innerHTML = html;
}

/* =========================================================
   DETAIL PENGADUAN & BERI TANGGAPAN
   ========================================================= */
async function openDetailPengaduan(kode){
  currentPengaduanKode = kode;
  const json = await panggilApi(`${API}/pengaduan_detail.php?kode=${encodeURIComponent(kode)}`);
  if(!json.success) return;
  const p = json.data;
  navigateTo('detail-pengaduan');

  document.getElementById('detail-pengaduan-body').innerHTML = `
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
      <div>
        <div style="font-size:12px; color:var(--muted);">ID Pengaduan</div>
        <div style="font-weight:600; margin-bottom:14px;">${p.kode}</div>
        <div style="font-size:12px; color:var(--muted);">Pelapor</div>
        <div style="font-weight:600; margin-bottom:14px;">${escapeHtml(p.pelapor)}</div>
        <div style="font-size:12px; color:var(--muted);">Kategori</div>
        <div style="font-weight:600; margin-bottom:14px;">${escapeHtml(p.kategori)}</div>
        <div style="font-size:12px; color:var(--muted);">Tanggal</div>
        <div style="font-weight:600;">${p.tanggal}</div>
      </div>
      <div>
        <div style="font-size:12px; color:var(--muted);">Judul Pengaduan</div>
        <div style="font-weight:700; font-size:16px; margin-bottom:10px;">${escapeHtml(p.judul)}</div>
        <div style="font-size:12px; color:var(--muted);">Isi Pengaduan</div>
        <p style="margin:6px 0 14px; line-height:1.6;">${escapeHtml(p.deskripsi)}</p>
        ${p.lampiran ? `<div style="font-size:12px; color:var(--muted);">Lampiran</div><div style="font-weight:600; margin-bottom:10px;"><a href="uploads/${encodeURIComponent(p.lampiran)}" target="_blank">📎 ${escapeHtml(p.lampiran)}</a></div>` : ''}
        <div style="font-size:12px; color:var(--muted); margin-bottom:6px;">Status</div>
        ${badgeHtml(p.status)}
      </div>
    </div>`;

  // simpan data pengaduan sekarang supaya halaman tanggapan tidak perlu fetch ulang
  window._pengaduanAktif = p;
}

// tombol "Berikan Tanggapan" di halaman detail membawa ke form tanggapan
document.getElementById('btn-berikan-tanggapan').addEventListener('click', function(){
  const p = window._pengaduanAktif;
  if(!p) return;
  navigateTo('tanggapan');
  document.getElementById('tanggapan-info').innerHTML = `
    <div style="font-size:12px; color:var(--muted);">ID Pengaduan</div>
    <div style="font-weight:600; margin-bottom:12px;">${p.kode}</div>
    <div style="font-size:12px; color:var(--muted);">Pelapor</div>
    <div style="font-weight:600; margin-bottom:12px;">${escapeHtml(p.pelapor)}</div>
    <div style="font-size:12px; color:var(--muted);">Kategori</div>
    <div style="font-weight:600; margin-bottom:12px;">${escapeHtml(p.kategori)}</div>
    <div style="font-size:12px; color:var(--muted);">Judul</div>
    <div style="font-weight:600;">${escapeHtml(p.judul)}</div>`;
  // isi tanggapan terakhir (kalau ada) sebagai draft, dan status sekarang
  const percakapan = p.percakapan || [];
  document.getElementById('t-tanggapan').value = '';
  document.getElementById('t-status').value = p.status;
});

// submit form tanggapan: simpan tanggapan + ubah status pengaduan lewat API
document.getElementById('form-tanggapan').addEventListener('submit', async function(e){
  e.preventDefault();
  const data = {
    kode: currentPengaduanKode,
    tanggapan: document.getElementById('t-tanggapan').value.trim(),
    status: document.getElementById('t-status').value,
  };
  const json = await panggilApi(`${API}/tanggapan_simpan.php`, {
    method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data)
  });
  if(json.success){
    showToast(json.message);
    navigateTo('daftar-pengaduan');
  }
});

/* =========================================================
   MODAL & TOAST (notifikasi kecil) - TIDAK BERUBAH
   ========================================================= */
function showModal({icon, title, text, confirmLabel, confirmClass, onConfirm, custom, bodyHtml}){
  const root = document.getElementById('modal-root');
  root.innerHTML = `
    <div class="modal-overlay" id="modal-overlay">
      <div class="modal-box">
        ${custom ? '' : `<div class="modal-icon">${icon}</div>`}
        <div class="modal-title">${title}</div>
        ${custom ? bodyHtml : `<div class="modal-text">${text}</div>`}
        <div class="modal-actions" style="${custom?'margin-top:16px;':''}">
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
  setTimeout(()=> toast.classList.remove('show'), 2500); // hilang otomatis setelah 2.5 detik
}

// Catatan: pencarian (siswa-search, kategori-search, pengaduan-search) dan
// filter (filter-status, filter-kategori) sudah dipasang lewat atribut
// oninput/onchange langsung di HTML (lihat admin.php bagian <section>),
// jadi tidak perlu addEventListener tambahan di sini.

/* =========================================================
   INISIALISASI APLIKASI SETELAH LOGIN
   ========================================================= */
function initApp(){
  navigateTo('dashboard'); // tampilkan dashboard sebagai halaman pertama
}

// Jika saat halaman dimuat session admin MASIH AKTIF (server sudah menampilkan
// #app, bukan #login-page), langsung jalankan initApp() supaya data terisi.
document.addEventListener('DOMContentLoaded', function(){
  if(!document.getElementById('app').classList.contains('hidden')){
    initApp();
  }
});
</script>
</body>
</html>
