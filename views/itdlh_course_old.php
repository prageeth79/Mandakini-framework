<?php
/**
 * Course detail page template. Expects `$course` array with `title`, `description`, `image`.
 */
?>
<div class="py-5">
  <div class="container">
    <div class="row gx-5 align-items-center">
      <div class="col-md-6">
        <div class="card shadow-sm border-0">
          <img src="<?php echo $course['image']; ?>" class="card-img-top rounded-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
        </div>
      </div>
      <div class="col-md-6">
        <h1 class="mb-2"><?php echo htmlspecialchars($course['title']); ?></h1>
        <?php if (!empty($course['instructor'])): ?>
          <p class="text-muted mb-1">Instructor: <strong><?php echo htmlspecialchars($course['instructor']); ?></strong></p>
        <?php endif; ?>
        <?php if (!empty($course['duration'])): ?>
          <p><span class="badge bg-primary me-2"><?php echo htmlspecialchars($course['duration']); ?></span></p>
        <?php endif; ?>
        <p class="lead"><?php echo htmlspecialchars($course['description']); ?></p>

        <?php if (!empty($course['contents']) && is_array($course['contents'])): ?>
          <h5 class="mt-4">Course Contents</h5>
          <ul class="list-group list-group-flush mb-3">
            <?php foreach ($course['contents'] as $item): ?>
              <li class="list-group-item bg-transparent border-0 ps-0 py-1">• <?php echo htmlspecialchars($item); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if (!empty($course['certification'])): ?>
          <p class="small text-muted"><?php echo htmlspecialchars($course['certification']); ?></p>
        <?php endif; ?>

        <div class="mt-3 d-flex gap-2">
          <a class="btn btn-primary btn-lg" href="<?php echo $this->asset('register'); ?>">Join Course</a>
          <a class="btn btn-outline-secondary btn-lg" href="<?php echo $this->asset('contact'); ?>">Ask a Question</a>
          <?php
            // Protected download: only show link to authenticated users and
            // only if a protected copy exists under storage/course-content/{slug}.pdf
            $slug = $slug ?? null;
            if ($slug) {
              $appRoot = (class_exists('\app\\core\\Application') ? \app\core\Application::$ROOT_DIR : dirname(__DIR__, 3));
              $storagePdf = $appRoot . '/storage/course-content/' . $slug . '.pdf';
              $storageDir = $appRoot . '/storage/course-content/' . $slug;

              // Determine availability: single PDF or a directory with multiple files
              $hasPdf = file_exists($storagePdf);
              $hasDir = is_dir($storageDir) && (count(scandir($storageDir)) > 2);

              // Determine logged-in state: prefer Application::isGuest(),
              // but also accept presence of a session 'user' key if hydration fails.
              $loggedIn = !\app\core\Application::isGuest() || (\app\core\Application::$app->session->get('user') !== null);

              // Only show link to the controller download route for logged-in users
              if (($hasPdf || $hasDir) && $loggedIn) {
                $downloadUrl = $this->asset('itdlh/course/' . $slug . '/download');
                echo '<a class="btn btn-outline-success btn-lg" href="' . $downloadUrl . '">Download Syllabus</a>';
              } elseif ($hasPdf || $hasDir) {
                // Prompt guest to login to download
                echo '<a class="btn btn-outline-success btn-lg" href="' . $this->asset('login') . '">Login to download</a>';
              }
            }
            ?>
        </div>
      </div>
    </div>
  </div>
</div>
