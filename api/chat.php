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

// THAY ĐỔI: Sử dụng API Key của OpenRouter (Bắt đầu bằng sk-or-...)
$apiKey = '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';

if ($apiKey === '') {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: Chưa cấu hình API Key cho OpenRouter.']);
    exit;
}

$controller = new ChatController(new ChatModel($apiKey), new ArticleModel($pdo));
$controller->handleChatRequest();