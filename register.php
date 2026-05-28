<?php

use App\Controllers\AuthController;
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

$controller = new AuthController(new UserModel($pdo));
[$error, $success] = $controller->register('sendSystemEmail');

View::render('auth.register', [
    'error' => $error,
    'success' => $success,
]);
