<?php
	$this->title = 'Staff Member';
	/**
	 * Expects `$member` array with keys: name, role, bio, photo
	 */
	$member = $member ?? null;
	if (!$member) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		echo $this->render('_404');
		exit;
	}
?>

<div class="py-4">
	<div class="row g-4">
		<div class="col-12 col-md-4">
			<img src="<?php echo $this->asset($member['photo']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="img-fluid rounded shadow-sm">
		</div>
		<div class="col-12 col-md-8">
			<h2><?php echo htmlspecialchars($member['name']); ?></h2>
			<p class="text-primary small mb-2"><?php echo htmlspecialchars($member['role']); ?></p>
			<p class="lead text-muted"><?php echo htmlspecialchars($member['bio']); ?></p>
			<!-- Placeholder for additional details -->
			 <div class="mb-5">
                    <h4 class="text-primary fw-bold mb-3 border-bottom pb-2">
                        <i class="bi bi-person-lines-fill me-2"></i>Professional Biography
                    </h4>
                    <p class="text-muted lh-lg">
                        Sarah has over 10 years of experience in talent management, organizational development, and employee relations. Before joining our organization, she led HR teams in the tech and finance sectors, optimizing recruitment pipelines and fostering inclusive workplace cultures.
                    </p>
                </div>

                <!-- Job Responsibilities Section -->
                <div class="mb-5">
                    <h4 class="text-primary fw-bold mb-3 border-bottom pb-2">
                        <i class="bi bi-briefcase-fill me-2"></i>Core Responsibilities
                    </h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item border-0 px-0 py-2 text-muted">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i> Overseeing global talent acquisition and onboarding programs.
                        </li>
                        <li class="list-group-item border-0 px-0 py-2 text-muted">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i> Developing and implementing employee wellness initiatives.
                        </li>
                        <li class="list-group-item border-0 px-0 py-2 text-muted">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i> Managing conflict resolution and internal policy compliance.
                        </li>
                    </ul>
                </div>

                <!-- Areas of Expertise Tags -->
                <div>
                    <h4 class="text-primary fw-bold mb-3 border-bottom pb-2">
                        <i class="bi bi-star-fill me-2"></i>Areas of Expertise
                    </h4>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium">Strategic Planning</span>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium">Employee Engagement</span>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium">Conflict Resolution</span>
                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium">HR Analytics</span>
                    </div>
                </div>
			<p class="mt-3"><a href="<?php echo $this->asset('staff'); ?>" class="btn btn-outline-secondary">Back to staff</a></p>
		</div>
	</div>
</div>
