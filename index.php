<?php

use App\Controllers\AboutController;
use App\Controllers\AccountController;
use App\Controllers\AdminController;
use App\Controllers\AdminDashboardController;
use App\Controllers\ArticleController;
use App\Controllers\ArticlePageController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\PasswordController;
use App\Controllers\SavedController;
use App\Controllers\SearchController;
use App\Controllers\VerificationController;
use App\Controllers\WeatherController;
use App\Controllers\WriterArticleController;
use App\Controllers\WriterDashboardController;
use App\Controllers\WriterFormController;
use App\Controllers\WriterProfileController;
use App\Core\Router;
use App\Core\View;
use App\Models\AboutSectionModel;
use App\Models\AdminDashboardModel;
use App\Models\AdminModel;
use App\Models\ArticleModel;
use App\Models\CategoryModel;
use App\Models\HomeModel;
use App\Models\SavedArticleModel;
use App\Models\SearchModel;
use App\Models\UserModel;
use App\Models\WriterDashboardModel;

session_start();
$root = defined('APP_ROOT') ? APP_ROOT : __DIR__;
require_once $root . '/config/db.php';
require_once $root . '/config/session.php';

$router = new Router();

$mapGet = function (array $urls, callable $action) use ($router): void {
    foreach ($urls as $url) {
        $router->get($url, $action);
    }
};
$mapAny = function (array $urls, callable $action) use ($router): void {
    foreach ($urls as $url) {
        $router->any($url, $action);
    }
};

$mapGet(['/', 'index.php', 'index', 'home', 'home.php'], function () use ($pdo): void {
    $page_title = 'Trang chủ - Thoáng.vn';
    $active_nav = $_GET['category'] ?? 'all';

    if ($active_nav === '' || !preg_match('/^[a-z0-9-]+$/', $active_nav)) {
        $active_nav = 'all';
    }

    $controller = new HomeController(new HomeModel($pdo));
    $homeData = $controller->index();
    $top_articles = $homeData['top_articles'];
    $latest_articles = $homeData['latest_articles'];

    View::render('home.index', get_defined_vars());
});

$mapGet(['about', 'about.php'], function () use ($pdo): void {
    $is_admin = isAdmin();
    $is_logged = isLoggedIn();
    $cur_user = getCurrentUser();
    $user_email = $cur_user ? $cur_user['email'] : '';

    $controller = new AboutController(new AboutSectionModel($pdo));
    $sections = $controller->index();

    View::render('about.index', get_defined_vars());
});

$mapAny(['account', 'account.php'], function () use ($pdo): void {
    if (!isLoggedIn()) {
        header('Location: ' . route('login', ['redirect' => route('account')]));
        exit;
    }

    $controller = new AccountController(new UserModel($pdo));
    $accountData = $controller->handle((int)$_SESSION['user_id']);
    $user = $accountData['user'];
    $avatars = $accountData['avatars'];
    $error = $accountData['error'];
    $message = $accountData['message'];
    $page_title = 'Quản lý tài khoản - Thoáng.vn';

    View::render('account.index', get_defined_vars());
});

$mapAny(['login', 'login.php'], function () use ($pdo): void {
    if (isLoggedIn()) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: ' . route('dashboard'));
        } elseif ($_SESSION['role'] === 'writer') {
            header('Location: ' . route('dashboard_writer'));
        } else {
            header('Location: ' . route());
        }
        exit;
    }

    $controller = new AuthController(new UserModel($pdo));
    $error = $controller->login() ?? '';

    View::render('auth.login', [
        'error' => $error,
    ]);
});

$mapAny(['register', 'register.php'], function () use ($pdo): void {
    if (isLoggedIn()) {
        header('Location: index.php');
        exit;
    }

    require_once __DIR__ . '/config/mailer.php';
    $controller = new AuthController(new UserModel($pdo));
    [$error, $success] = $controller->register('sendSystemEmail');

    View::render('auth.register', [
        'error' => $error,
        'success' => $success,
    ]);
});

$mapAny(['forgot_password', 'forgot_password.php'], function () use ($pdo): void {
    if (isLoggedIn()) {
        header('Location: index.php');
        exit;
    }

    require_once __DIR__ . '/config/mailer.php';
    $controller = new PasswordController(new UserModel($pdo));
    [$error, $success] = $controller->forgotPassword('sendSystemEmail');

    View::render('auth.forgot_password', [
        'error' => $error,
        'success' => $success,
    ]);
});

$mapAny(['reset_password', 'reset_password.php'], function () use ($pdo): void {
    if (App\Core\Auth::check()) {
        header('Location: index.php');
        exit;
    }

    $token = $_GET['token'] ?? '';
    $controller = new PasswordController(new UserModel($pdo));
    [$error, $success] = $controller->resetPassword($token);

    View::render('auth.reset_password', [
        'error' => $error,
        'success' => $success,
    ]);
});

$mapGet(['verify', 'verify.php'], function () use ($pdo): void {
    $page_title = 'Xác thực tài khoản - Thoáng.vn';
    $controller = new VerificationController(new UserModel($pdo));
    $result = $controller->verify($_GET['token'] ?? '');
    $message = $result['message'];
    $status_class = $result['status_class'];

    View::render('auth.verify', get_defined_vars());
});

$mapAny(['saved', 'saved.php'], function () use ($pdo): void {
    $page_title = 'Đã lưu - Thoáng.vn';
    $active_nav = 'saved';
    $page_css = 'stylesheets/style.css';
    $filter_cat = $_GET['cat'] ?? 'all';
    $search_kw = trim($_GET['q'] ?? '');
    $is_logged_in = App\Core\Auth::check();
    $saved_articles = [];
    $total = 0;

    if ($is_logged_in) {
        $controller = new SavedController(new SavedArticleModel($pdo));
        $savedData = $controller->index(App\Core\Auth::id(), $filter_cat, $search_kw);
        $saved_articles = $savedData['saved_articles'];
        $total = $savedData['total'];
    }

    View::render('saved.index', get_defined_vars());
});

$mapGet(['search', 'search.php'], function () use ($pdo): void {
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
});

$mapGet(['writer', 'writer.php'], function () use ($pdo): void {
    if (!isLoggedIn() || !in_array($_SESSION['role'] ?? '', ['user', 'admin'], true)) {
        $redirect = route('writer', isset($_GET['id']) ? ['id' => (int)$_GET['id']] : []);
        header('Location: ' . route('login', ['redirect' => $redirect]));
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
});

$mapAny(['dashboard_writer', 'dashboard_writer.php'], function () use ($pdo): void {
    if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'writer') {
        header('Location: ' . route('login'));
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
});

$mapAny(['dashboard', 'dashboard.php'], function () use ($pdo): void {
    if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ' . route('login'));
        exit;
    }

    $error = '';
    $adminController = new AdminController(new AdminModel($pdo));
    $error = $adminController->handleRequest() ?? '';
    $selected_comment_user = null;
    $selected_user_comments = [];
    $comment_user_id = (int)($_GET['comment_user_id'] ?? 0);

    if ($comment_user_id > 0) {
        $commentAdminModel = new AdminModel($pdo);
        $selected_comment_user = $commentAdminModel->userBrief($comment_user_id);
        if ($selected_comment_user && ($selected_comment_user['role'] ?? '') === 'user') {
            $selected_user_comments = $commentAdminModel->commentsByUser($comment_user_id);
        } else {
            $selected_comment_user = null;
        }
    }

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
    $categories = $dashboardData['categories'];
    $error = $error ?: $dashboardData['error'];
    $admin_view = ($_GET['view'] ?? '') === 'categories' ? 'categories' : 'overview';
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
});

$mapAny(['vietbai', 'vietbai.php'], function () use ($pdo): void {
    if (!isLoggedIn() || $_SESSION['role'] !== 'writer') {
        header('Location: ' . route());
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
});

$mapAny(['article', 'article.php'], function () use ($pdo): void {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $comment_error = '';
    $comments = [];
    $nextArticle = null;
    $prevArticle = null;

    if ($id <= 0) {
        if (is_ajax_request()) {
            jsonResponse(['success' => false, 'message' => 'Bài viết không hợp lệ.'], 400);
        }

        header('Location: ' . route());
        exit;
    }

    $articleModel = new ArticleModel($pdo);
    $previewArticle = $articleModel->find($id);

    if (!$previewArticle) {
        if (is_ajax_request()) {
            jsonResponse(['success' => false, 'message' => 'Không tìm thấy bài viết.'], 404);
        }

        header('Location: ' . route());
        exit;
    }

    $canPreview = App\Core\Auth::check() && (
        App\Core\Auth::isAdmin() ||
        (
            App\Core\Auth::isWriter() &&
            (int)($previewArticle['author_id'] ?? 0) === App\Core\Auth::id()
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

    $back_url = route();
    if (($_GET['from'] ?? '') === 'dashboard') {
        $back_url = route('dashboard');
    }

    View::render('articles.show', get_defined_vars());
});

$mapGet(['logout', 'logout.php'], function (): void {
    session_unset();
    session_destroy();
    header('Location: ' . route('login'));
    exit;
});

$url = trim($_GET['route'] ?? '', '/');
if ($url === '') {
    $url = trim($_GET['url'] ?? '', '/');
}

if ($url === '') {
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    $requestUri = trim($requestUri, '/');
    $scriptName = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($requestUri !== '' && $requestUri !== $scriptName) {
        $url = $requestUri;
    }
}

if ($url === '' || $url === 'index.php' || $url === '/') {
    $url = '/';
}

$router->dispatch($url);
