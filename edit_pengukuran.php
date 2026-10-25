<?php
    include('koneksi.php');
    $waktu = $_GET['waktu'];
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <title>Ubah Data Pengukuran - Nami Data</title>
    <style>
      .kolom {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        transition: all 0.3s ease;
      }
      .kolom:focus-within {
        border-color: #35a29f;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(53, 162, 159, 0.15);
      }
      .kolom .input-group-text {
        background: transparent;
        border: none;
        color: #0b666a;
        padding-left: 1.25rem;
      }
      .kolom .form-control,
      .kolom .form-select {
        background-color: transparent;
        border: none;
        padding: 0.85rem 1rem;
        font-size: 0.95rem;
        color: #1e293b;
        box-shadow: none;
      }
      .kolom .satuan {
        background: transparent;
        border: none;
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
        padding-right: 1.25rem;
      }
      .btn-simpan {
        background: linear-gradient(135deg, #0b666a, #071952);
        border: none;
        border-radius: 14px;
        padding: 0.9rem;
        font-weight: 700;
        color: #ffffff;
        box-shadow: 0 10px 25px rgba(11, 102, 106, 0.3);
        transition: all 0.3s ease;
      }
      .btn-simpan:hover {
        background: linear-gradient(135deg, #35a29f, #0b666a);
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(53, 162, 159, 0.4);
      }
      .btn-batal {
        background: #eef2f6;
        border: none;
        border-radius: 14px;
        padding: 0.9rem;
        font-weight: 600;
        color: #475569;
        transition: all 0.3s ease;
      }
      .btn-batal:hover {
        background: #e2e8f0;
        color: #071952;
      }
    </style>
  </head>

  <body style="background: radial-gradient(circle at 10% 20%, rgba(7, 25, 82, 0.9) 0%, rgba(11, 102, 106, 0.85) 90%), url('https://images.unsplash.com/photo-1518837695005-2083093ee35b?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh;">

    <div class="container py-5">
      <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">

          <!-- Card -->
          <div class="card border-0"
               style="background: rgba(255, 255, 255, 0.82); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.6); border-radius: 24px; box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.4) inset; overflow: hidden;">

            <!-- Header -->
            <div class="text-white"
                 style="background: linear-gradient(135deg, #071952, #0b666a); padding: 2.5rem 1.5rem 2rem; text-align: center;">
              <div id="iconContainer"
                   style="width: 75px; height: 75px; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);">
                <i class="bi bi-pencil-square text-info" style="font-size: 2.2rem;"></i>
              </div>
              <h3 class="fw-bold mb-1" style="letter-spacing: 0.05em;">Ubah Data Pengukuran</h3>
              <p class="small text-white-50 mb-0">Perbarui hasil pengukuran tinggi gelombang</p>
            </div>

            <!-- Form Body -->
            <div class="card-body p-4 p-md-5">
              <form action="proses_update.php" method="post">

                <input type="hidden" name="id_pengukuran" value="1">

                <!-- Stasiun -->
                <div class="mb-3">
                  <label for="id_stasiun" class="form-label text-secondary small fw-semibold">Stasiun</label>
                  <div class="input-group kolom">
                    <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                    <select class="form-select" name="id_stasiun" id="id_stasiun" required>
                      <option value="">Pilih stasiun</option>
                      <option value="2010" selected>Stasiun Anyer</option>
                      <option value="2011">Stasiun Merak</option>
                      <option value="2012">Stasiun Labuan</option>
                    </select>
                  </div>
                </div>

                <!-- Tinggi -->
                <div class="mb-3">
                  <label for="tinggi" class="form-label text-secondary small fw-semibold">Tinggi Gelombang</label>
                  <div class="input-group kolom">
                    <span class="input-group-text"><i class="bi bi-rulers"></i></span>
                    <input type="number" step="0.01" min="0" class="form-control" name="tinggi" id="tinggi" placeholder="Contoh: 1.25" value="1.25" required>
                    <span class="input-group-text satuan">m</span>
                  </div>
                </div>

                <!-- Periode -->
                <div class="mb-3">
                  <label for="periode" class="form-label text-secondary small fw-semibold">Periode Gelombang</label>
                  <div class="input-group kolom">
                    <span class="input-group-text"><i class="bi bi-stopwatch-fill"></i></span>
                    <input type="number" step="0.01" min="0" class="form-control" name="periode" id="periode" placeholder="Contoh: 5.40" value="5.40" required>
                    <span class="input-group-text satuan">detik</span>
                  </div>
                </div>

                <!-- Suhu -->
                <div class="mb-3">
                  <label for="suhu" class="form-label text-secondary small fw-semibold">Suhu Air</label>
                  <div class="input-group kolom">
                    <span class="input-group-text"><i class="bi bi-thermometer-half"></i></span>
                    <input type="number" step="0.01" class="form-control" name="suhu" id="suhu" placeholder="Contoh: 29.10" value="29.10" required>
                    <span class="input-group-text satuan">&deg;C</span>
                  </div>
                </div>

                <!-- Petugas -->
                <div class="mb-4">
                  <label for="id_petugas" class="form-label text-secondary small fw-semibold">Petugas</label>
                  <div class="input-group kolom">
                    <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                    <select class="form-select" name="id_petugas" id="id_petugas" required>
                      <option value="">Pilih petugas</option>
                      <option value="2001" selected>Ade Rasyah Maulana</option>
                      <option value="2002">Muhammad Dien Muharam</option>
                    </select>
                  </div>
                </div>

                <!-- Tombol -->
                <div class="row g-2">
                  <div class="col-5">
                    <a href="dashboard.php" class="btn btn-batal w-100">Batal</a>
                  </div>
                  <div class="col-7">
                    <input type="hidden" value="<?php echo $waktu;?>" name="isbn">
                    <input type="submit" name="submit" id="submit" class="btn btn-simpan w-100">
                      <i class="bi bi-check-circle-fill me-2"></i>Simpan perubahan
                    </button>
                  </div>
                </div>

              </form>
            </div>

            <!-- Footer -->
            <div class="card-footer bg-light text-center py-3 border-0">
              <small class="text-muted">Created By Ade Ganteng</small>
            </div>

          </div>

        </div>
      </div>
    </div>

    <script>
      // Animasi ikon melayang (dimatikan jika perangkat meminta gerakan dikurangi)
      if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.getElementById('iconContainer').animate(
          [
            { transform: 'translateY(0)' },
            { transform: 'translateY(-6px)' },
            { transform: 'translateY(0)' }
          ],
          { duration: 3000, iterations: Infinity, easing: 'ease-in-out' }
        );
      }
    </script>
  </body>
</html>
