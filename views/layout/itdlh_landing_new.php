<?php 
  use app\core\Application; 

  /**
   * Main layout used for most pages. Inserts `{{content}}` where views are rendered.
   */
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $this->title; ?></title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $this->asset('css/itdlh.css'); ?>">
    
    <style>
      :root {
        --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        --text-dark: #0f2043;
        --accent-color: #00d2ff;
      }
      
      body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        background-color: #f8fafc;
        color: #334155;
      }

      /* Premium Sticky & Floating Navbar */
      .custom-navbar {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 15px 0;
      }
      .navbar-brand {
        font-weight: 800;
        color: #1e3c72 !important;
        letter-spacing: -0.5px;
      }
      .nav-link {
        font-weight: 500;
        color: #475569 !important;
        transition: color 0.3s ease, transform 0.2s ease;
        padding: 6px 12px !important;
        text-transform: capitalize;
      }
      .nav-link:hover {
        color: #1e3c72 !important;
        transform: translateY(-1px);
      }
      .nav-item.active .nav-link {
        color: #1e3c72 !important;
        font-weight: 700;
      }

      /* Inner Pages Hero Section */
      .hero.main-inner {
        background: linear-gradient(rgba(15, 32, 67, 0.9), rgba(15, 32, 67, 0.9)), 
                    url('https://unsplash.com') no-repeat center center/cover;
        padding: 80px 0;
        color: #fff;
        margin-bottom: 40px;
        border-bottom-left-radius: 24px;
        border-bottom-right-radius: 24px;
        box-shadow: 0 10px 30px rgba(15, 32, 67, 0.1);
      }
      .hero-content h1 {
        font-weight: 800;
        letter-spacing: -1px;
      }
      
      /* Modern Custom Badges for Users */
      .user-badge {
        background: var(--primary-gradient);
        color: white !important;
        border-radius: 20px;
        padding: 6px 16px !important;
        font-weight: 600;
      }
      .user-badge:hover {
        box-shadow: 0 4px 12px rgba(30, 60, 114, 0.2);
        color: white !important;
      }
    </style>
  </head>
  <body>
    
    <!-- NAVIGATION BAR -->
    <nav class="navbar navbar-expand-lg custom-navbar sticky-top">
      <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
          <i class="bi bi-cpu text-primary me-2 fs-4"></i>ITDLH Kelaniya
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $this->asset(''); ?>"><i class="bi bi-house-door me-1"></i>Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $this->asset('about'); ?>">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $this->asset('contact'); ?>">Contact</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $this->asset('staff'); ?>">Staff</a>
            </li>
            <?php if (!Application::isGuest()): ?>
              <li class="nav-item">
                <a class="nav-link text-primary" href="<?php echo $this->asset('app'); ?>"><i class="bi bi-grid-1x2-fill me-1"></i>App</a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-danger" href="<?php echo $this->asset('admin'); ?>"><i class="bi bi-shield-lock-fill me-1"></i>Admin</a>
              </li>
            <?php endif; ?>
          </ul>

          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-2 align-items-center">
            <?php if (Application::isGuest()): ?>
              <li class="nav-item"><a class="btn btn-sm btn-outline-primary px-3 rounded-pill" href="<?php echo $this->asset('login'); ?>">Login</a></li>
              <li class="nav-item"><a class="btn btn-sm btn-primary px-3 rounded-pill shadow-sm" href="<?php echo $this->asset('register'); ?>">Register</a></li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link" href="<?php echo $this->asset('profile'); ?>"><i class="bi bi-person-circle me-1"></i>Profile</a></li>
              <li class="nav-item">
                <a class="nav-link user-badge shadow-sm" href="<?php echo $this->asset('logout'); ?>">
                  <i class="bi bi-box-arrow-right me-1"></i>Logout (<?php echo htmlspecialchars(Application::$app->user->getDisplayName()); ?>)
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>

    <!-- INNER PAGE HERO -->
    <header class="hero main-inner text-center text-md-start">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h1 class="display-5 mb-2 text-white">ITDLH Kelaniya</h1>
            <p class="lead text-white-50 mb-0 fs-5">Practical IT courses for students — programming, web, databases and more.</p>
          </div>
          <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a class="btn btn-info fw-bold rounded-pill px-4 shadow-sm" href="<?php echo $this->asset('register'); ?>">Join a Course</a>
          </div>
        </div>
      </div> 
    </header>
    <!-- MAIN CONTENT & FLASH MESSAGES -->
    <div class="container main-inner min-vh-50">
      <?php $flash = Application::$app->session->getFlash('success'); ?>
      <?php if ($flash): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center" role="alert">
          <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
          <div><?php echo $flash; ?></div>
        </div>
      <?php else: ?>
        <?php $flash = Application::$app->session->getFlash('error'); ?>
        <?php if ($flash): ?>
          <div class="alert alert-danger border-0 shadow-sm rounded-3 d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-danger"></i>
            <div><?php echo $flash; ?></div>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Dynamic Page Content Insertion -->
      <div class="py-2">
        {{content}}
      </div>
    </div>
   
    <!-- PREMIUM FOOTER -->
    <footer class="py-5 text-white bg-dark main-inner border-top border-secondary border-opacity-25 mt-5">
      <div class="container">
        <div class="row g-4 text-center text-md-start mb-4">
          <div class="col-md-6">
            <h5 class="fw-bold text-white mb-3">ITDLH Kelaniya</h5>
            <p class="small text-secondary mb-0">Empowering the next generation of technology professionals in Sri Lanka through rigorous practical guidance and project-based certifications.</p>
          </div>
          <div class="col-md-6 text-md-end">
            <h6 class="fw-bold text-white mb-2">Connect With Us</h6>
            <p class="small text-secondary mb-1"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Makola North, Kelaniya, Sri Lanka</p>
            <p class="small text-secondary mb-0"><i class="bi bi-envelope-at-fill text-info me-2"></i>itdlhkelaniya@gmail.com</p>
          </div>
        </div>
        <hr class="border-secondary opacity-25">
        <div class="text-center pt-2">
          <p class="small text-secondary mb-0">&copy; <?php echo date('Y'); ?> ITDLH Kelaniya. All Rights Reserved.</p>
          <p class="small text-muted mb-0" style="font-size: 11px;">Powered by <a href="https://github.com/prageeth79/Mandakini-framework" target="_blank" class="text-secondary text-decoration-none fw-semibold">Mandakini Framework</a></p>
        </div>
      </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  </body>
</html>
