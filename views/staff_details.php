<?php
	$this->title = $member ? htmlspecialchars($member['name']) . ' - Staff Profile' : 'Staff Member';
	
	/**
	 * Expects `$member` array with keys: name, role, bio, photo, email, responsibilities, expertise
	 */
	$member = $member ?? null;
	if (!$member) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		echo $this->render('_404');
		exit;
	}

	// Array දත්ත පරීක්ෂා කිරීම (විකල්ප - දත්ත නොමැති නම් හිස්ව පෙන්වීමට)
	$responsibilities = $member['responsibilities'] ?? [];
	$expertise = $member['expertise'] ?? [];
?>

<div class="py-5">
	<!-- Back Button -->
	<div class="mb-4">
		<a href="<?php echo $this->asset('staff'); ?>" class="btn btn-outline-secondary btn-sm rounded-pill">
			<i class="bi bi-arrow-left me-1"></i> Back to Staff Directory
		</a>
	</div>

	<!-- Main Profile Card Container -->
	<div class="card shadow border-0 overflow-hidden rounded-4">
		<div class="row g-0">
			
			<!-- Left Side Column: Photo and Quick Contact -->
			<div class="col-12 col-md-4 bg-dark text-white p-4 text-center d-flex flex-column justify-content-between">
				<div>
					<div class="mb-4 mt-2">
						<img src="<?php echo $this->asset($member['photo']); ?>" 
							 alt="<?php echo htmlspecialchars($member['name']); ?>" 
							 class="img-fluid rounded-circle border border-4 border-secondary shadow-sm"
							 style="width: 180px; height: 180px; object-fit: cover;">
					</div>
					<h2 class="fw-bold h4 mb-1"><?php echo htmlspecialchars($member['name']); ?></h2>
					<p class="text-info small text-uppercase tracking-wider fw-semibold mb-4"><?php echo htmlspecialchars($member['role']); ?></p>
					<hr class="border-secondary my-4">
				</div>

				<!-- Contact Details (Dynamic) -->
				<?php if (!empty($member['email'])): ?>
				<div class="text-start mb-3">
					<div class="d-flex align-items-center bg-secondary bg-opacity-25 rounded p-3">
						<i class="bi bi-envelope-fill text-info fs-5 me-3"></i>
						<div>
							<small class="text-secondary d-block text-uppercase font-monospace" style="font-size: 11px;">Email Address</small>
							<a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="text-white text-decoration-none small">
								<?php echo htmlspecialchars($member['email']); ?>
							</a>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Social Links Footer -->
				<div class="mt-3 d-flex justify-content-center gap-3 opacity-75">
					<span class="small text-secondary">Organization Staff Portal</span>
				</div>
			</div>

			<!-- Right Side Column: Dynamic Details -->
			<div class="col-12 col-md-8 p-4 p-lg-5 bg-white">
				
				<!-- Professional Biography -->
				<div class="mb-5">
					<h4 class="text-primary fw-bold mb-3 border-bottom pb-2">
						<i class="bi bi-person-lines-fill me-2"></i>Professional Biography
					</h4>
					<p class="text-muted lh-lg">
						<?php echo nl2br(htmlspecialchars($member['bio'])); ?>
					</p>
				</div>

				<!-- Job Responsibilities (Dynamic Loop) -->
				<?php if (!empty($responsibilities)): ?>
				<div class="mb-5">
					<h4 class="text-primary fw-bold mb-3 border-bottom pb-2">
						<i class="bi bi-briefcase-fill me-2"></i>Core Responsibilities
					</h4>
					<ul class="list-group list-group-flush">
						<?php foreach ($responsibilities as $item): ?>
							<li class="list-group-item border-0 px-0 py-2 text-muted">
								<i class="bi bi-check-circle-fill text-primary me-2"></i> 
								<?php echo htmlspecialchars($item); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>

				<!-- Areas of Expertise Tags (Dynamic Loop) -->
				<?php if (!empty($expertise)): ?>
				<div>
					<h4 class="text-primary fw-bold mb-3 border-bottom pb-2">
						<i class="bi bi-star-fill me-2"></i>Areas of Expertise
					</h4>
					<div class="d-flex flex-wrap gap-2 mt-2">
						<?php foreach ($expertise as $tag): ?>
							<span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-medium">
								<?php echo htmlspecialchars($tag); ?>
							</span>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

			</div>

		</div>
	</div>
</div>
