<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));













if (isset($_GET['clear_route_cache'])) {
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    $cacheFile = __DIR__.'/../bootstrap/cache/routes-v7.php';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
        echo "Route cache cleared and OPCache reset successfully!";
    } else {
        echo "No route cache found, but OPCache has been reset.";
    }
    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
