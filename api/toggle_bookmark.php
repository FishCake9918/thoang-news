<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../config/session.php';

if (!isLoggedIn()) {
    jsonResponse([
        'status' => 'auth_required',
        'message' => 'Vui lòng đăng nhập hoặc đăng ký để lưu bài viết.'
    ], 401);
}

$session_id = session_id();
$news_id = isset($_POST['news_id']) ? (int)$_POST['news_id'] : 0;

if ($news_id <= 0) {
    jsonResponse(['status' => 'error', 'message' => 'ID bài báo không hợp lệ.']);
}

try {
    $articleStmt = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND status = 'published' LIMIT 1");
    $articleStmt->execute([$news_id]);
    if (!$articleStmt->fetch()) {
        jsonResponse(['status' => 'error', 'message' => 'Bài viết không tồn tại hoặc chưa được xuất bản.'], 404);
    }

    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE session_id = :session_id AND article_id = :article_id");
    $stmt->execute([':session_id' => $session_id, ':article_id' => $news_id]);

    if ($stmt->fetch()) {
        $delStmt = $pdo->prepare("DELETE FROM bookmarks WHERE session_id = :session_id AND article_id = :article_id");
        $delStmt->execute([':session_id' => $session_id, ':article_id' => $news_id]);
        jsonResponse(['status' => 'success', 'action' => 'removed']);
    }

    $insStmt = $pdo->prepare("INSERT INTO bookmarks (session_id, article_id) VALUES (:session_id, :article_id)");
    $insStmt->execute([':session_id' => $session_id, ':article_id' => $news_id]);
    jsonResponse(['status' => 'success', 'action' => 'saved']);
} catch (PDOException $e) {
    jsonResponse(['status' => 'error', 'message' => 'Lỗi SQL: ' . $e->getMessage()], 500);
}
