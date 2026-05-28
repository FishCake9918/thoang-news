<?php

use App\Controllers\FeedApiController;
use App\Models\ArticleModel;

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

$controller = new FeedApiController(new ArticleModel($pdo));
$controller->index($_GET['category'] ?? 'all');
