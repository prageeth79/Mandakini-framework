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
    <title><?php echo $this->title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo $this->asset('css/itdlh.css'); ?>">
  </head>
  <body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <a class="navbar-brand" href="/">ITDLH Kelaniya</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $this->asset(''); ?>">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $this->asset('about'); ?>">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $this->asset('contact'); ?>">Contact</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $this->asset('staff'); ?>">staff</a>
            </li>
            <?php if (!Application::isGuest()): ?>
              <li class="nav-item">
                <a class="nav-link" href="<?php echo $this->asset('app'); ?>">App</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="<?php echo $this->asset('admin'); ?>">Admin</a>
              </li>
            <?php endif; ?>
          </ul>

          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <?php if (Application::isGuest()): ?>
              <li class="nav-item"><a class="nav-link" href="<?php echo $this->asset('register'); ?>">Register</a></li>
              <li class="nav-item"><a class="nav-link" href="<?php echo $this->asset('login'); ?>">Login</a></li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link" href="<?php echo $this->asset('profile'); ?>">Profile</a></li>
              <li class="nav-item"><a class="nav-link" href="<?php echo $this->asset('logout'); ?>">Welcome <?php echo Application::$app->user->getDisplayName(); ?>(Logout)</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container">
      <?php $flash = Application::$app->session->getFlash('success'); ?>
      <?php if ($flash): ?>
        <div class="alert alert-success" role="alert">
          <?php echo $flash; ?>
        </div>
      <?php endif; ?>
        {{content}}
    </div>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  </body>
</html>