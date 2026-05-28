<?php

use App\Controllers\SearchController;
use App\Core\View;
use App\Models\SearchModel;

session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$page_title = 'Tìm kiếm - Thoáng.vn';
$active_nav = '';
$search_kw = trim($_GET['q'] ?? '');
$search_results = [];
$total = 0;

if ($search_kw !== '') {
    $controller = new SearchController(new SearchModel($pdo));
    $searchData = $controller->index($search_kw);
    $search_results = $searchData['search_results'];
    $total = $searchData['total'];
}

View::render('search.index', get_defined_vars());
