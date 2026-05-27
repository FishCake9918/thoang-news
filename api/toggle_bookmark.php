<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../config/session.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$session_id = session_id();
$news_id = isset($_POST['news_id']) ? (int)$_POST['news_id'] : 0;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Hỗ trợ đọc từ payload JSON (nếu script JS gửi bằng JSON)
if ($news_id === 0) {
    $inputData = json_decode(file_get_contents('php://input'), true);
    $news_id = isset($inputData['news_id']) ? (int)$inputData['news_id'] : (isset($inputData['article_id']) ? (int)$inputData['article_id'] : (isset($inputData['id']) ? (int)$inputData['id'] : 0));
}

if ($news_id <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID bài viết không hợp lệ.'], 400);
}

if (!$user_id) {
    jsonResponse([
        'status' => 'auth_required',
        'success' => false,
        'message' => 'Vui lòng đăng nhập hoặc đăng ký để lưu bài viết.'
    ], 401);
}

try {
    $articleStmt = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND status = 'Approved' LIMIT 1");
    $articleStmt->execute([$news_id]);
    if (!$articleStmt->fetch()) {
        jsonResponse(['status' => 'error', 'success' => false, 'message' => 'Bài viết không tồn tại hoặc chưa được xuất bản.'], 404);
    }

    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = :user_id AND article_id = :article_id");
    $stmt->execute([':user_id' => $user_id, ':article_id' => $news_id]);

    if ($stmt->fetch()) {
        $delStmt = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = :user_id AND article_id = :article_id");
        $delStmt->execute([':user_id' => $user_id, ':article_id' => $news_id]);
        jsonResponse(['status' => 'success', 'success' => true, 'action' => 'removed']);
    }

    $insStmt = $pdo->prepare("INSERT INTO bookmarks (session_id, article_id, user_id) VALUES (:session_id, :article_id, :user_id)");
    $insStmt->execute([
        ':session_id' => $session_id, 
        ':article_id' => $news_id,
        ':user_id' => $user_id
    ]);
    
    jsonResponse(['status' => 'success', 'success' => true, 'action' => 'saved']);
} catch (Exception $e) {
    jsonResponse(['status' => 'error', 'success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
}
