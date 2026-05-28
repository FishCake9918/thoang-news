<?php

use App\Controllers\BookmarkApiController;
use App\Models\BookmarkModel;

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

$controller = new BookmarkApiController(new BookmarkModel($pdo));
$controller->savedAction();
