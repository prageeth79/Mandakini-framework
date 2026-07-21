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
  <title>ITDLH Kelaniya — Information Technology Courses</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?php echo $this->asset('css/itdlh.css'); ?>">
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
      --accent-color: #00d2ff;
      --card-hover-transform: translateY(-10px);
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
    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    .hero {
      background: linear-gradient(rgba(15, 32, 67, 0.88), rgba(15, 32, 67, 0.88)), 
                  url('https://unsplash.com') no-repeat center center/cover;
      padding: 140px 0;
      color: #fff;
    }
    .section-title {
      font-weight: 800;
      position: relative;
      padding-bottom: 15px;
      color: #0f2043;
    }
    .section-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 4px;
      background: var(--accent-color);
      border-radius: 2px;
    }
    .text-start.section-title::after {
      left: 0;
      transform: translateX(0);
    }
    .feature-card, .course-card {
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      border: none !important;
      border-radius: 16px !important;
      overflow: hidden;
    }
    .feature-card:hover, .course-card:hover {
      transform: var(--card-hover-transform);
      box-shadow: 0 15px 30px rgba(0,0,0,0.12) !important;
    }
    .staff-card {
      border: none !important;
      border-radius: 20px !important;
      transition: all 0.4s ease;
      background: #ffffff;
      position: relative;
      overflow: hidden;
    }
    .staff-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 6px;
      background: var(--primary-gradient);
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    .staff-card:hover::before {
      opacity: 1;
    }
    .staff-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(30, 60, 114, 0.12) !important;
    }
    .staff-img-wrapper {
      position: relative;
      display: inline-block;
      padding: 5px;
      background: linear-gradient(45deg, #00d2ff, #2a5298);
      border-radius: 50%;
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
<body class="bg-light">
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
  <header class="hero text-center text-md-start">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <span class="badge bg-info text-dark fw-bold mb-3 px-3 py-2 text-uppercase rounded-pill shadow-sm">Empowering Digital Careers</span>
          <h1 class="display-4 fw-bold mb-3 text-white">ITDLH Kelaniya</h1>
          <p class="lead text-white-50 mb-4 fs-4">Unlock your technical potential with world-class, practical IT and professional communication courses designed for today's global market.</p>
          <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
            <a class="btn btn-info btn-lg px-4 py-3 fw-bold rounded-pill text-dark shadow-sm" href="<?php echo $this->asset('register'); ?>">Join a Course Today</a>
            <a class="btn btn-outline-light btn-lg px-4 py-3 rounded-pill" href="<?php echo $this->asset('contact'); ?>"><i class="bi bi-chat-left-text me-2"></i>Contact Us</a>
          </div>
        </div>
      </div>
    </div> 
  </header>
  <!-- KEY FEATURES SECTION -->
  <section class="features py-5 bg-white">
    <div class="container py-3">
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm feature-card">
            <img src="<?php echo $this->asset('images/features/programming.jpg'); ?>" class="card-img-top" alt="Programming" style="height: 200px; object-fit: cover;">
            <div class="card-body p-4">
              <div class="text-primary mb-3 fs-3"><i class="bi bi-code-square"></i></div>
              <h5 class="fw-bold">Software Development</h5>
              <p class="text-muted card-text mb-0">Hands-on programming courses focusing on PHP, Python, JavaScript, and modern structural engineering frameworks.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm feature-card">
            <img src="<?php echo $this->asset('images/features/web.jpg'); ?>" class="card-img-top" alt="Web" style="height: 200px; object-fit: cover;">
            <div class="card-body p-4">
              <div class="text-success mb-3 fs-3"><i class="bi bi-laptop"></i></div>
              <h5 class="fw-bold">Web & Frontend</h5>
              <p class="text-muted card-text mb-0">Build breathtaking, fully responsive and functional websites using HTML, CSS, Bootstrap, and JS libraries.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm feature-card">
            <img src="<?php echo $this->asset('images/features/datadevops.jpg'); ?>" class="card-img-top" alt="Databases & DevOps" style="height: 200px; object-fit: cover;">
            <div class="card-body p-4">
              <div class="text-warning mb-3 fs-3"><i class="bi bi-database-check"></i></div>
              <h5 class="fw-bold">Databases & DevOps</h5>
              <p class="text-muted card-text mb-0">Master SQL relational database designs, query optimization, backend deployment, and cloud architectures.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT SECTION -->
  <section class="about bg-light py-5">
    <div class="container py-4">
      <div class="row align-items-center g-5">
        <div class="col-md-6">
          <h2 class="section-title text-start mb-4">About ITDLH Kelaniya</h2>
          <p class="lead text-secondary">We are an educational institute focused on delivering practical IT education to students who want to build high-value careers in technology.</p>
          <p class="text-muted">Our instructors are industry-experienced and classes include modern web development labs and dynamic real-world project assignments.</p>
          <div class="mt-4">
            <div class="d-flex align-items-center mb-3"><i class="bi bi-check-circle-fill text-success fs-5 me-3"></i><span class="fw-medium text-dark">Small class sizes for personalized guidance</span></div>
            <div class="d-flex align-items-center mb-3"><i class="bi bi-check-circle-fill text-success fs-5 me-3"></i><span class="fw-medium text-dark">100% Project-based learning and interactive labs</span></div>
            <div class="d-flex align-items-center"><i class="bi bi-check-circle-fill text-success fs-5 me-3"></i><span class="fw-medium text-dark">Recognized valid certifications & career help</span></div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="position-relative">
            <img src="<?php echo $this->asset('images/about.jpg'); ?>" class="img-fluid rounded-4 shadow-lg" alt="Students learning">
            <div class="position-absolute bottom-0 start-0 bg-dark text-white p-3 m-3 rounded-3 opacity-90 d-none d-sm-block shadow">
              <h6 class="mb-0 fw-bold"><i class="bi bi-shield-check text-info me-2"></i>Ministry Registered Institute</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- DYNAMIC COURSES SECTION -->
  <section class="courses py-5 bg-white">
    <div class="container py-3">
      <h2 class="text-center section-title mb-5">Explore Our Professional Courses</h2>
      <div class="row g-4">
        <?php foreach($webCourseList as $course) { ?>
          <div class="col-md-4">
            <div class="card h-100 shadow-sm course-card border-light">
              <img src="<?php echo $this->asset($course->course_image_land); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course->course_name); ?>" style="height: 220px; object-fit: cover;">
              <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                  <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($course->course_name); ?></h5>
                  <p class="text-muted small mb-4"><?php echo htmlspecialchars($course->course_description); ?></p>
                </div>
                <a class="btn btn-outline-primary w-100 rounded-pill fw-medium" href="<?php echo $this->asset('itdlh/course/' . $course->course_id ); ?>">View Course Details</a>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
  </section>
  <!-- STAFF SECTION -->
  <section class="staff bg-light py-5 border-top">
    <div class="container py-4">
      <div class="text-center mb-5">
        <h2 class="section-title mb-3">Meet Our Elite Academic Team</h2>
        <p class="text-muted">Learn directly from certified professionals, corporate instructors, and educational pioneers.</p>
      </div>
      <div class="row g-4 justify-content-center">
        <!-- Member 0 -->
        <div class="col-lg-3 col-sm-6">
          <div class="card h-100 text-center shadow-sm staff-card p-4">
            <div class="my-3">
              <div class="staff-img-wrapper">
                <img src="<?php echo $this->asset('images/staff/person.webp'); ?>" class="rounded-circle bg-white" alt="Mr. L.A.S. Kumarasinghe" style="width: 120px; height: 120px; object-fit: cover; padding: 4px;">
              </div>
            </div>
            <div class="card-body d-flex flex-column justify-content-between p-0 mt-2">
              <div>
                <h6 class="fw-bold text-dark mb-1">Mr. L.A.S. Kumarasinghe</h6>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill small fw-bold mb-3 d-inline-block">Center Manager</span>
              </div>
              <a href="<?php echo $this->asset('staff-details?id=0'); ?>" class="btn btn-sm btn-dark w-100 rounded-pill py-2 shadow-sm">View Full Profile</a>
            </div>
          </div>
        </div>
        <!-- Member 1 -->
        <div class="col-lg-3 col-sm-6">
          <div class="card h-100 text-center shadow-sm staff-card p-4">
            <div class="my-3">
              <div class="staff-img-wrapper">
                <img src="<?php echo $this->asset('images/staff/person.webp'); ?>" class="rounded-circle bg-white" alt="Ms. R.T.S. Ranasinge" style="width: 120px; height: 120px; object-fit: cover; padding: 4px;">
              </div>
            </div>
            <div class="card-body d-flex flex-column justify-content-between p-0 mt-2">
              <div>
                <h6 class="fw-bold text-dark mb-1">Ms. R.T.S. Ranasinge</h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small fw-bold mb-3 d-inline-block">English Faculty</span>
              </div>
              <a href="<?php echo $this->asset('staff-details?id=1'); ?>" class="btn btn-sm btn-dark w-100 rounded-pill py-2 shadow-sm">View Full Profile</a>
            </div>
          </div>
        </div>
        <!-- Member 2 -->
        <div class="col-lg-3 col-sm-6">
          <div class="card h-100 text-center shadow-sm staff-card p-4">
            <div class="my-3">
              <div class="staff-img-wrapper">
                <img src="<?php echo $this->asset('images/staff/person.webp'); ?>" class="rounded-circle bg-white" alt="Mr. T.K.C. Talpawila" style="width: 120px; height: 120px; object-fit: cover; padding: 4px;">
              </div>
            </div>
            <div class="card-body d-flex flex-column justify-content-between p-0 mt-2">
              <div>
                <h6 class="fw-bold text-dark mb-1">Mr. T.K.C. Talpawila</h6>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill small fw-bold mb-3 d-inline-block">Creative Design</span>
              </div>
              <a href="<?php echo $this->asset('staff-details?id=2'); ?>" class="btn btn-sm btn-dark w-100 rounded-pill py-2 shadow-sm">View Full Profile</a>
            </div>
          </div>
        </div>
        <!-- Member 3 -->
        <div class="col-lg-3 col-sm-6">
          <div class="card h-100 text-center shadow-sm staff-card p-4">
            <div class="my-3">
              <div class="staff-img-wrapper">
                <img src="<?php echo $this->asset('images/staff/person.webp'); ?>" class="rounded-circle bg-white" alt="Mr. B.D.P. Niranjan" style="width: 120px; height: 120px; object-fit: cover; padding: 4px;">
              </div>
            </div>
            <div class="card-body d-flex flex-column justify-content-between p-0 mt-2">
              <div>
                <h6 class="fw-bold text-dark mb-1">Mr. B.D.P. Niranjan</h6>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1 rounded-pill small fw-bold mb-3 d-inline-block">Web Engineering</span>
              </div>
              <a href="<?php echo $this->asset('staff-details?id=3'); ?>" class="btn btn-sm btn-dark w-100 rounded-pill py-2 shadow-sm">View Full Profile</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="py-5 text-center bg-dark text-white border-top">
    <div class="container">
      <p class="mb-2 text-white-50">Institute of Technology & Digital Learning Hub (ITDLH) — Kelaniya</p>
      <small class="text-secondary">&copy; <?php echo date('Y'); ?> B.D.P. Niranjan. All Rights Reserved. Powered by <a href="https://github.com/prageeth79/Mandakini-framework" target="_blank" class="text-white text-decoration-none">Mandakini Framework</a></small>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>
