<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

require 'requirements.php';

if (!file_exists(__DIR__ . '/../.env')) {
    rename('../.env.example', '../.env');
}

/** Redirect for URLs with 'public' in it */
$url = $_SERVER['REQUEST_URI'];
if (strpos($url, '/public/') !== false) {
    $url = str_replace('public/', '', $url);
    header('location: ' . $url);
    exit();
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->handleRequest(Request::capture());
