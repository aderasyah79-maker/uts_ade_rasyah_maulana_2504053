<?php
session_start();

if (!isset($_SESSION['nama'])) {
    header('Location: login.php');
    exit;
}

include('koneksi.php');
$query_terbaru = mysqli_query(
    $koneksi,
    "SELECT * FROM pengukuran ORDER BY waktu DESC LIMIT 1"
);

if (!$query_terbaru) {
    die("Query data terbaru gagal: " . mysqli_error($koneksi));
}

$data_terbaru = mysqli_fetch_assoc($query_terbaru);

$tinggi = isset($data_terbaru['tinggi'])
    ? (float) $data_terbaru['tinggi']
    : null;

if ($tinggi === null) {
    $status = 'Belum Ada Data';
    $warnaStatus = '#64748b';
    $ikonStatus = 'bi-question-circle';
} elseif ($tinggi < 1) {
    $status = 'Rendah';
    $warnaStatus = '#15803d';
    $ikonStatus = 'bi-check-circle-fill';
} elseif ($tinggi < 2) {
    $status = 'Sedang';
    $warnaStatus = '#d97706';
    $ikonStatus = 'bi-exclamation-triangle-fill';
} else {
    $status = 'Tinggi';
    $warnaStatus = '#b91c1c';
    $ikonStatus = 'bi-exclamation-octagon-fill';
}


$hasil = mysqli_query(
    $koneksi,
    "SELECT * FROM pengukuran ORDER BY waktu DESC"
);

if (!$hasil) {
    die("Query tabel gagal: " . mysqli_error($koneksi));
}

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

while ($row_grafik = mysqli_fetch_assoc($query_grafik)) {
    // Format waktu agar lebih ringkas
    $label_grafik[] = date(
        'H:i',
        strtotime($row_grafik['waktu'])
    );

    $data_grafik[] = (float) $row_grafik['tinggi'];
}

// Urutkan dari waktu paling lama ke paling baru
$label_grafik = array_reverse($label_grafik);
$data_grafik = array_reverse($data_grafik);
?>


<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <title>Dashboard Petugas - Nami Data</title>

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
      .glass-box:hover {
        transform: translateY(-4px);
        box-shadow: 0 26px 50px rgba(0, 0, 0, 0.3), 0 0 24px rgba(53, 162, 159, 0.25);
      }
      .stat-chip {
        width: 46px; height: 46px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #0b666a, #071952);
        color: #fff; font-size: 1.25rem;
        box-shadow: 0 8px 16px rgba(11, 102, 106, 0.35);
      }
      .judul-box { color: #071952; font-weight: 700; }
      .tabel-modern { border-collapse: separate; border-spacing: 0 8px; }
      .tabel-modern tbody tr { background: rgba(255, 255, 255, 0.75); }
      .tabel-modern tbody td:first-child { border-radius: 12px 0 0 12px; }
      .tabel-modern tbody td:last-child { border-radius: 0 12px 12px 0; }
      .tabel-modern tbody tr:hover { background: #ffffff; }
      .btn-keluar {
        background: rgba(255, 255, 255, 0.14); border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px; color: #fff; padding: 0.5rem 1rem; font-weight: 600;
        transition: all 0.25s ease;
      }
      .btn-keluar:hover { background: rgba(255, 255, 255, 0.28); color: #fff; }
    </style>
  </head>

  <body style="background: radial-gradient(circle at 10% 20%, rgba(7, 25, 82, 0.9) 0%, rgba(11, 102, 106, 0.85) 90%), url('https://images.unsplash.com/photo-1518837695005-2083093ee35b?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh;">

    <!-- Navbar -->
    <nav class="text-white"
         style="position: sticky; top: 0; z-index: 1030; width: 100%; background: linear-gradient(135deg, #071952, #0b666a); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35); border-bottom: 1px solid rgba(255, 255, 255, 0.2);">
      <div class="container-fluid px-3 px-md-5 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
          <div id="iconContainer"
               style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25); backdrop-filter: blur(8px);">
            <i class="bi bi-water text-info" style="font-size: 1.7rem;"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0" style="letter-spacing: 0.05em;">Nami Data</h4>
            <p class="small text-white-50 mb-0 font-monospace">Portal Pemantauan Tinggi Gelombang</p>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div class="text-end">
            <div class="fw-semibold"><i class="bi bi-person-badge-fill me-1"></i><?php echo $_SESSION['nama'];?></div>
            <small class="text-white-50">Petugas</small>
          </div>
          <a href="logout.php" class="btn btn-keluar"><i class="bi bi-box-arrow-right me-1"></i>Keluar</a>
        </div>
      </div>
    </nav>

    <div class="container py-4 py-md-5">

      <div class="mb-4 text-white">
        <h4 class="fw-bold mb-1">Selamat Datang, <?php echo $_SESSION['nama'];?> Petugas</h4>
        <p class="small mb-0" style="color: rgba(255,255,255,0.75);">Berikut kondisi gelombang laut terkini di stasiun pengamatan.</p>
      </div>

      <!-- Kartu ringkasan -->
      <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
          <div class="glass-box h-100 p-3 p-md-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="stat-chip"><i class="bi bi-water"></i></div>
              <div class="small text-secondary fw-semibold">Tinggi Gelombang</div>
            </div>
            <div class="fs-2 fw-bold" style="color:#071952; line-height: 1;"><span id="valTinggi"><?php echo htmlspecialchars($data_terbaru['tinggi']); ?></span><span class="fs-6 fw-semibold text-secondary">m</span></div>
            <small id="valSelisih" class="text-success fw-semibold">
              <i class="bi bi-arrow-up-short"></i>Naik 0,2 m dari sebelumnya
            </small>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="glass-box h-100 p-3 p-md-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="stat-chip"><i class="bi bi-clock-history"></i></div>
              <div class="small text-secondary fw-semibold">Periode</div>
            </div>
            <div class="fs-2 fw-bold" style="color:#071952; line-height: 1;"><span id="valPeriode"><?php echo htmlspecialchars($data_terbaru['periode']); ?></span> <span class="fs-6 fw-semibold text-secondary">s</span></div>
            <small class="text-muted">Waktu antar gelombang</small>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="glass-box h-100 p-3 p-md-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="stat-chip"><i class="bi bi-thermometer-half"></i></div>
              <div class="small text-secondary fw-semibold">Suhu Air</div>
            </div>
            <div class="fs-2 fw-bold" style="color:#071952; line-height: 1;"><span id="valSuhu"><?php echo htmlspecialchars($data_terbaru['suhu']); ?></span> <span class="fs-6 fw-semibold text-secondary">&deg;C</span></div>
            <small class="text-muted">Data terbaru</small>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="glass-box h-100 p-3 p-md-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="stat-chip"><i class="bi bi-stopwatch"></i></div>
              <div class="small text-secondary fw-semibold">Pembaruan</div>
            </div>
            <div class="fs-2 fw-bold" id="valWaktu" style="color:#071952; line-height: 1;"><?php echo htmlspecialchars($data_terbaru['waktu']); ?></div>
            <small class="text-muted">Data terbaru</small>
          </div>
        </div>
      </div>

      <!-- Grafik + status -->
      <div class="row g-3 mb-3">
        <div class="col-lg-8">
          <div class="glass-box h-100 p-3 p-md-4">
              <div class="judul-box mb-3">
                  <i class="bi bi-graph-up-arrow me-2"
                    style="color:#0b666a;"></i>
                  Grafik Tinggi Gelombang
              </div>

              <div style="position: relative; height: 250px;">
                  <canvas id="grafikGelombang"></canvas>
              </div>
          </div>
      </div> 
          <div class="col-lg-4">
              <div id="kartuStatus"
                  class="h-100 p-4 text-white text-center d-flex flex-column justify-content-center align-items-center"
                  style="
                      border-radius: 22px;
                      background: <?= htmlspecialchars($warnaStatus) ?>;
                      box-shadow: 0 18px 40px rgba(0,0,0,0.28);
                      border: 1px solid rgba(255,255,255,0.3);
                  ">

                  <i class="bi <?= htmlspecialchars($ikonStatus) ?>"
                    style="font-size: 3rem;"></i>

                  <div class="small fw-semibold mt-2" style="opacity: 0.85;">
                      Status Kondisi
                  </div>

                  <div class="fw-bold"
                      style="font-size: 2rem; letter-spacing: 0.05em;">
                      <?= htmlspecialchars($status) ?>
                  </div>

                  <p class="small mt-2 mb-0" style="opacity: 0.9;">
                      Tinggi gelombang saat ini
                      <strong>
                          <?= $tinggi === null
                              ? '-'
                              : htmlspecialchars((string) $data_terbaru['tinggi']) ?>
                      </strong>
                      m
                  </p>
              </div>
          </div>
      </div>

      <!-- Tabel data terbaru -->
      <div class="glass-box p-3 p-md-4 mb-4">
        <div class="judul-box mb-2"><i class="bi bi-list-ul me-2" style="color:#0b666a;"></i>Data Terbaru</div>
        <div class="table-responsive">
          <table class="table table-borderless align-middle mb-0 small tabel-modern">
            <thead>
              <tr class="text-secondary">
                <th class="ps-3">Waktu</th>
                <th>Tinggi (m)</th>
                <th>Periode (s)</th>
                <th>Suhu (&deg;C)</th>
                <th>Status</th>
              </tr>
            </thead>  
            <tbody>
                <?php if (mysqli_num_rows($hasil) > 0) { ?>
                    <?php while ($data = mysqli_fetch_assoc($hasil)) { ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($data['waktu']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['tinggi']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['periode']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($data['suhu']); ?>
                            </td>
                            <td>
                                <?php
                                if ($data['tinggi'] < 1) {
                                    echo '<span class="badge bg-success">Rendah</span>';
                                } elseif ($data['tinggi'] < 2) {
                                    echo '<span class="badge bg-warning text-dark">Sedang</span>';
                                } else {
                                    echo '<span class="badge bg-danger">Tinggi</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Belum ada data pengukuran.
                        </td>
                    </tr>
                <?php } ?>
            </tbody>

          </table>
        </div>
      </div>

    </div>

    <!-- Footer -->
    <footer class="text-center text-white py-3"
            style="width: 100%; background: linear-gradient(135deg, #071952, #0b666a); border-top: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.25);">
      <small>Created By Ade Ganteng</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
<script>
const canvasGrafik = document.getElementById('grafikGelombang');

const labelGrafik = <?= json_encode(
    $label_grafik,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

const dataGrafik = <?= json_encode(
    $data_grafik,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

new Chart(canvasGrafik, {
    type: 'line',

    data: {
        labels: labelGrafik,
        datasets: [{
            label: 'Tinggi Gelombang (m)',
            data: dataGrafik,
            borderColor: '#0b666a',
            backgroundColor: 'rgba(11, 102, 106, 0.15)',
            borderWidth: 3,
            fill: true,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#071952'
        }]
    },

    options: {
        responsive: true,
        maintainAspectRatio: false,

        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },

        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Waktu Pengukuran'
                }
            },

            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Tinggi Gelombang (m)'
                }
            }
        }
    }
});
</script>
  </body>
</html>
