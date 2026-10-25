<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header('Location: login.php');
    exit;
}

include('koneksi.php');

$nama_admin = $_SESSION['nama'];

// Data tabel pengukuran terbaru
$hasil = mysqli_query(
    $koneksi,
    "SELECT m.*, s.nama_stasiun, p.nama_petugas
     FROM pengukuran m
     INNER JOIN stasiun s ON m.id_stasiun = s.id_stasiun
     INNER JOIN petugas p ON m.id_petugas = p.id_petugas
     ORDER BY m.waktu DESC"
);

if (!$hasil) {
    die("Query tabel gagal: " . mysqli_error($koneksi));
}

// Data grafik (10 pengukuran terakhir)
$query_grafik = mysqli_query(
    $koneksi,
    "SELECT waktu, tinggi
     FROM pengukuran
     ORDER BY waktu DESC
     LIMIT 10"
);

if (!$query_grafik) {
    die("Query grafik gagal: " . mysqli_error($koneksi));
}

$label_grafik = [];
$data_grafik = [];

while ($row = mysqli_fetch_assoc($query_grafik)) {
    $label_grafik[] = date('d/m H:i', strtotime($row['waktu']));
    $data_grafik[] = (float) $row['tinggi'];
}

$label_grafik = array_reverse($label_grafik);
$data_grafik = array_reverse($data_grafik);

// Fungsi bantu untuk mengambil satu angka total
function ambil_total($koneksi, $sql, $pesan)
{
    $q = mysqli_query($koneksi, $sql);
    if (!$q) {
        die($pesan . ": " . mysqli_error($koneksi));
    }
    return (int) mysqli_fetch_assoc($q)['total'];
}

// Total petugas
$total_petugas = ambil_total(
    $koneksi,
    "SELECT COUNT(*) AS total FROM petugas",
    "Query petugas gagal"
);

// Total stasiun
$total_stasiun = ambil_total(
    $koneksi,
    "SELECT COUNT(*) AS total FROM stasiun",
    "Query stasiun gagal"
);

// Jumlah seluruh rekaman pengukuran
$total_data = ambil_total(
    $koneksi,
    "SELECT COUNT(*) AS total FROM pengukuran",
    "Query total data gagal"
);

// Jumlah pengukuran berstatus bahaya (tinggi >= 2 m)
$total_bahaya = ambil_total(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM pengukuran
     WHERE tinggi >= 2",
    "Query data bahaya gagal"
);
?>

<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Admin - Nami Data</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  .glass-box{background:rgba(255,255,255,.8);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.65);border-radius:22px;box-shadow:0 18px 40px rgba(0,0,0,.22),0 0 0 1px rgba(255,255,255,.35) inset;transition:transform .3s ease,box-shadow .3s ease}
  .glass-box.hover-up:hover{transform:translateY(-4px);box-shadow:0 26px 50px rgba(0,0,0,.3),0 0 24px rgba(53,162,159,.25)}
  .stat-chip{width:46px;height:46px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0b666a,#071952);color:#fff;font-size:1.25rem;box-shadow:0 8px 16px rgba(11,102,106,.35)}
  .judul-box{color:#071952;font-weight:700}
  .tabel-modern{border-collapse:separate;border-spacing:0 8px}
  .tabel-modern tbody tr{background:rgba(255,255,255,.75)}
  .tabel-modern tbody td:first-child{border-radius:12px 0 0 12px}
  .tabel-modern tbody td:last-child{border-radius:0 12px 12px 0}
  .tabel-modern tbody tr:hover{background:#fff}
  .btn-keluar{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.28);border-radius:12px;color:#fff;padding:.5rem 1rem;font-weight:600;text-decoration:none;transition:all .25s ease;cursor:pointer}
  .btn-keluar:hover{background:rgba(255,255,255,.26);color:#fff}
  .btn-tambah{background:linear-gradient(135deg,#0b666a,#071952);border-radius:12px;color:#fff;padding:.5rem 1.1rem;font-weight:600;text-decoration:none;box-shadow:0 10px 22px rgba(11,102,106,.3);transition:all .25s ease}
  .btn-tambah:hover{background:linear-gradient(135deg,#35a29f,#0b666a);color:#fff}
</style>
</head>
<body style="background:radial-gradient(circle at 10% 20%,rgba(7,25,82,.9) 0%,rgba(11,102,106,.85) 90%),url('https://images.unsplash.com/photo-1518837695005-2083093ee35b?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed;font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;display:flex;flex-direction:column;">

<!-- Navbar -->
<nav class="text-white" style="position:sticky;top:0;z-index:1030;width:100%;background:linear-gradient(135deg,#071952,#0b666a);box-shadow:0 10px 30px rgba(0,0,0,.35);border-bottom:1px solid rgba(255,255,255,.2);">
  <div class="container-fluid px-3 px-md-5 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-3">
      <div id="iconContainer" style="width:56px;height:56px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);border-radius:16px;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 20px rgba(0,0,0,.25);">
        <i class="bi bi-water text-info" style="font-size:1.7rem;"></i>
      </div>
      <div>
        <h4 class="fw-bold mb-0" style="letter-spacing:.05em;">Nami Data</h4>
        <p class="small text-white-50 mb-0 font-monospace">Portal Pemantauan Tinggi Gelombang</p>
      </div>
    </div>
    <div class="d-flex align-items-center gap-3">
      <div class="text-end">
        <div class="fw-semibold"><i class="bi bi-person-badge-fill me-1"></i><?= htmlspecialchars($nama_admin) ?></div>
        <small class="text-white-50">Admin</small>
      </div>
      <a href="logout.php" class="btn-keluar"><i class="bi bi-box-arrow-right me-1"></i>Keluar</a>
    </div>
  </div>
</nav>

<div class="container py-4 py-md-5 flex-grow-1">

  <div class="mb-4 text-white">
    <h4 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($nama_admin) ?></h4>
    <p class="small mb-0" style="color:rgba(255,255,255,.75);">Pantau seluruh sistem monitoring gelombang laut.</p>
  </div>

  <!-- Kartu statistik -->
  <div class="row g-3 mb-3">

    <div class="col-6 col-lg-3">
      <div class="glass-box hover-up h-100 p-3 p-md-4">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="stat-chip"><i class="bi bi-people-fill"></i></div>
          <div class="small text-secondary fw-semibold">Petugas</div>
        </div>
        <div class="fs-2 fw-bold" style="color:#071952;line-height:1;"><?= $total_petugas ?></div>
        <small class="text-muted">petugas terdaftar</small>
      </div>
    </div>

    <div class="col-6 col-lg-3">
      <div class="glass-box hover-up h-100 p-3 p-md-4">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="stat-chip"><i class="bi bi-geo-alt-fill"></i></div>
          <div class="small text-secondary fw-semibold">Stasiun Aktif</div>
        </div>
        <div class="fs-2 fw-bold" style="color:#071952;line-height:1;"><?= $total_stasiun ?></div>
        <small class="text-muted">stasiun terdaftar</small>
      </div>
    </div>

    <div class="col-6 col-lg-3">
      <div class="glass-box hover-up h-100 p-3 p-md-4">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="stat-chip"><i class="bi bi-exclamation-octagon-fill"></i></div>
          <div class="small text-secondary fw-semibold">Data Bahaya</div>
        </div>
        <div class="fs-2 fw-bold" style="color:#b91c1c;line-height:1;"><?= $total_bahaya ?></div>
        <small class="text-muted">pengukuran tinggi &ge; 2 m</small>
      </div>
    </div>

    <div class="col-6 col-lg-3">
      <div class="glass-box hover-up h-100 p-3 p-md-4">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="stat-chip"><i class="bi bi-database-fill"></i></div>
          <div class="small text-secondary fw-semibold">Total Data</div>
        </div>
        <div class="fs-2 fw-bold" style="color:#071952;line-height:1;"><?= $total_data ?></div>
        <small class="text-muted">rekaman tersimpan</small>
      </div>
    </div>

  </div>

  <!-- Grafik -->
  <div class="glass-box p-3 p-md-4 mb-3">
    <div class="judul-box mb-3">
      <i class="bi bi-graph-up-arrow me-2" style="color:#0b666a;"></i>Riwayat Tinggi Gelombang
    </div>
    <div style="position:relative;height:260px;">
      <canvas id="grafikGelombang"></canvas>
    </div>
  </div>

  <!-- Tabel data terbaru -->
  <div class="glass-box p-3 p-md-4 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
      <div class="judul-box">
        <i class="bi bi-list-ul me-2" style="color:#0b666a;"></i>Data Terbaru
      </div>
      <a href="tambah_pengukuran.php" class="btn-tambah">
        <i class="bi bi-plus-circle-fill me-1"></i>Tambah Data
      </a>
    </div>
    <div class="table-responsive">
      <table class="table table-borderless align-middle mb-0 small tabel-modern">
        <thead>
          <tr class="text-secondary">
            <th class="ps-3">Waktu</th>
            <th>Stasiun</th>
            <th>Tinggi (m)</th>
            <th>Periode (s)</th>
            <th>Suhu (&deg;C)</th>
            <th>Petugas</th>
            <th>Status</th>
            <th class="text-end pe-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($hasil) > 0) { ?>
            <?php while ($data = mysqli_fetch_assoc($hasil)) { ?>
              <tr>
                <td class="ps-3 fw-semibold">
                  <?= htmlspecialchars($data['waktu']) ?>
                </td>
                <td><?= htmlspecialchars($data['nama_stasiun']) ?></td>
                <td><?= htmlspecialchars($data['tinggi']) ?></td>
                <td><?= htmlspecialchars($data['periode']) ?></td>
                <td><?= htmlspecialchars($data['suhu']) ?></td>
                <td><?= htmlspecialchars($data['nama_petugas']) ?></td>
                <td>
                  <?php
                  if ($data['tinggi'] < 1) {
                      echo '<span class="badge rounded-pill bg-success px-3 py-2">NORMAL</span>';
                  } elseif ($data['tinggi'] < 2) {
                      echo '<span class="badge rounded-pill bg-warning text-dark px-3 py-2">WASPADA</span>';
                  } else {
                      echo '<span class="badge rounded-pill bg-danger px-3 py-2">BAHAYA</span>';
                  }
                  ?>
                </td>
                <td class="text-end pe-3">
                  <a href="edit_pengukuran.php?waktu=<?php echo $data['waktu']; ?>"
                     class="btn btn-sm btn-outline-primary" style="border-radius:10px;">
                    <i class="bi bi-pencil-square"></i> Edit
                  </a>
                  <a href="hapus_pengukuran.php?waktu=<?php echo $data['waktu']; ?>"
                     class="btn btn-sm btn-outline-danger" style="border-radius:10px;"
                     onclick="return confirm('Yakin ingin menghapus data ini?')">
                    <i class="bi bi-trash3-fill"></i> Delete
                  </a>
                </td>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                Belum ada data pengukuran.
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<footer class="text-center text-white py-3" style="width:100%;background:linear-gradient(135deg,#071952,#0b666a);border-top:1px solid rgba(255,255,255,.2);">
  <small>Created By Ade Ganteng</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  // Grafik tinggi gelombang
  const canvas = document.getElementById('grafikGelombang');
  const ctx = canvas.getContext('2d');

  const grad = ctx.createLinearGradient(0, 0, 0, 260);
  grad.addColorStop(0, 'rgba(53,162,159,.45)');
  grad.addColorStop(1, 'rgba(53,162,159,.02)');

  const labelGrafik = <?= json_encode($label_grafik, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  const dataGrafik = <?= json_encode($data_grafik, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labelGrafik,
      datasets: [{
        label: 'Tinggi Gelombang (m)',
        data: dataGrafik,
        borderColor: '#0b666a',
        backgroundColor: grad,
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointBackgroundColor: '#ffffff',
        pointBorderColor: '#0b666a',
        pointBorderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          beginAtZero: true,
          title: { display: true, text: 'Tinggi (m)' },
          grid: { color: 'rgba(0,0,0,.05)' }
        },
        x: { grid: { display: false } }
      }
    }
  });

  // Animasi ikon melayang
  document.getElementById('iconContainer').animate(
    [{ transform: 'translateY(0)' }, { transform: 'translateY(-6px)' }, { transform: 'translateY(0)' }],
    { duration: 3000, iterations: Infinity, easing: 'ease-in-out' }
  );
</script>
</body>
</html>
