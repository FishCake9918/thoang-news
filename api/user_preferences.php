<?php

use App\Controllers\UserPreferenceApiController;
use App\Models\UserModel;

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

$controller = new UserPreferenceApiController(new UserModel($pdo));
$controller->save();
