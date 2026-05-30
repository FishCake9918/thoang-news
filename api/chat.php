<?php

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/ChatModel.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/ArticleModel.php';
require_once __DIR__ . '/../app/Controllers/ChatController.php';

use App\Models\ChatModel;
use App\Models\ArticleModel;
use App\Controllers\ChatController;

header('Content-Type: application/json');

$apiKey = 'AIzaSyCHQd6ndvhMPdTjvYhuy4i314uQTQ57sFM'; 

if ($apiKey === '') {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: Chưa cấu hình API Key cho Google Gemini.']);
    exit;
}

$controller = new ChatController(new ChatModel($apiKey), new ArticleModel($pdo));
$controller->handleChatRequest();