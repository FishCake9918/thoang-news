<?php

use App\Controllers\PasswordController;
use App\Core\View;
use App\Models\UserModel;

session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/mailer.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$controller = new PasswordController(new UserModel($pdo));
[$error, $success] = $controller->forgotPassword('sendSystemEmail');

View::render('auth.forgot_password', [
    'error' => $error,
    'success' => $success,
]);
