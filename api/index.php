<?php
// Định nghĩa root path tuyệt đối trước khi load index.php
define('APP_ROOT', dirname(__DIR__));

$_GET['url'] = ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
require_once APP_ROOT . '/index.php';