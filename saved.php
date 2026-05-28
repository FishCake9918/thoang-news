<?php

use App\Controllers\SavedController;
use App\Core\Auth;
use App\Core\View;
use App\Models\SavedArticleModel;

session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$page_title = 'Đã lưu - Thoáng.vn';
$active_nav = 'saved';
$page_css = 'stylesheets/style.css';
$filter_cat = $_GET['cat'] ?? 'all';
$search_kw = trim($_GET['q'] ?? '');
$is_logged_in = Auth::check();
$saved_articles = [];
$total = 0;

if ($is_logged_in) {
    $controller = new SavedController(new SavedArticleModel($pdo));
    $savedData = $controller->index(Auth::id(), $filter_cat, $search_kw);
    $saved_articles = $savedData['saved_articles'];
    $total = $savedData['total'];
}

View::render('saved.index', get_defined_vars());
