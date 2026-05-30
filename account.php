<?php

use App\Controllers\AccountController;
use App\Core\View;
use App\Models\UserModel;

session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=account.php');
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
