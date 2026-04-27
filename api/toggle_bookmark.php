<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
if (!isset($pdo)) {
    exit(json_encode(['status' => 'error', 'message' => 'Lỗi DB']));
}

$guest_id = session_id(); 
$news_id = isset($_POST['news_id']) ? intval($_POST['news_id']) : 0;

if ($news_id <= 0) {
    exit(json_encode(['status' => 'error', 'message' => 'ID bài báo không hợp lệ']));
}

try {
    // Kiểm tra trạng thái hiện tại
    $stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE session_id = :session_id AND news_id = :news_id");
    $stmt->execute([':session_id' => $guest_id, ':news_id' => $news_id]);
    
    if ($stmt->fetch()) {
        // Đã lưu -> Thực hiện Xóa
        $delStmt = $pdo->prepare("DELETE FROM bookmarks WHERE session_id = :session_id AND news_id = :news_id");
        $delStmt->execute([':session_id' => $guest_id, ':news_id' => $news_id]);
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        // Chưa lưu -> Thực hiện Thêm
        $insStmt = $pdo->prepare("INSERT INTO bookmarks (session_id, news_id) VALUES (:session_id, :news_id)");
        $insStmt->execute([':session_id' => $guest_id, ':news_id' => $news_id]);
        echo json_encode(['status' => 'success', 'action' => 'saved']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi SQL: ' . $e->getMessage()]);
}
?>