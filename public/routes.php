<?php

use app\controllers\SiteController;
use app\controllers\AuthController;

$app->router->get('/', [SiteController::class, 'home']);
$app->router->get('/home', [SiteController::class, 'home']);
$app->router->get('/contact', [SiteController::class, 'contact']);
$app->router->post('/contact', [SiteController::class, 'contact']);
$app->router->get('/login', [SiteController::class, 'login']);
$app->router->get('/about', [SiteController::class, 'about']);
$app->router->get('/staff', [SiteController::class, 'staff']);

$app->router->get('/register', [SiteController::class, 'register']);
$app->router->get('/logout', [AuthController::class, 'logout']);
$app->router->get('/profile', [AuthController::class, 'profile']);

$app->router->get('/course/{id}', [SiteController::class, 'courses']);
