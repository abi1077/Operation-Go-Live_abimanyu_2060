<?php

// Catch all errors and exceptions to print them directly on screen
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
    define('LARAVEL_START', microtime(true));

    // Setup temporary writable storage directories for Vercel Serverless environment
    $tmpDirs = [
        '/tmp/storage/framework/views',
        '/tmp/storage/framework/sessions',
        '/tmp/storage/framework/cache',
        '/tmp/storage/framework/cache/data',
        '/tmp/storage/logs',
        '/tmp/bootstrap/cache',
    ];

    foreach ($tmpDirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    // Inisialisasi SQLite database di /tmp jika belum ada
    $dbFile = '/tmp/database.sqlite';
    if (!file_exists($dbFile)) {
        touch($dbFile);
        if (file_exists(__DIR__ . '/../database/database.sqlite')) {
            @copy(__DIR__ . '/../database/database.sqlite', $dbFile);
        }
    }

    // Register Composer autoloader
    require __DIR__.'/../vendor/autoload.php';

    // Bootstrap Laravel
    $app = require_once __DIR__.'/../bootstrap/app.php';

    $app->useStoragePath('/tmp/storage');

    $request = Illuminate\Http\Request::capture();
    $response = $app->handleRequest($request);
    $response->send();
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<h1>Laravel Execution Error</h1>";
    echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>File:</b> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
