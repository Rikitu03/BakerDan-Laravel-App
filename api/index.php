<?php
/**
 * Laravel - A PHP Framework For Web Artisans
 * Entry point for Vercel serverless functions
 */

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the Laravel application
/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__ . '/../bootstrap/app.php';

// Capture the request and handle it
$app->handleRequest(Illuminate\Http\Request::capture());
