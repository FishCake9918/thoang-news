<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
if (!isset($pdo)) {
    exit(json_encode(["error" => "Lỗi cấu hình Database."]));
}

$guest_id = session_id(); 
$category = $_GET['category'] ?? 'all';

// Bổ sung JOIN bảng categories để lấy mã danh mục (slug) cho JS
$sql = "SELECT n.*, 
               c.slug as category, 
               n.source as source_name,
               IF(b.id IS NOT NULL, 1, 0) as is_saved 
        FROM articles n
        LEFT JOIN categories c ON n.category_id = c.id
        LEFT JOIN bookmarks b ON n.id = b.article_id AND b.session_id = :session_id";

$params = [':session_id' => $guest_id];

// Lọc theo danh mục dùng cột slug
if ($category !== 'all') {
    $sql .= " WHERE c.slug = :category";
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