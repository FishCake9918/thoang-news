<?php
// Bridge file — forward mọi request về root index.php
$_GET['url'] = ltrim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
require_once __DIR__ . '/../index.php';