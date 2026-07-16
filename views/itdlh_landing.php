<?php
/**
 * ITDLH Kelaniya — Landing page template
 * Replace Unsplash image URLs with local images if desired.
 * Expects no special variables.
 */
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ITDLH Kelaniya — Information Technology Courses</title>
  <link rel="stylesheet" href="<?php echo $this->asset('css/itdlh.css'); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body>
  <header class="hero">
    <div class="container hero-inner">
      <div class="hero-content">
        <h1>ITDLH Kelaniya</h1>
        <p class="lead">Practical IT courses for students — programming, web, databases and more.</p>
        <p>
          <a class="btn btn-primary btn-lg" href="<?php echo $this->asset('register'); ?>">Join a Course</a>
          <a class="btn btn-outline-light btn-lg" href="<?php echo $this->asset('contact'); ?>">Contact Us</a>
        </p>
      </div>
    </div> 
  </header>

  <section class="features py-5">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <img src="<?php echo $this->asset('images/features/programming.jpg'); ?>" class="card-img-top" alt="Programming">
            <div class="card-body">
              <h5 class="card-title">Software Development</h5>
              <p class="card-text">Hands-on programming courses: PHP, Python, JavaScript, and modern frameworks.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <img src="<?php echo $this->asset('images/features/web.jpg'); ?>" class="card-img-top" alt="Web">
            <div class="card-body">
              <h5 class="card-title">Web & Frontend</h5>
              <p class="card-text">Build modern responsive websites using HTML, CSS, Bootstrap and JS.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <img src="<?php echo $this->asset('images/features/datadevops.jpg'); ?>" class="card-img-top" alt="Databases & DevOps">
            <div class="card-body">
              <h5 class="card-title">Databases & DevOps</h5>
              <p class="card-text">Learn SQL, database design, and the basics of deployment and DevOps.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="about bg-light py-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h2>About ITDLH Kelaniya</h2>
          <p>We are an educational institute focused on delivering practical IT education to students who want to build careers in technology. Our instructors are industry-experienced and classes include labs and project work.</p>
          <ul>
            <li>Small class sizes</li>
            <li>Project-based learning</li>
            <li>Certifications & placement help</li>
          </ul>
        </div>
        <div class="col-md-6">
          <img src="<?php echo $this->asset('images/about.jpg'); ?>" class="img-fluid rounded shadow" alt="Students learning">
        </div>
      </div>
    </div>
  </section>

  <section class="courses py-5">
    <div class="container">
      <h2 class="mb-4">Our Courses</h2>
      <div class="row g-4">
    <?php foreach($webCourseList as $course) { ?>
      <div class="col-md-4">
          <div class="card h-100">
              <img src="<?php echo $this->asset($course->course_image_land); ?>" class="card-img-top" alt="<?php echo $course->course_name ?>">
            <div class="card-body">
              <h5 class="card-title"><?php echo $course->course_name ?></h5>
              <p class="card-text"><?php echo $course->course_description ?></p>
              <p><a class="btn btn-outline-primary" href="<?php echo $this->asset('itdlh/course/' . $course->course_id ); ?>">View Course</a></p>
            </div>
          </div>
        </div>
<?php } ?>


      <!--
        <div class="col-md-4">
          <div class="card h-100">
              <img src="<?php echo $this->asset('images/courses/mso.png'); ?>" class="card-img-top" alt="MS Office Applications">
            <div class="card-body">
              <h5 class="card-title">MS Office Applications</h5>
              <p class="card-text">Hands-on training in Word, Excel, PowerPoint and Outlook for workplace productivity.</p>
              <p><a class="btn btn-outline-primary" href="<?php echo $this->asset('itdlh/course/mso'); ?>">View Course</a></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="<?php echo $this->asset('images/courses/web.webp'); ?>" class="card-img-top" alt="Web Designing">
            <div class="card-body">
              <h5 class="card-title">Web Designing</h5>
              <p class="card-text">Learn HTML, CSS, responsive design and modern frontend tooling to build attractive websites.</p>
              <p><a class="btn btn-outline-primary" href="<?php echo $this->asset('itdlh/course/web'); ?>">View Course</a></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="<?php echo $this->asset('images/courses/python.webp'); ?>" class="card-img-top" alt="Programming with Python">
            <div class="card-body">
              <h5 class="card-title">Programming with Python</h5>
              <p class="card-text">Intro to Python programming, data structures, scripting and practical projects.</p>
              <p><a class="btn btn-outline-primary" href="<?php echo $this->asset('itdlh/course/python'); ?>">View Course</a></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="<?php echo $this->asset('images/courses/java.jpg'); ?>" class="card-img-top" alt="Programming with Java">
            <div class="card-body">
              <h5 class="card-title">Programming with Java</h5>
              <p class="card-text">Object-oriented programming, Java fundamentals and application development.</p>
              <p><a class="btn btn-outline-primary" href="<?php echo $this->asset('itdlh/course/java'); ?>">View Course</a></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="<?php echo $this->asset('images/courses/php.jpg'); ?>" class="card-img-top" alt="Programming with PHP">
            <div class="card-body">
              <h5 class="card-title">Programming with PHP</h5>
              <p class="card-text">Server-side web development with PHP: building dynamic websites and simple APIs.</p>
              <p><a class="btn btn-outline-primary" href="<?php echo $this->asset('itdlh/course/php'); ?>">View Course</a></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="<?php echo $this->asset('images/courses/graphic.jpg'); ?>" class="card-img-top" alt="Graphic Designing">
            <div class="card-body">
              <h5 class="card-title">Graphic Designing</h5>
              <p class="card-text">Design fundamentals, image editing and tools like Photoshop and Illustrator.</p>
              <p><a class="btn btn-outline-primary" href="<?php echo $this->asset('itdlh/course/graphic'); ?>">View Course</a></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="<?php echo $this->asset('images/courses/english.jpg'); ?>" class="card-img-top" alt="Practical English">
            <div class="card-body">
              <h5 class="card-title">Practical English</h5>
              <p class="card-text">Communicative English for students focusing on workplace and technical communication.</p>
              <p><a class="btn btn-outline-primary" href="<?php echo $this->asset('itdlh/course/english'); ?>">View Course</a></p>
            </div>
          </div>
        </div>
-->
      </div>
    </div>
  </section>

  <footer class="py-4 text-white bg-dark">
    <div class="container text-center">
      <p class="mb-1">&copy; <?php echo date('Y'); ?> ITDLH Kelaniya</p>
      <p class="small mb-0">Address:ITDLH Kelaniya, Makola North, Sri Lanka &nbsp; | &nbsp; Email: itdlhkelaniya@gmail.com</p>
      <p class="small mb-0">powered by <a href="#" target="_blank" class="text-white">Mandakini Framework</a></p>
    </div>
  </footer>

</body>
</html>
