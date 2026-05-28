<?php

use App\Controllers\PasswordController;
use App\Core\Auth;
use App\Core\View;
use App\Models\UserModel;

ob_start();
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

if (Auth::check()) {
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
