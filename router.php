<?php

// Router for PHP built-in server.
// Serve existing files directly; otherwise forward every request to index.php.
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$requested = __DIR__ . DIRECTORY_SEPARATOR . ltrim($uri, '/');
$extension = strtolower(pathinfo($requested, PATHINFO_EXTENSION));

// Allow direct serving of existing files. For PHP files, allow direct serving
// for API endpoints (e.g. /api/*.php) but route top-level legacy .php pages
// through the front controller.
$isApiPath = strpos($uri, '/api/') === 0;
if ($uri !== '/' && file_exists($requested) && !is_dir($requested) && ($extension !== 'php' || $isApiPath)) {
    return false;
}

// Ensure the front controller sees the current request path when using PHP built-in server.
$_GET['url'] = ltrim($uri, '/');
require_once __DIR__ . '/index.php';
