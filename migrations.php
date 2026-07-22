<?php
use app\core\Application;


require_once __DIR__ . '/vendor/autoload.php';

$config = [
    'db' => [
        'dsn' => 'mysql:host=localhost;port=3306;dbname=mandakini',
        'username' => 'root',
        'password' => ''
    ]
];

include_once __DIR__ . '/public/config.php';


$app = new Application(__DIR__, $config);
Application::$app = $app;

echo $app->db->applyMigrations();
