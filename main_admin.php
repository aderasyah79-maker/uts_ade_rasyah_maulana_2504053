<?php
session_start();
include('koneksi.php');

// Paksa mysqli melempar exception agar error SQL jelas dan bisa ditangkap
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ===== Pengamanan akses: hanya admin =====
// Ganti 'login.php' jika nama halaman loginmu berbeda (mis. index.php)
if (!isset($_SESSION['nama']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$nama = e($_SESSION['nama']);
$page = (($_GET['page'] ?? '') === 'petugas') ? 'petugas' : 'dashboard';

// Token CSRF
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); }

function flash($tipe, $pesan) { $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan]; }

// Username harus unik di akun_petugas dan akun_admin
function usernameDipakai($db, $username, $kecualiIdPetugas = 0) {
    $st = $db->prepare("SELECT username FROM akun_petugas WHERE username = ? AND id_petugas <> ?
                        UNION
                        SELECT username FROM akun_admin WHERE username = ?");
    $st->bind_param('sis', $username, $kecualiIdPetugas, $username);
    $st->execute();
    return $st->get_result()->num_rows > 0;
}

// ===== Proses CRUD petugas =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        flash('danger', 'Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
        header('Location: main_admin.php?page=petugas'); exit;
    }

    $aksi     = $_POST['aksi'] ?? '';
    $id       = (int)($_POST['id_petugas'] ?? 0);
    $namaIn   = trim($_POST['nama_petugas'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');
    $jk       = $_POST['jenis_kelamin'] ?? '';
    $telp     = trim($_POST['no_telp'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        if ($aksi === 'tambah' || $aksi === 'ubah') {
            if ($namaIn === '' || $alamat === '' || $username === '') {
                flash('danger', 'Nama, alamat, dan username wajib diisi.');
            } elseif ($jk !== '0' && $jk !== '1') {
                flash('danger', 'Jenis kelamin tidak valid.');
            } elseif (!preg_match('/^[0-9]{9,15}$/', $telp)) {
                flash('danger', 'No. telepon harus 9-15 digit angka.');
            } elseif (!preg_match('/^[A-Za-z0-9_]{3,10}$/', $username)) {
                flash('danger', 'Username 3-10 karakter: huruf, angka, dan garis bawah saja.');
            } elseif (usernameDipakai($koneksi, $username, $aksi === 'ubah' ? $id : 0)) {
                flash('danger', 'Username sudah dipakai, pilih yang lain.');
            } elseif ($aksi === 'tambah') {
                if (strlen($password) < 6) {
                    flash('danger', 'Password minimal 6 karakter.');
                } else {
                    $jkInt = (int)$jk;
                    $hash  = md5($password);
                    $koneksi->begin_transaction();
                    // id_petugas pada skema tidak AUTO_INCREMENT: ambil nomor berikutnya (mulai 2001)
                    $idBaru = (int)$koneksi->query("SELECT COALESCE(MAX(id_petugas), 2000) + 1 FROM petugas")->fetch_row()[0];

                    $st = $koneksi->prepare("INSERT INTO petugas (id_petugas, nama_petugas, alamat, jenis_kelamin, no_telp) VALUES (?, ?, ?, ?, ?)");
                    $st->bind_param('issis', $idBaru, $namaIn, $alamat, $jkInt, $telp);
                    $st->execute();

                    $st = $koneksi->prepare("INSERT INTO akun_petugas (username, id_petugas, password) VALUES (?, ?, ?)");
                    $st->bind_param('sis', $username, $idBaru, $hash);
                    $st->execute();

                    $koneksi->commit();
                    flash('success', 'Petugas berhasil ditambahkan.');
                }
            } else { // ubah
                if ($password !== '' && strlen($password) < 6) {
                    flash('danger', 'Password baru minimal 6 karakter (kosongkan jika tidak diganti).');
                } else {
                    $jkInt = (int)$jk;
                    $koneksi->begin_transaction();

                    $st = $koneksi->prepare("UPDATE petugas SET nama_petugas=?, alamat=?, jenis_kelamin=?, no_telp=? WHERE id_petugas=?");
                    $st->bind_param('ssisi', $namaIn, $alamat, $jkInt, $telp, $id);
                    $st->execute();

                    if ($password !== '') {
                        $hash = md5($password);
                        $st = $koneksi->prepare("UPDATE akun_petugas SET username=?, password=? WHERE id_petugas=?");
                        $st->bind_param('ssi', $username, $hash, $id);
                    } else {
                        $st = $koneksi->prepare("UPDATE akun_petugas SET username=? WHERE id_petugas=?");
                        $st->bind_param('si', $username, $id);
                    }
                    $st->execute();

                    $koneksi->commit();
                    flash('success', 'Data petugas berhasil diperbarui.');
                }
            }
        } elseif ($aksi === 'hapus') {
            $koneksi->begin_transaction();
            // akun dihapus dulu karena akun_petugas mereferensikan petugas
            $st = $koneksi->prepare("DELETE FROM akun_petugas WHERE id_petugas=?");
            $st->bind_param('i', $id);
            $st->execute();

            $st = $koneksi->prepare("DELETE FROM petugas WHERE id_petugas=?");
            $st->bind_param('i', $id);
            $st->execute();

            $koneksi->commit();
            flash('success', 'Petugas berhasil dihapus.');
        }
    } catch (mysqli_sql_exception $ex) {
        $koneksi->rollback();
        if ($ex->getCode() == 1451) {
            flash('danger', 'Petugas tidak bisa dihapus karena sudah mencatat data pengukuran.');
        } elseif ($ex->getCode() == 1062) {
            flash('danger', 'Username sudah dipakai, pilih yang lain.');
        } elseif ($ex->getCode() == 1264) {
            flash('danger', 'No. telepon terlalu besar untuk kolom no_telp (INT). Ubah kolom menjadi VARCHAR(15).');
        } else {
            flash('danger', 'Terjadi kesalahan pada database.');
        }
    }
    header('Location: main_admin.php?page=petugas'); exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

function statusGelombang($tinggi) {
    if ($tinggi >= 2.0) return ['BAHAYA', 'danger'];
    if ($tinggi >= 1.2) return ['WASPADA', 'warning'];
    return ['NORMAL', 'success'];
}

// ===== Data per halaman =====
// Query dibuat tahan error: kalau tabel/kolom bermasalah atau data kosong,
// halaman tetap tampil (angka 0 / tabel kosong) dan pesan MySQL muncul sebagai peringatan.
$peringatanDb = [];

function hitung($db, $sql) {
    global $peringatanDb;
    try {
        return (int)$db->query($sql)->fetch_row()[0];
    } catch (mysqli_sql_exception $ex) {
        $peringatanDb[] = $ex->getMessage();
        return 0;
    }
}

function ambilSemua($db, $sql) {
    global $peringatanDb;
    try {
        return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    } catch (mysqli_sql_exception $ex) {
        $peringatanDb[] = $ex->getMessage();
        return [];
    }
}

if ($page === 'dashboard') {
    $jmlPetugas    = hitung($koneksi, "SELECT COUNT(*) FROM petugas");
    $jmlHariIni    = hitung($koneksi, "SELECT COUNT(*) FROM pengukuran WHERE DATE(waktu)=CURDATE()");
    $jmlPeringatan = hitung($koneksi, "SELECT COUNT(*) FROM pengukuran WHERE DATE(waktu)=CURDATE() AND tinggi>=1.2");

    // Kolom status di skema asli tertulis 'stastus' (typo); coba 'status' dulu, lalu 'stastus'
    $kolomStatus = 'status';
    $cek = $koneksi->query("SHOW COLUMNS FROM stasiun LIKE 'stastus'");
    if ($cek && $cek->num_rows > 0) { $kolomStatus = 'stastus'; }
    $jmlStasiun = hitung($koneksi, "SELECT COUNT(*) FROM stasiun WHERE $kolomStatus='aktif'");

    $terbaru = ambilSemua($koneksi, "SELECT m.waktu, m.tinggi, m.periode, m.suhu, s.nama_stasiun, p.nama_petugas AS petugas
                                     FROM pengukuran m
                                     JOIN stasiun s ON m.id_stasiun = s.id_stasiun
                                     JOIN petugas p ON m.id_petugas = p.id_petugas
                                     ORDER BY m.waktu DESC LIMIT 8");
    $grafik = array_reverse($terbaru);
    $labelGrafik = array_map(function ($r) { return date('d/m H:i', strtotime($r['waktu'])); }, $grafik);
    $nilaiGrafik = array_map(function ($r) { return (float)$r['tinggi']; }, $grafik);
} else {
    $daftar = ambilSemua($koneksi, "SELECT p.id_petugas, p.nama_petugas, p.alamat, p.jenis_kelamin, p.no_telp, a.username
                                    FROM petugas p
                                    LEFT JOIN akun_petugas a ON a.id_petugas = p.id_petugas
                                    ORDER BY p.nama_petugas");
}
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <title>Dashboard Admin - nami data</title>

    <style>
      .glass-box {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.65);
        border-radius: 22px;
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22), 0 0 0 1px rgba(255, 255, 255, 0.35) inset;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }
      .glass-box.hover-up:hover { transform: translateY(-4px); box-shadow: 0 26px 50px rgba(0, 0, 0, 0.3), 0 0 24px rgba(53, 162, 159, 0.25); }
      .stat-chip {
        width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #0b666a, #071952); color: #fff; font-size: 1.25rem;
        box-shadow: 0 8px 16px rgba(11, 102, 106, 0.35);
      }
      .judul-box { color: #071952; font-weight: 700; }
      .tabel-modern { border-collapse: separate; border-spacing: 0 8px; }
      .tabel-modern tbody tr { background: rgba(255, 255, 255, 0.75); }
      .tabel-modern tbody td:first-child { border-radius: 12px 0 0 12px; }
      .tabel-modern tbody td:last-child { border-radius: 0 12px 12px 0; }
      .tabel-modern tbody tr:hover { background: #ffffff; }
      .btn-keluar, .nav-pill-x {
        background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.28);
        border-radius: 12px; color: #fff; padding: 0.5rem 1rem; font-weight: 600; text-decoration: none; transition: all 0.25s ease;
      }
      .btn-keluar:hover, .nav-pill-x:hover { background: rgba(255, 255, 255, 0.26); color: #fff; }
      .nav-pill-x.aktif { background: #ffffff; color: #071952; border-color: #ffffff; }
      .btn-tema { background: linear-gradient(135deg, #0b666a, #071952); border: none; border-radius: 12px; color: #fff; font-weight: 600; padding: 0.55rem 1.1rem; box-shadow: 0 10px 22px rgba(11, 102, 106, 0.3); }
      .btn-tema:hover { color: #fff; background: linear-gradient(135deg, #35a29f, #0b666a); }
      .field { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 0.7rem 1rem; }
      .field:focus { border-color: #35a29f; background: #fff; box-shadow: 0 0 0 4px rgba(53, 162, 159, 0.15); }
      .modal-content { border: none; border-radius: 22px; overflow: hidden; }
      .modal-header-tema { background: linear-gradient(135deg, #071952, #0b666a); color: #fff; }
    </style>
  </head>

  <body style="background: radial-gradient(circle at 10% 20%, rgba(7, 25, 82, 0.9) 0%, rgba(11, 102, 106, 0.85) 90%), url('https://images.unsplash.com/photo-1518837695005-2083093ee35b?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: flex; flex-direction: column;">

    <!-- Navbar full width -->
    <nav class="text-white"
         style="position: sticky; top: 0; z-index: 1030; width: 100%; background: linear-gradient(135deg, #071952, #0b666a); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35); border-bottom: 1px solid rgba(255, 255, 255, 0.2);">
      <div class="container-fluid px-3 px-md-5 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
          <div id="iconContainer"
               style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25);">
            <i class="bi bi-water text-info" style="font-size: 1.7rem;"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0" style="letter-spacing: 0.05em;">Nami Data</h4>
            <p class="small text-white-50 mb-0 font-monospace">Portal Pemantauan Tinggi Gelombang</p>
          </div>
        </div>

        <div class="d-flex gap-2">
          <a href="main_admin.php" class="nav-pill-x <?= $page === 'dashboard' ? 'aktif' : '' ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
          <a href="main_admin.php?page=petugas" class="nav-pill-x <?= $page === 'petugas' ? 'aktif' : '' ?>"><i class="bi bi-people-fill me-1"></i>Manajemen Petugas</a>
        </div>

        <div class="d-flex align-items-center gap-3">
          <div class="text-end">
            <div class="fw-semibold"><i class="bi bi-person-badge-fill me-1"></i><?= $nama ?></div>
            <small class="text-white-50">Admin</small>
          </div>
          <a href="logout.php" class="btn-keluar"><i class="bi bi-box-arrow-right me-1"></i>Keluar</a>
        </div>
      </div>
    </nav>

    <div class="container py-4 py-md-5 flex-grow-1">

      <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['tipe']) ?> alert-dismissible fade show" style="border-radius: 14px;" role="alert">
          <?= e($flash['pesan']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php foreach (array_unique($peringatanDb) as $w): ?>
        <div class="alert alert-warning small" style="border-radius: 14px;" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>Sebagian data tidak bisa dimuat: <?= e($w) ?>
        </div>
      <?php endforeach; ?>

      <?php if ($page === 'dashboard'): ?>
      <!-- ================= HALAMAN DASHBOARD ================= -->
      <div class="mb-4 text-white">
        <h4 class="fw-bold mb-1">Selamat Datang, <?= $nama ?></h4>
        <p class="small mb-0" style="color: rgba(255,255,255,0.75);">Kelola dan pantau seluruh sistem monitoring gelombang laut.</p>
      </div>

      <div class="row g-3 mb-3">
        <?php
        $kartu = [
            ['bi-people-fill', 'Petugas', $jmlPetugas, 'petugas terdaftar'],
            ['bi-geo-alt-fill', 'Stasiun Aktif', $jmlStasiun, 'stasiun pengamatan'],
            ['bi-exclamation-triangle-fill', 'Peringatan', $jmlPeringatan, 'data waspada hari ini'],
            ['bi-database-fill', 'Data Hari Ini', $jmlHariIni, 'rekaman tercatat'],
        ];
        foreach ($kartu as $k): ?>
        <div class="col-6 col-lg-3">
          <div class="glass-box hover-up h-100 p-3 p-md-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="stat-chip"><i class="bi <?= $k[0] ?>"></i></div>
              <div class="small text-secondary fw-semibold"><?= $k[1] ?></div>
            </div>
            <div class="fs-2 fw-bold" style="color:#071952; line-height: 1;"><?= (int)$k[2] ?></div>
            <small class="text-muted"><?= $k[3] ?></small>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="glass-box p-3 p-md-4 mb-3">
        <div class="judul-box mb-3"><i class="bi bi-graph-up-arrow me-2" style="color:#0b666a;"></i>Riwayat Tinggi Gelombang</div>
        <div style="position: relative; height: 260px;"><canvas id="grafikGelombang"></canvas></div>
      </div>

      <div class="glass-box p-3 p-md-4 mb-4">
        <div class="judul-box mb-2"><i class="bi bi-list-ul me-2" style="color:#0b666a;"></i>Data Terbaru</div>
        <div class="table-responsive">
          <table class="table table-borderless align-middle mb-0 small tabel-modern">
            <thead>
              <tr class="text-secondary">
                <th class="ps-3">Waktu</th><th>Stasiun</th><th>Tinggi (m)</th><th>Periode (s)</th><th>Suhu (&deg;C)</th><th>Petugas</th><th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$terbaru): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data pengukuran.</td></tr>
              <?php endif; ?>
              <?php foreach ($terbaru as $d): [$teks, $warna] = statusGelombang($d['tinggi']); ?>
              <tr>
                <td class="ps-3 fw-semibold"><?= e(date('d/m H:i:s', strtotime($d['waktu']))) ?></td>
                <td><?= e($d['nama_stasiun']) ?></td>
                <td><?= number_format($d['tinggi'], 1, ',', '') ?></td>
                <td><?= number_format($d['periode'], 1, ',', '') ?></td>
                <td><?= number_format($d['suhu'], 1, ',', '') ?></td>
                <td><?= e($d['petugas']) ?></td>
                <td><span class="badge rounded-pill bg-<?= $warna ?> <?= $warna === 'warning' ? 'text-dark' : '' ?> px-3 py-2"><?= $teks ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php else: ?>
      <!-- ================= HALAMAN MANAJEMEN PETUGAS ================= -->
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="text-white">
          <h4 class="fw-bold mb-1">Manajemen Petugas</h4>
          <p class="small mb-0" style="color: rgba(255,255,255,0.75);">Tambah, ubah, dan hapus data serta akun petugas.</p>
        </div>
        <button class="btn btn-tema" data-bs-toggle="modal" data-bs-target="#modalTambah">
          <i class="bi bi-person-plus-fill me-1"></i>Tambah Petugas
        </button>
      </div>

      <div class="glass-box p-3 p-md-4 mb-4">
        <div class="table-responsive">
          <table class="table table-borderless align-middle mb-0 small tabel-modern">
            <thead>
              <tr class="text-secondary">
                <th class="ps-3">No</th><th>Nama</th><th>Username</th><th>Jenis Kelamin</th><th>No. Telp</th><th>Alamat</th><th class="text-end pe-3">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$daftar): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data petugas.</td></tr>
              <?php endif; ?>
              <?php foreach ($daftar as $i => $p): ?>
              <tr>
                <td class="ps-3"><?= $i + 1 ?></td>
                <td class="fw-semibold"><?= e($p['nama_petugas']) ?></td>
                <td><i class="bi bi-person-badge-fill me-1" style="color:#0b666a;"></i><?= e($p['username']) ?></td>
                <td><?= (int)$p['jenis_kelamin'] === 1 ? 'Laki-laki' : 'Perempuan' ?></td>
                <td><?= e($p['no_telp']) ?></td>
                <td><?= e($p['alamat']) ?></td>
                <td class="text-end pe-3">
                  <button class="btn btn-sm btn-outline-primary btn-edit" style="border-radius: 10px;"
                          data-id="<?= (int)$p['id_petugas'] ?>" data-nama="<?= e($p['nama_petugas']) ?>"
                          data-username="<?= e($p['username']) ?>" data-jk="<?= (int)$p['jenis_kelamin'] ?>"
                          data-telp="<?= e($p['no_telp']) ?>" data-alamat="<?= e($p['alamat']) ?>"
                          data-bs-toggle="modal" data-bs-target="#modalEdit"><i class="bi bi-pencil-square"></i> Ubah</button>
                  <button class="btn btn-sm btn-outline-danger btn-hapus" style="border-radius: 10px;"
                          data-id="<?= (int)$p['id_petugas'] ?>" data-nama="<?= e($p['nama_petugas']) ?>"
                          data-bs-toggle="modal" data-bs-target="#modalHapus"><i class="bi bi-trash3-fill"></i> Hapus</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal tambah -->
      <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
          <form method="post">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <input type="hidden" name="aksi" value="tambah">
            <div class="modal-header modal-header-tema">
              <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Tambah Petugas</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
              <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
              <input type="text" name="nama_petugas" class="form-control field mb-3" maxlength="100" required>
              <div class="row g-3 mb-3">
                <div class="col-6">
                  <label class="form-label small fw-semibold text-secondary">Jenis Kelamin</label>
                  <select name="jenis_kelamin" class="form-select field" required>
                    <option value="1">Laki-laki</option>
                    <option value="0">Perempuan</option>
                  </select>
                </div>
                <div class="col-6">
                  <label class="form-label small fw-semibold text-secondary">No. Telepon</label>
                  <input type="text" name="no_telp" class="form-control field" pattern="[0-9]{9,15}" title="9-15 digit angka" inputmode="numeric" required>
                </div>
              </div>
              <label class="form-label small fw-semibold text-secondary">Alamat</label>
              <textarea name="alamat" class="form-control field mb-3" rows="2" required></textarea>
              <label class="form-label small fw-semibold text-secondary">Username</label>
              <input type="text" name="username" class="form-control field mb-3" pattern="[A-Za-z0-9_]{3,10}" maxlength="10" title="3-10 karakter: huruf, angka, garis bawah" required>
              <label class="form-label small fw-semibold text-secondary">Password</label>
              <input type="password" name="password" class="form-control field" minlength="6" required>
              <div class="form-text">Minimal 6 karakter.</div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
              <button type="button" class="btn btn-light" style="border-radius: 12px;" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-tema">Simpan</button>
            </div>
          </form>
        </div></div>
      </div>

      <!-- Modal ubah -->
      <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
          <form method="post">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <input type="hidden" name="aksi" value="ubah">
            <input type="hidden" name="id_petugas" id="editId">
            <div class="modal-header modal-header-tema">
              <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Ubah Petugas</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
              <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
              <input type="text" name="nama_petugas" id="editNama" class="form-control field mb-3" maxlength="100" required>
              <div class="row g-3 mb-3">
                <div class="col-6">
                  <label class="form-label small fw-semibold text-secondary">Jenis Kelamin</label>
                  <select name="jenis_kelamin" id="editJk" class="form-select field" required>
                    <option value="1">Laki-laki</option>
                    <option value="0">Perempuan</option>
                  </select>
                </div>
                <div class="col-6">
                  <label class="form-label small fw-semibold text-secondary">No. Telepon</label>
                  <input type="text" name="no_telp" id="editTelp" class="form-control field" pattern="[0-9]{9,15}" title="9-15 digit angka" inputmode="numeric" required>
                </div>
              </div>
              <label class="form-label small fw-semibold text-secondary">Alamat</label>
              <textarea name="alamat" id="editAlamat" class="form-control field mb-3" rows="2" required></textarea>
              <label class="form-label small fw-semibold text-secondary">Username</label>
              <input type="text" name="username" id="editUsername" class="form-control field mb-3" pattern="[A-Za-z0-9_]{3,10}" maxlength="10" required>
              <label class="form-label small fw-semibold text-secondary">Password Baru</label>
              <input type="password" name="password" class="form-control field" minlength="6" placeholder="Kosongkan jika tidak diganti">
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
              <button type="button" class="btn btn-light" style="border-radius: 12px;" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-tema">Perbarui</button>
            </div>
          </form>
        </div></div>
      </div>

      <!-- Modal hapus -->
      <div class="modal fade" id="modalHapus" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
          <form method="post">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="id_petugas" id="hapusId">
            <div class="modal-body p-4 text-center">
              <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
              <h5 class="fw-bold mt-2">Hapus petugas?</h5>
              <p class="text-muted mb-0">Data dan akun <strong id="hapusNama"></strong> akan dihapus permanen.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
              <button type="button" class="btn btn-light" style="border-radius: 12px;" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-danger" style="border-radius: 12px;">Ya, Hapus</button>
            </div>
          </form>
        </div></div>
      </div>
      <?php endif; ?>

    </div>

    <!-- Footer full width -->
    <footer class="text-center text-white py-3"
            style="width: 100%; background: linear-gradient(135deg, #071952, #0b666a); border-top: 1px solid rgba(255, 255, 255, 0.2);">
      <small>Created By Ade Ganteng</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <?php if ($page === 'dashboard'): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
      const ctx = document.getElementById('grafikGelombang').getContext('2d');
      const grad = ctx.createLinearGradient(0, 0, 0, 260);
      grad.addColorStop(0, 'rgba(53, 162, 159, 0.45)');
      grad.addColorStop(1, 'rgba(53, 162, 159, 0.02)');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: <?= json_encode($labelGrafik) ?>,
          datasets: [{
            label: 'Tinggi Gelombang (m)',
            data: <?= json_encode($nilaiGrafik) ?>,
            borderColor: '#0b666a', backgroundColor: grad, borderWidth: 3, fill: true, tension: 0.4,
            pointRadius: 5, pointBackgroundColor: '#ffffff', pointBorderColor: '#0b666a', pointBorderWidth: 3
          }]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true, title: { display: true, text: 'Tinggi (m)' }, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } }
        }
      });
    </script>
    <?php else: ?>
    <script>
      // Isi form modal ubah dan hapus dari atribut data tombol
      document.querySelectorAll('.btn-edit').forEach(function (b) {
        b.addEventListener('click', function () {
          document.getElementById('editId').value = b.dataset.id;
          document.getElementById('editNama').value = b.dataset.nama;
          document.getElementById('editUsername').value = b.dataset.username;
          document.getElementById('editJk').value = b.dataset.jk;
          document.getElementById('editTelp').value = b.dataset.telp;
          document.getElementById('editAlamat').value = b.dataset.alamat;
        });
      });
      document.querySelectorAll('.btn-hapus').forEach(function (b) {
        b.addEventListener('click', function () {
          document.getElementById('hapusId').value = b.dataset.id;
          document.getElementById('hapusNama').textContent = b.dataset.nama;
        });
      });
    </script>
    <?php endif; ?>

    <script>
      // Animasi ikon melayang
      document.getElementById('iconContainer').animate(
        [{ transform: 'translateY(0)' }, { transform: 'translateY(-6px)' }, { transform: 'translateY(0)' }],
        { duration: 3000, iterations: Infinity, easing: 'ease-in-out' }
      );
    </script>
  </body>
</html>
