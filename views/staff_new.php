<?php
	$this->title = 'Our Staff Faculty - ITDLH Kelaniya';

	// Upgraded, professional, and detailed staff list array
	$staff = [
		[
			'name' => 'Mr. L.A.S. Kumarasinghe', 
			'role' => 'Center Manager', 
			'bio' => 'Bringing over a decade of administrative leadership. He supervises daily campus operations and acts as the lead instructor for MS Office Applications.', 
			'photo' => $this->asset('images/staff/person.webp'),
			'badge' => 'bg-primary-subtle text-primary border-primary-subtle'
		],
		[
			'name' => 'Ms. R.T.S. Ranasinge', 
			'role' => 'Instructor', 
			'bio' => 'An experienced Language Instructor pioneering the communicative teaching methodologies for the \'Certification of Practical English\' course.', 
			'photo' => $this->asset('images/staff/person.webp'),
			'badge' => 'bg-success-subtle text-success border-success-subtle'
		],
		[
			'name' => 'Mr. T.K.C. Talpawila', 
			'role' => 'Instructor', 
			'bio' => 'Functions as the Chief Creative Instructor, guiding students through the core principles of visual storytelling and Graphic Designing.', 
			'photo' => $this->asset('images/staff/person.webp'),
			'badge' => 'bg-warning-subtle text-warning border-warning-subtle'
		],
		[
			'name' => 'Mr. B.D.P. Niranjan', 
			'role' => 'Instructor', 
			'bio' => 'A highly technical Academic Instructor directing front-end structural design layouts and back-end Software Programming paths.', 
			'photo' => $this->asset('images/staff/person.webp'),
			'badge' => 'bg-info-subtle text-info border-info-subtle'
		],
	];
?>

<style>
	/* Interactive Premium Card Styles */
	.staff-directory-card {
		border: none !important;
		border-radius: 20px !important;
		overflow: hidden;
		transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
		background: #ffffff;
	}
	
	.staff-directory-card:hover {
		transform: translateY(-10px);
		box-shadow: 0 20px 40px rgba(15, 32, 67, 0.1) !important;
	}

	.staff-img-zoom-container {
		overflow: hidden;
		position: relative;
		border-bottom: 4px solid #f1f5f9;
	}

	.staff-directory-card img {
		transition: transform 0.5s ease;
	}

	.staff-directory-card:hover img {
		transform: scale(1.08);
	}

	.custom-section-title {
		font-weight: 800;
		color: #0f2043;
		letter-spacing: -0.5px;
	}

	.btn-view-profile {
		border-radius: 50px !important;
		font-weight: 600;
		padding: 8px 20px;
		transition: all 0.3s ease;
	}
	
	.staff-directory-card:hover .btn-view-profile {
		background-color: #0f2043 !important;
		border-color: #0f2043 !important;
		color: #ffffff !important;
		box-shadow: 0 4px 12px rgba(15, 32, 67, 0.2);
	}
</style>
<div class="py-5">
	<!-- Header Section with Decorative Line -->
	<div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3">
		<div>
			<h2 class="custom-section-title mb-1"><i class="bi bi-people-fill me-2 text-primary"></i>Our Professional Faculty</h2>
			<p class="text-muted mb-0">Meet the industry-experienced trainers and mentors at ITDLH Kelaniya</p>
		</div>
	</div>

	<!-- Interactive Staff Grid -->
	<div class="row g-4">
		<?php foreach ($staff as $i => $member): ?>
			<div class="col-12 col-sm-6 col-md-4 col-lg-3">
				<div class="card h-100 shadow-sm staff-directory-card">
					
					<!-- Image Zoom Wrapper Container -->
					<div class="staff-img-zoom-container ratio ratio-1x1">
						<img src="<?php echo $member['photo']; ?>" 
							 class="card-img-top object-fit-cover" 
							 alt="<?php echo htmlspecialchars($member['name']); ?>"
							 loading="lazy">
					</div>
					
					<!-- Card Data Body Area -->
					<div class="card-body d-flex flex-column p-4">
						<h5 class="fw-bold text-dark h6 mb-1"><?php echo htmlspecialchars($member['name']); ?></h5>
						
						<!-- Dynamic Structural Badge -->
						<div class="mb-3">
							<span class="badge border px-3 py-1 rounded-pill small fw-bold <?php echo $member['badge']; ?>">
								<?php echo htmlspecialchars($member['role']); ?>
							</span>
						</div>
						
						<p class="card-text small text-secondary lh-base mb-4">
							<?php echo htmlspecialchars($member['bio']); ?>
						</p>
						
						<!-- Action Profile Redirection Button -->
						<div class="mt-auto">
							<a href="<?php echo $this->asset('staff-details?id=' . $i); ?>" class="btn btn-sm btn-outline-primary btn-view-profile w-100">
								<i class="bi bi-file-earmark-person me-1"></i> View Full Profile
							</a>
						</div>
					</div>

				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
