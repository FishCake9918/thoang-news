<?php

use App\Controllers\WriterDashboardController;
use App\Controllers\WriterArticleController;
use App\Core\View;
use App\Models\ArticleModel;
use App\Models\WriterDashboardModel;

session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'writer') {
    header('Location: login.php');
    exit;
}

$author_id = (int)($_SESSION['user_id'] ?? 0);
$error = '';

$writerArticleController = new WriterArticleController(new ArticleModel($pdo));
$error = $writerArticleController->handleDeleteRequest($author_id) ?? '';

$writerDashboard = new WriterDashboardController(new WriterDashboardModel($pdo));
$dashboardData = $writerDashboard->show($author_id);
$articles_by_status = $dashboardData['articles_by_status'];
$stats = $dashboardData['stats'];
$cat_views_data = $dashboardData['cat_views_data'];
$error = $error ?: $dashboardData['error'];

$page_title = 'Không gian viết bài - Thoáng.vn';

View::render('dashboard.writer', get_defined_vars());
