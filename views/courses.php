<section class="course-detail py-5">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-5">
        <div class="card border-light shadow-sm">
          <img src="<?php echo $this->asset(ltrim($course['image'], '/')); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
        </div>
      </div>
      <div class="col-lg-7">
        <div class="p-4 bg-white rounded-4 shadow-sm">
          <h1 class="mb-3"><?php echo htmlspecialchars($course['title']); ?></h1>
          <p class="lead text-secondary mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
          <div class="row g-3 mb-4">
            <div class="col-sm-6">
              <div class="border rounded-3 p-3 h-100">
                <strong>Fee</strong>
                <p class="mb-0"><?php echo htmlspecialchars($course['fee']); ?></p>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="border rounded-3 p-3 h-100">
                <strong>Duration</strong>
                <p class="mb-0"><?php echo htmlspecialchars($course['duration']); ?></p>
              </div>
            </div>
            <div class="col-sm-12">
              <div class="border rounded-3 p-3">
                <strong>Instructor</strong>
                <p class="mb-0"><?php echo htmlspecialchars($course['instructor']); ?></p>
              </div>
            </div>
          </div>
          <div class="mb-4">
            <h4>Course Content</h4>
            <ul class="list-group list-group-flush">
              <?php foreach ($course['contents'] as $content) { ?>
                <li class="list-group-item py-2"><?php echo htmlspecialchars($content); ?></li>
              <?php } ?>
            </ul>
          </div>
          <div class="p-3 bg-light rounded-3">
            <strong>Certification</strong>
            <p class="mb-0"><?php echo htmlspecialchars($course['certification']); ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>