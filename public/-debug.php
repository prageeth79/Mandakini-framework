
<?php
echo '<pre>'; print_r([
  'REQUEST_URI'=>$_SERVER['REQUEST_URI'] ?? null,
  'SCRIPT_NAME'=>$_SERVER['SCRIPT_NAME'] ?? null,
  'PHP_SELF'=>$_SERVER['PHP_SELF'] ?? null,
  'DOCUMENT_ROOT'=>$_SERVER['DOCUMENT_ROOT'] ?? null,
]); echo '</pre>';