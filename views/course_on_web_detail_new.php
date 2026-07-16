<?php
/**
 * Course detail page template. Expects `$course` array with `title`, `description`, `image`.
 */
?>

<style>
  /* Premium Course Details Styles */
  .course-detail-img {
    border-radius: 24px !important;
    box-shadow: 0 20px 40px rgba(15, 32, 67, 0.12) !important;
    transition: transform 0.4s ease;
    object-fit: cover;
  }
  .course-detail-img:hover {
    transform: scale(1.02);
  }
  .meta-info-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,0.04);
  }
  .info-badge {
    padding: 8px 16px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
  }
  .badge-duration {
    background-color: #e0f2fe;
    color: #0369a1;
  }
  .badge-fee {
    background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);
    color: #ffffff;
  }
  .content-list-item {
    transition: all 0.2s ease;
    border-radius: 8px;
    margin-bottom: 6px;
  }
  .content-list-item:hover {
    background-color: #f1f5f9 !important;
    padding-left: 8px !important;
  }
  .certification-box {
    background-color: #f8fafc;
    border-left: 4px solid #3b82f6;
    border-radius: 4px 12px 12px 4px;
  }
</style>

<div class="py-5">
  <div class="container">
    <div class="row gx-5 align-items-center">
      
      <!-- LEFT COLUMN: COURSE IMAGE -->
      <div class="col-lg-5 mb-4 mb-lg-0">
        <div class="position-relative">
          <img src="<?php echo $course['image']; ?>" 
               class="img-fluid course-detail-img w-100" 
               alt="<?php echo htmlspecialchars($course['title']); ?>">
          
          <?php if (!empty($course['instructor'])): ?>
            <div class="position-absolute top-0 start-0 m-3 bg-dark bg-opacity-75 backdrop-blur text-white px-3 py-2 rounded-pill small shadow-sm">
              <i class="bi bi-person-fill text-info me-1"></i> Instructor: <strong><?php echo htmlspecialchars($course['instructor']); ?></strong>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <!-- RIGHT COLUMN: COURSE DETAILS & CTA -->
      <div class="col-lg-7">
        <span class="text-uppercase tracking-wider text-primary fw-bold small mb-2 d-block">Premium Professional Course</span>
        <h1 class="display-6 fw-bold text-dark mb-3"><?php echo htmlspecialchars($course['title']); ?></h1>
        
        <!-- Badges & Meta Info Row -->
        <div class="d-flex flex-wrap gap-2 mb-4">
          <?php if (!empty($course['duration'])): ?>
            <span class="info-badge badge-duration shadow-sm">
              <i class="bi bi-clock-fill me-2"></i><?php echo htmlspecialchars($course['duration']); ?>
            </span>
          <?php endif; ?>

          <?php if (!empty($course['fee'])): ?>
            <span class="info-badge badge-fee shadow-sm">
              <i class="bi bi-tags-fill me-2"></i>Fee: <?php echo htmlspecialchars($course['fee']); ?>
            </span>
          <?php endif; ?>
        </div>

        <!-- Course Description Lead -->
        <p class="lead text-secondary lh-lg mb-4"><?php echo htmlspecialchars($course['description']); ?></p>

        <!-- Dynamic Course Contents Accordion/List -->
        <?php if (!empty($course['contents']) && is_array($course['contents'])): ?>
          <div class="mb-4">
            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center">
              <i class="bi bi-book-half text-primary me-2"></i>What You Will Learn
            </h5>
            <ul class="list-group list-group-flush bg-transparent">
              <?php foreach ($course['contents'] as $item): ?>
                <li class="list-group-item bg-transparent border-0 ps-0 py-2 text-muted content-list-item d-flex align-items-start">
                  <i class="bi bi-patch-check-fill text-primary me-3 mt-1 fs-6"></i>
                  <span><?php echo htmlspecialchars($item); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <!-- Certification Info Box -->
        <?php if (!empty($course['certification'])): ?>
          <div class="certification-box p-3 mb-4">
            <p class="small text-secondary mb-0 d-flex align-items-center">
              <i class="bi bi-award-fill text-warning fs-4 me-3"></i>
              <span><strong>Certification:</strong> <?php echo htmlspecialchars($course['certification']); ?></span>
            </p>
          </div>
        <?php endif; ?>

        <!-- Interactive Action Buttons -->
        <div class="d-flex flex-wrap gap-2 mt-4">
          <a class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold shadow-sm" href="<?php echo $this->asset('register'); ?>">
            <i class="bi bi-lightning-charge-fill me-1"></i>Enroll Now
          </a>
          <a class="btn btn-outline-secondary btn-lg rounded-pill px-4 py-3 fw-medium" href="<?php echo $this->asset('contact'); ?>">
            <i class="bi bi-question-circle me-1"></i>Ask a Question
          </a>
          
          <?php
            // Protected syllabus download logic synchronized with Mandakini Framework
            $slug = $slug ?? null;
            if ($slug) {
              $appRoot = (class_exists('\app\\core\\Application') ? \app\core\Application::$ROOT_DIR : dirname(__DIR__, 3));
              $storagePdf = $appRoot . '/storage/course-content/' . $slug . '.pdf';
              $storageDir = $appRoot . '/storage/course-content/' . $slug;

              $hasPdf = file_exists($storagePdf);
              $hasDir = is_dir($storageDir) && (count(scandir($storageDir)) > 2);
              $loggedIn = !\app\core\Application::isGuest() || (\app\core\Application::$app->session->get('user') !== null);

              if (($hasPdf || $hasDir) && $loggedIn) {
                $downloadUrl = $this->asset('itdlh/course/' . $slug . '/download');
                echo '<a class="btn btn-outline-success btn-lg rounded-pill px-4 py-3" href="' . $downloadUrl . '"><i class="bi bi-download me-2"></i>Download Syllabus</a>';
              } elseif ($hasPdf || $hasDir) {
                echo '<a class="btn btn-outline-warning btn-lg rounded-pill px-4 py-3" href="' . $this->asset('login') . '"><i class="bi bi-lock-fill me-2"></i>Login to Download</a>';
              }
            }
          ?>
        </div>
      </div>

    </div>
  </div>
</div>
