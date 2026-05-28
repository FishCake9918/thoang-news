<?php

use App\Controllers\AuthController;
use App\Core\View;
use App\Models\UserModel;

session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard.php');
    } elseif ($_SESSION['role'] === 'writer') {
        header('Location: dashboard_writer.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$controller = new AuthController(new UserModel($pdo));
$error = $controller->login() ?? '';

View::render('auth.login', [
    'error' => $error,
]);
