<?php
//require __DIR__ . '/../vendor/autoload.php';





header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="demo.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

echo $pdf;
exit;