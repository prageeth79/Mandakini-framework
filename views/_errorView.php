<?php
/** @var \Exception $exception */

?>
<h1>Error</h1>
<p><?php echo $exception->getCode(); ?>: <?php echo $exception->getMessage(); ?></p>