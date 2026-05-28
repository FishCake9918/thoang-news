<?php

use App\Controllers\AboutApiController;
use App\Models\AboutSectionModel;

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

$controller = new AboutApiController(new AboutSectionModel($pdo));
$controller->save();
