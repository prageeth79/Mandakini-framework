<?php
/*
require __DIR__ . '/vendor/autoload.php';

var_dump(class_exists(\Dompdf\Dompdf::class));

$dompdf = new \Dompdf\Dompdf();

$dompdf->loadHtml('<h1>Hello Dompdf</h1>');
$dompdf->setPaper('A4');
$dompdf->render();

file_put_contents(__DIR__ . '/test.pdf', $dompdf->output());

echo 'PDF created successfully.';

*/


$autoload = __DIR__ . '/vendor/autoload.php';

echo "Autoload: $autoload\n";
echo "Autoload exists: ";
var_dump(file_exists($autoload));

require $autoload;

echo "Dompdf directory exists: ";
var_dump(is_dir(__DIR__ . '/vendor/dompdf/dompdf'));

echo "Dompdf class: ";
var_dump(class_exists(\Dompdf\Dompdf::class));

echo "\nComposer autoload files:\n";

foreach (spl_autoload_functions() ?: [] as $loader) {
    var_dump($loader);
}