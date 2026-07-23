<?php

// 1. Prepare writable directories in /tmp for Vercel Serverless environment
$storagePath = '/tmp/storage';
$dirs = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
    '/tmp/bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// 2. Set environment variables for serverless cache paths
putenv('APP_SERVICES_CACHE=' . '/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=' . '/tmp/bootstrap/cache/packages.php');
putenv('APP_CONFIG_CACHE=' . '/tmp/bootstrap/cache/config.php');
putenv('APP_ROUTES_CACHE=' . '/tmp/bootstrap/cache/routes.php');
putenv('VIEW_COMPILED_PATH=' . $storagePath . '/framework/views');

// 3. Require the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// 4. Boot Laravel application and bind storage path to /tmp
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath($storagePath);

// 5. Handle HTTP request
$request = Illuminate\Http\Request::capture();
$response = $app->handleRequest($request);
$response->send();
