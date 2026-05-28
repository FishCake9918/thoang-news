<?php

use App\Controllers\HomeController;
use App\Core\View;
use App\Models\HomeModel;

session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$page_title = 'Trang chủ - Thoáng.vn';
$active_nav = $_GET['category'] ?? 'all';
$allowed_nav = ['hot', 'all', 'world', 'biz', 'tech', 'sport', 'life', 'edu', 'other'];

if (!in_array($active_nav, $allowed_nav, true)) {
    $active_nav = 'all';
}

$controller = new HomeController(new HomeModel($pdo));
$homeData = $controller->index();
$top_articles = $homeData['top_articles'];
$latest_articles = $homeData['latest_articles'];

View::render('home.index', get_defined_vars());
