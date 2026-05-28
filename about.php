<?php

use App\Controllers\AboutController;
use App\Core\View;
use App\Models\AboutSectionModel;

session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';

$is_admin = isAdmin();
$is_logged = isLoggedIn();
$cur_user = getCurrentUser();
$user_email = $cur_user ? $cur_user['email'] : '';

$controller = new AboutController(new AboutSectionModel($pdo));
$sections = $controller->index();

View::render('about.index', get_defined_vars());
