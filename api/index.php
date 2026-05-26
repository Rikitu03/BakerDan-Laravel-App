<?php
/**
 * Laravel - A PHP Framework For Web Artisans
 * Entry point for Vercel serverless functions
 */

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Get the HTTP kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Capture the request (Vercel provides the request context)
$request = Illuminate\Http\Request::capture();

// Send the response
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);