<?php

use App\Controllers\AdminDashboardController;
use App\Controllers\AdminController;
use App\Core\View;
use App\Models\AdminModel;
use App\Models\AdminDashboardModel;

session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = '';
$adminController = new AdminController(new AdminModel($pdo));
$error = $adminController->handleRequest() ?? '';

$adminDashboard = new AdminDashboardController(new AdminDashboardModel($pdo));
$dashboardData = $adminDashboard->show();
$users = $dashboardData['users'];
$articles = $dashboardData['articles'];
$feedbacks = $dashboardData['feedbacks'];
$fcnt = $dashboardData['fcnt'];
$stats = $dashboardData['stats'];
$cat_stats = $dashboardData['cat_stats'];
$top_articles = $dashboardData['top_articles'];
$writer_stats = $dashboardData['writer_stats'];
$top_bookmarked = $dashboardData['top_bookmarked'];
$recent_comments = $dashboardData['recent_comments'];
$error = $error ?: $dashboardData['error'];
$engagement_rate = $stats['total_views'] > 0
    ? round(($stats['total_bookmarks'] / $stats['total_views']) * 100, 2)
    : 0;

$status_labels = [
    'pending' => 'Chờ xử lý',
    'replied' => 'Đã trả lời',
    'done' => 'Hoàn tất'
];

$page_title = 'Bảng quản trị Admin - Thoáng.vn';

View::render('dashboard.admin', get_defined_vars());
