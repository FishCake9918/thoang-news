<?php

use App\Controllers\WriterArticleApiController;
use App\Models\ArticleModel;
use App\Models\CategoryModel;

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

$controller = new WriterArticleApiController(new ArticleModel($pdo), new CategoryModel($pdo));
$controller->save();
