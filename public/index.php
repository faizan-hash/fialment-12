<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Correctly determine the application base path
$rootPath = realpath(__DIR__ . '/..');

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $rootPath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $rootPath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $rootPath . '/bootstrap/app.php';

// Make Laravel aware of the base URL for proper URL generation
// This is crucial for generating correct URLs in emails and redirects
$request = Request::capture();
$app->handleRequest($request);
