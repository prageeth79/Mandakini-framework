<?php
use app\core\Application;


require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


$config = [
    'db' => [
        'dsn' => $_ENV['DB_DSN'] ?? 'mysql:host=localhost;port=3306;dbname=mandakini',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? ''
    ]
];

include_once __DIR__ . '/public/config.php';


$app = new Application(__DIR__, $config);
Application::$app = $app;

echo $app->db->applyMigrations();
