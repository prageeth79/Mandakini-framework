<?php

use app\controllers\SiteController;
use app\controllers\AuthController;
use app\controllers\ItdlhController;

$app->router->get('/', [SiteController::class, 'home']);
$app->router->get('/home', [SiteController::class, 'home']);
$app->router->get('/contact', [SiteController::class, 'contact']);
$app->router->post('/contact', [SiteController::class, 'contact']);
$app->router->get('/login', [AuthController::class, 'login']);
$app->router->post('/login', [AuthController::class, 'login']);
$app->router->get('/about', [SiteController::class, 'about']);
$app->router->get('/staff', [SiteController::class, 'staff']);
// Staff detail page (shows a single staff member). Expects query param `id`.
$app->router->get('/staff-details', [SiteController::class, 'staffDetails']);
$app->router->get('/register', [AuthController::class, 'register']);
$app->router->post('/register', [AuthController::class, 'register']);
$app->router->get('/logout', [AuthController::class, 'logout']);
$app->router->get('/profile', [AuthController::class, 'profile']);

/*
$app->router->get('/debug-session', [\app\controllers\DebugController::class, 'session']);
// Debug helper to view DB tables
$app->router->get('/debug/tables', [\app\controllers\DebugController::class, 'tables']);
*/
// ITDLH landing page (controller)
$app->router->get('/itdlh', [ItdlhController::class, 'index']);

// Optional API endpoint listing courses
$app->router->get('/itdlh/courses', [ItdlhController::class, 'courses']);


// Course detail pages (per-course routes)
$app->router->get('/itdlh/course/mso', [ItdlhController::class, 'show_web_course']);
$app->router->get('/itdlh/course/web', [ItdlhController::class, 'show_web_course']);
$app->router->get('/itdlh/course/python', [ItdlhController::class, 'show_web_course']);
$app->router->get('/itdlh/course/java', [ItdlhController::class, 'show_web_course']);
$app->router->get('/itdlh/course/php', [ItdlhController::class, 'show_web_course']);
$app->router->get('/itdlh/course/graphic', [ItdlhController::class, 'show_web_course']);
$app->router->get('/itdlh/course/english', [ItdlhController::class, 'show_web_course']);

//$app->router->get('/itdlh/course', [ItdlhController::class, 'course']);

$app->router->get('/courses/add', [ItdlhController::class, 'add_courses']);
$app->router->post('/courses/add', [ItdlhController::class, 'add_courses']);
$app->router->get('/courses/category/add', [ItdlhController::class, 'add_category']);
$app->router->post('/courses/category/add', [ItdlhController::class, 'add_category']);

$app->router->get('/courses/edit', [ItdlhController::class, 'edit_courses']);
$app->router->post('/courses/edit', [ItdlhController::class, 'edit_courses']);

// Protected download routes for course content (serve via controller)
$app->router->get('/itdlh/course/mso/download', [ItdlhController::class, 'download']);
$app->router->get('/itdlh/course/web/download', [ItdlhController::class, 'download']);
$app->router->get('/itdlh/course/python/download', [ItdlhController::class, 'download']);
$app->router->get('/itdlh/course/java/download', [ItdlhController::class, 'download']);
$app->router->get('/itdlh/course/php/download', [ItdlhController::class, 'download']);
$app->router->get('/itdlh/course/graphic/download', [ItdlhController::class, 'download']);
$app->router->get('/itdlh/course/english/download', [ItdlhController::class, 'download']);

$app->router->get('/app', [ItdlhController::class, 'app_home']);