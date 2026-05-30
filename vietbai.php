<?php

use App\Controllers\WriterFormController;
use App\Core\View;
use App\Models\ArticleModel;
use App\Models\CategoryModel;

session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'writer') {
    header('Location: index.php');
    exit;
}

$author_id = (int)$_SESSION['user_id'];
$article_id = (int)($_GET['id'] ?? 0);
$controller = new WriterFormController(
    new ArticleModel($pdo),
    new CategoryModel($pdo)
);
$formData = $controller->formData($article_id, $author_id);

if (!empty($formData['redirect'])) {
    header('Location: ' . $formData['redirect']);
    exit;
}

$article = $formData['article'];
$categories = $formData['categories'];
$category_tree = $formData['category_tree'];
$page_title = ($article_id > 0 ? 'Chỉnh sửa bài viết' : 'Viết bài mới') . ' - Thoáng.vn';

View::render('writer.form', get_defined_vars());
