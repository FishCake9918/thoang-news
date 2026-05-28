<?php

use App\Controllers\FeedbackApiController;
use App\Models\FeedbackModel;

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

$controller = new FeedbackApiController(new FeedbackModel($pdo));
$controller->action();
