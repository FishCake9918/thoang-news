<?php

use App\Controllers\ArticlePageController;
use App\Controllers\ArticleController;
use App\Core\Auth;
use App\Core\View;
use App\Models\ArticleModel;

ob_start();
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$comment_error = '';
$comments = [];
$nextArticle = null;
$prevArticle = null;

if ($id <= 0) {
    if (is_ajax_request()) {
        jsonResponse(['success' => false, 'message' => 'Bài viết không hợp lệ.'], 400);
    }

    header('Location: index.php');
    exit;
}

$articleModel = new ArticleModel($pdo);
$previewArticle = $articleModel->find($id);

if (!$previewArticle) {
    if (is_ajax_request()) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy bài viết.'], 404);
    }

    header('Location: index.php');
    exit;
}

$canPreview = Auth::check() && (
    Auth::isAdmin() ||
    (
        Auth::isWriter() &&
        (int)($previewArticle['author_id'] ?? 0) === Auth::id()
    )
);

if ($previewArticle['status'] !== 'Approved' && !$canPreview) {
    if (is_ajax_request()) {
        jsonResponse(['success' => false, 'message' => 'Bạn không có quyền xem bài viết này.'], 403);
    }

    header('Location: index.php');
    exit;
}

$articleController = new ArticleController($articleModel);
$comment_error = $previewArticle['status'] === 'Approved'
    ? ($articleController->handleCommentRequest($id) ?? $comment_error)
    : $comment_error;

$pageController = new ArticlePageController($articleModel);
$pageData = $pageController->safeShow($id, $canPreview);

if (!empty($pageData['error_status'])) {
    if (is_ajax_request()) {
        jsonResponse(['success' => false, 'message' => $pageData['error_message']], (int)$pageData['error_status']);
    }

    header('Location: index.php');
    exit;
}

$article = $pageData['article'];
$comments = $pageData['comments'];
$nextArticle = $pageData['nextArticle'];
$prevArticle = $pageData['prevArticle'];

$published_time = !empty($article['published_at']) ? $article['published_at'] : $article['created_at'];
$page_title = htmlspecialchars($article['title']) . ' - Thoáng.vn';

$back_url = 'index.php';
if (($_GET['from'] ?? '') === 'dashboard') {
    $back_url = 'dashboard.php';
}

View::render('articles.show', get_defined_vars());
