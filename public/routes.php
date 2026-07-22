<?php

use app\controllers\SiteController;
use app\controllers\AuthController;

$app->router->get('/', [SiteController::class, 'home']);
$app->router->get('/home', [SiteController::class, 'home']);
$app->router->get('/contact', [SiteController::class, 'contact']);
$app->router->post('/contact', [SiteController::class, 'contact']);
$app->router->get('/login', [AuthController::class, 'login']);
$app->router->post('/login', [AuthController::class, 'login']);
$app->router->get('/about', [SiteController::class, 'about']);
$app->router->get('/staff', [SiteController::class, 'staff']);

$app->router->get('/register', [AuthController::class, 'register']);
$app->router->post('/register', [AuthController::class, 'register']);
$app->router->get('/logout', [AuthController::class, 'logout']);
$app->router->get('/profile', [AuthController::class, 'profile']);


$app->router->get('/course/mso', [SiteController::class, 'courses']);
$app->router->get('/course/web', [SiteController::class, 'courses']);
$app->router->get('/course/python', [SiteController::class, 'courses']);
$app->router->get('/course/java', [SiteController::class, 'courses']);
$app->router->get('/course/php', [SiteController::class, 'courses']);
$app->router->get('/course/graphic', [SiteController::class, 'courses']);
$app->router->get('/course/english', [SiteController::class, 'courses']);


/*
$app->router->get('/debug-session', [\app\controllers\DebugController::class, 'session']);
// Debug helper to view DB tables
$app->router->get('/debug/tables', [\app\controllers\DebugController::class, 'tables']);
*/
