<?php
    session_start();
    if (isset($_SESSION['nama'])) {
        header('Location: main.php');
        exit;
    }
    if (isset($_SESSION['nama'])) {
        header('Location: main_admin.php');
        exit;
    }
    include('koneksi.php');
?>

<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <title>Login - nami data</title>
    <style>

    input::-ms-reveal,
    input::-ms-clear {
      display: none;
    }
    </style>
  </head>

  <body style="background: radial-gradient(circle at 10% 20%, rgba(7, 25, 82, 0.9) 0%, rgba(11, 102, 106, 0.85) 90%), url('https://images.unsplash.com/photo-1518837695005-2083093ee35b?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center;">

    <div id="modern-ui-wrapper" style="width: 100%; min-height: 100vh;">

      <div class="container py-5">
        <div class="row justify-content-center align-items-center min-vh-100">
          <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">

            <!-- Card -->
            <div class="card border-0"
                 style="background: rgba(255, 255, 255, 0.82); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.6); border-radius: 24px; box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.4) inset; overflow: hidden; transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);"
                 onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 40px 80px rgba(0,0,0,0.4), 0 0 30px rgba(53,162,159,0.3)';"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 30px 60px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.4) inset';">

              <!-- Header -->
              <div class="text-white"
                   style="background: linear-gradient(135deg, #071952, #0b666a); padding: 2.5rem 1.5rem 2rem; text-align: center; position: relative;">
                <div id="iconContainer"
                     style="width: 75px; height: 75px; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);">
                  <i class="bi bi-water text-info" style="font-size: 2.2rem;"></i>
                </div>
                <h3 class="fw-bold mb-1" style="letter-spacing: 0.05em;">Nami Data</h3>
                <p class="small text-white-50 mb-0 font-monospace">Portal Pemantauan Tinggi Gelombang</p>
              </div>

              <!-- Form Body -->
              <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                  <h5 class="fw-bold text-dark mb-1">Selamat Datang Kembali</h5>
                  <p class="text-muted small">Silakan masuk untuk melihat gelombang laut terkini</p>
                </div>

                <form action="proses_login.php" method="post">

                  <!-- Username -->
                  <div class="mb-3">
                    <label for="username" class="form-label text-secondary small fw-semibold">Username Akun</label>
                    <div class="input-group"
                         style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; overflow: hidden; transition: all 0.3s ease;"
                         onfocusin="this.style.borderColor='#35a29f'; this.style.background='#ffffff'; this.style.boxShadow='0 0 0 4px rgba(53,162,159,0.15)';"
                         onfocusout="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc'; this.style.boxShadow='none';">
                      <span class="input-group-text" style="background: transparent; border: none; color: #0b666a; padding-left: 1.25rem;">
                        <i class="bi bi-person-badge-fill"></i>
                      </span>
                      <input type="text" class="form-control" name="username" id="username" placeholder="Masukkan Username" required
                             style="background: transparent; border: none; padding: 0.85rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: none;">
                    </div>
                  </div>

                  <!-- Password -->
                  <div class="mb-3">
                    <label for="password" class="form-label text-secondary small fw-semibold">Kata Sandi</label>
                    <div class="input-group"
                         style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; overflow: hidden; transition: all 0.3s ease;"
                         onfocusin="this.style.borderColor='#35a29f'; this.style.background='#ffffff'; this.style.boxShadow='0 0 0 4px rgba(53,162,159,0.15)';"
                         onfocusout="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc'; this.style.boxShadow='none';">
                      <span class="input-group-text" style="background: transparent; border: none; color: #0b666a; padding-left: 1.25rem;">
                        <i class="bi bi-shield-lock-fill"></i>
                      </span>
                      <input type="password" class="form-control" name="password" id="password" placeholder="Masukkan password" required
                             style="background: transparent; border: none; padding: 0.85rem 1rem; font-size: 0.95rem; color: #1e293b; box-shadow: none;">
                      <button type="button" id="togglePassword"
                              style="background: transparent; border: none; color: #64748b; cursor: pointer; padding-right: 1.25rem; transition: color 0.2s;"
                              onmouseover="this.style.color='#071952';"
                              onmouseout="this.style.color='#64748b';">
                        <i class="bi bi-eye-slash" id="eyeIcon"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Submit -->
                  <div class="d-grid">
                    <button type="submit" name="submit" id="submit" class="btn"
                            style="background: linear-gradient(135deg, #0b666a, #071952); border: none; border-radius: 14px; padding: 0.9rem; font-weight: 700; letter-spacing: 0.5px; color: #ffffff; box-shadow: 0 10px 25px rgba(11, 102, 106, 0.3); transition: all 0.3s ease;"
                            onmouseover="this.style.background='linear-gradient(135deg, #35a29f, #0b666a)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 30px rgba(53,162,159,0.4)';"
                            onmouseout="this.style.background='linear-gradient(135deg, #0b666a, #071952)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(11,102,106,0.3)';">
                      <i class="bi bi-arrow-right-circle-fill me-2"></i>Masuk ke Sistem Monitoring
                    </button>
                  </div>

                </form>
              </div>

              <!-- Footer -->
              <div class="card-footer bg-light text-center py-3 border-0">
                <small class="text-muted">Created By Ade Ganteng
                </small>
              </div>

            </div>

          </div>
        </div>
      </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <script>
      // Toggle lihat/sembunyikan password
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');

      togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        eyeIcon.classList.toggle('bi-eye');
        eyeIcon.classList.toggle('bi-eye-slash');
      });

      // Animasi ikon melayang (pengganti @keyframes floatIcon)
      document.getElementById('iconContainer').animate(
        [
          { transform: 'translateY(0)' },
          { transform: 'translateY(-6px)' },
          { transform: 'translateY(0)' }
        ],
        { duration: 3000, iterations: Infinity, easing: 'ease-in-out' }
      );
    </script>
  </body>
</html>