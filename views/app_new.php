<?php
	$this->title = 'App Dashboard - ITDLH Kelaniya';
	
	// Determine if the current user has admin access (Optional helper)
	//$isAdmin = !Application::isGuest() && (method_exists('Application', 'isAdmin') ? Application::isAdmin() : true);
?>

<style>
	/* Premium Dashboard Card Custom Style */
	.app-launcher-card {
		border: 1px solid rgba(0, 0, 0, 0.04) !important;
		border-radius: 20px !important;
		background: #ffffff;
		transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
		text-decoration: none !important;
		overflow: hidden;
		position: relative;
	}
	
	.app-launcher-card:hover {
		transform: translateY(-8px);
		box-shadow: 0 20px 40px rgba(30, 60, 114, 0.1) !important;
		border-color: rgba(30, 60, 114, 0.1) !important;
	}

	.app-icon-box {
		width: 65px;
		height: 65px;
		border-radius: 16px;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 28px;
		transition: all 0.3s ease;
		margin-bottom: 20px;
	}

	/* Card Specific Hover Glow Effects */
	.app-launcher-card:hover .bg-primary-subtle { background-color: #1e3c72 !important; color: #ffffff !important; }
	.app-launcher-card:hover .bg-success-subtle { background-color: #198754 !important; color: #ffffff !important; }
	.app-launcher-card:hover .bg-warning-subtle { background-color: #ffc107 !important; color: #000000 !important; }
	.app-launcher-card:hover .bg-danger-subtle { background-color: #dc3545 !important; color: #ffffff !important; }
	.app-launcher-card:hover .bg-info-subtle { background-color: #0dcaf0 !important; color: #000000 !important; }
	.app-launcher-card:hover .bg-secondary-subtle { background-color: #6c757d !important; color: #ffffff !important; }

	.dashboard-title {
		font-weight: 800;
		color: #0f2043;
		letter-spacing: -0.5px;
	}
</style>

<div class="py-4">
	<!-- Dashboard Header -->
	<div class="mb-5 border-bottom pb-3">
		<h2 class="dashboard-title mb-1"><i class="bi bi-grid-1x2-fill me-2 text-primary"></i>Application Console</h2>
		<p class="text-muted mb-0">Welcome back! Access all integrated hub modules and management software below.</p>
	</div>
	<!-- Interactive Launcher Grid -->
	<div class="row g-4">
		
		<!-- 1. COURSES MODULE -->
		<div class="col-12 col-sm-6 col-md-4">
			<a href="<?php echo $this->asset('courses/add'); ?>" class="card h-100 p-4 app-launcher-card">
				<div class="app-icon-box bg-primary-subtle text-primary">
					<i class="bi bi-journal-bookmark-fill"></i>
				</div>
				<h5 class="fw-bold text-dark h6 mb-1">Course Catalog</h5>
				<p class="text-secondary small mb-0">Browse active certifications, syllabus outlines, and enrollment gates.</p>
			</a>
		</div>

		<!-- 2. STUDENTS PORTAL -->
		<div class="col-12 col-sm-6 col-md-4">
			<a href="<?php echo $this->asset('students'); ?>" class="card h-100 p-4 app-launcher-card">
				<div class="app-icon-box bg-success-subtle text-success">
					<i class="bi bi-mortarboard-fill"></i>
				</div>
				<h5 class="fw-bold text-dark h6 mb-1">Student Profiles</h5>
				<p class="text-secondary small mb-0">Manage student directories, active batches, and individual learning progress.</p>
			</a>
		</div>

		<!-- 3. STAFF FACULTY -->
		<div class="col-12 col-sm-6 col-md-4">
			<a href="<?php echo $this->asset('staff'); ?>" class="card h-100 p-4 app-launcher-card">
				<div class="app-icon-box bg-info-subtle text-info">
					<i class="bi bi-people-fill"></i>
				</div>
				<h5 class="fw-bold text-dark h6 mb-1">Staff Directory</h5>
				<p class="text-secondary small mb-0">View professional lecturer biographies, expertise arrays, and rosters.</p>
			</a>
		</div>

		<!-- 4. ACADEMIC RESULTS -->
		<div class="col-12 col-sm-6 col-md-4">
			<a href="<?php echo $this->asset('results'); ?>" class="card h-100 p-4 app-launcher-card">
				<div class="app-icon-box bg-warning-subtle text-warning">
					<i class="bi bi-file-earmark-bar-graph-fill"></i>
				</div>
				<h5 class="fw-bold text-dark h6 mb-1">Examination Results</h5>
				<p class="text-secondary small mb-0">Publish or check transcript matrices, project marks, and final classifications.</p>
			</a>
		</div>

		<!-- 5. ADVANCED ADMIN CONTROL -->
		<?php if ($isAdmin): ?>
			<div class="col-12 col-sm-6 col-md-4">
				<a href="<?php echo $this->asset('admin'); ?>" class="card h-100 p-4 app-launcher-card">
					<div class="app-icon-box bg-danger-subtle text-danger">
						<i class="bi bi-shield-lock-fill"></i>
					</div>
					<h5 class="fw-bold text-dark h6 mb-1">Admin Central</h5>
					<p class="text-secondary small mb-0">Configure master database parameters, security tokens, and user hydrations.</p>
				</a>
			</div>
		<?php endif; ?>

		<!-- 6. SETTINGS / UTILITIES -->
		<div class="col-12 col-sm-6 col-md-4">
			<a href="<?php echo $this->asset('profile'); ?>" class="card h-100 p-4 app-launcher-card">
				<div class="app-icon-box bg-secondary-subtle text-secondary">
					<i class="bi bi-gear-wide-connected"></i>
				</div>
				<h5 class="fw-bold text-dark h6 mb-1">Account Configuration</h5>
				<p class="text-secondary small mb-0">Modify personal identity keys, core passwords, and active session bounds.</p>
			</a>
		</div>

	</div>
</div>
