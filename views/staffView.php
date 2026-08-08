<?php
	$this->title = 'Staff';

	// Example staff list. Replace or populate from a model as needed.
	$staff = [
		['name' => 'Mr. L.A.S. Kumarasinghe', 'role' => 'Center Manager', 'bio' => 'Center Manager of ITDLH Kelaniya and the main instructor for MS Office Applications', 'photo' => $this->asset('images/staff/person.webp')],
		['name' => 'Ms. R.T.S. Ranasinge', 'role' => 'Instructor', 'bio' => 'Instructor of ITDLH Kelaniya and the main instructor for the \'Certification of Practical English\' course', 'photo' => $this->asset('images/staff/person.webp')],
		['name' => 'Mr. T.K.C. Talpawila', 'role' => 'Instructor', 'bio' => 'Instructor of ITDLH Kelaniya and the main instructor for the \'Certification of Graphic Design\' course', 'photo' => $this->asset('images/staff/person.webp')],
		['name' => 'Mr. B.D.P. Niranjan', 'role' => 'Instructor', 'bio' => 'Instructor of ITDLH Kelaniya and the main instructo for Prorgrammin and Web Design Coures', 'photo' => $this->asset('images/staff/person.webp')],
	];
?>

<div class="py-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<div>
			<h2 class="mb-0">Our Staff</h2>
			<p class="text-muted small mb-0">Experienced trainers at ITDLH Kelaniya</p>
		</div>
	</div>

	<div class="row g-4">
		<?php foreach ($staff as $i => $member): ?>
			<div class="col-12 col-sm-6 col-md-4 col-lg-3">
				<div class="card h-100 shadow-sm">
					<div class="ratio ratio-1x1">
						<img src="<?php echo $member['photo']; ?>" class="card-img-top object-fit-cover" alt="<?php echo htmlspecialchars($member['name']); ?>">
					</div>
					<div class="card-body d-flex flex-column">
						<h5 class="card-title mb-1"><?php echo htmlspecialchars($member['name']); ?></h5>
						<p class="text-primary small mb-2"><?php echo htmlspecialchars($member['role']); ?></p>
						<p class="card-text small text-muted mb-3"><?php echo htmlspecialchars($member['bio']); ?></p>
						<div class="mt-auto">
							<a href="<?php echo $this->asset('staff/details?id=' . $i); ?>" class="btn btn-sm btn-outline-primary">View profile</a>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<style>
	.object-fit-cover { object-fit: cover; width: 100%; height: 100%; }
	.ratio img { display: block; }
</style>

