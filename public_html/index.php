<?php
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));
define('LARAVEL_ROOT', __DIR__ . '/../examminer'); // <-- your Laravel root outside public_html
require __DIR__ . '/bootstrap_errors.php';


// Maintenance
if (file_exists($maintenance = LARAVEL_ROOT . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoload
require LARAVEL_ROOT . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once LARAVEL_ROOT . '/bootstrap/app.php';

// IMPORTANT: Make Laravel use public_html as the public path
if (method_exists($app, 'usePublicPath')) {
    $app->usePublicPath(__DIR__);
} else {
    // fallback for older Laravel
    $app->bind('path.public', fn () => __DIR__);
}

// Handle request
$app->handleRequest(Request::capture());
