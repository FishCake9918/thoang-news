<?php
session_start(); // Khởi tạo Session ID cho khách
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
if (!isset($pdo)) {
    exit(json_encode(["error" => "Lỗi cấu hình Database."]));
}

$guest_id = session_id(); // Lấy mã phiên làm việc duy nhất của trình duyệt
$category = $_GET['category'] ?? 'all';

// LEFT JOIN để biết bài nào đã được khách này lưu (is_saved = 1)
$sql = "SELECT n.*, IF(b.id IS NOT NULL, 1, 0) as is_saved 
        FROM news n 
        LEFT JOIN bookmarks b ON n.id = b.news_id AND b.session_id = :session_id";

$params = [':session_id' => $guest_id];

// Lọc theo danh mục
if ($category !== 'all') {
    $sql .= " WHERE n.category = :category";
    $params[':category'] = $category;
}

$sql .= " ORDER BY n.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode(["error" => "Lỗi truy vấn SQL: " . $e->getMessage()]);
}
?>