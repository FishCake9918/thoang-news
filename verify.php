<?php

use App\Controllers\VerificationController;
use App\Core\View;
use App\Models\UserModel;

session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$page_title = 'Xác thực tài khoản - Thoáng.vn';
$controller = new VerificationController(new UserModel($pdo));
$result = $controller->verify($_GET['token'] ?? '');
$message = $result['message'];
$status_class = $result['status_class'];

View::render('auth.verify', get_defined_vars());
