<?php

use App\Controllers\WriterProfileController;
use App\Core\View;
use App\Models\ArticleModel;
use App\Models\UserModel;

session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isLoggedIn() || !in_array($_SESSION['role'] ?? '', ['user', 'admin'], true)) {
    $redirect = 'writer.php';
    if (isset($_GET['id'])) {
        $redirect .= '?id=' . urlencode((string)$_GET['id']);
    }
    header('Location: login.php?redirect=' . urlencode($redirect));
    exit;
}

$writer_id = (int)($_GET['id'] ?? 0);
$controller = new WriterProfileController(new UserModel($pdo), new ArticleModel($pdo));
$profileData = $controller->show($writer_id, isAdmin());
$writer = $profileData['writer'];
$articles = $profileData['articles'];
$error = $profileData['error'];
$page_title = $writer ? 'Hồ sơ writer - ' . $writer['username'] : 'Hồ sơ writer - Thoáng.vn';

View::render('writer.profile', get_defined_vars());
