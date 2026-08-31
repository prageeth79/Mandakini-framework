<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
use app\core\Application;
use app\controllers\SiteController;
use app\controllers\AuthController;
use app\controllers\ItdlhController;

/*
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


$config = [
    'userClass' => \app\models\User::class,
    'appName' => $_ENV['DEFAULT_APP_NAME'] ?? 'Mandakini',
    'db' => [
        'dsn' => $_ENV['DB_DSN'] ?? 'mysql:host=localhost;port=3306;dbname=mandakini',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? ''
    ]
];
*/
include_once __DIR__ . '/config.php';

if($config['ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}



$app = new Application(dirname(__DIR__), $config);
Application::$app = $app;

include_once __DIR__ . '/routes.php';

echo $app->run();
