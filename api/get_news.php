<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../config/session.php';

$session_id = session_id();
$category = $_GET['category'] ?? 'all';
$is_logged = isLoggedIn();

$allowed_categories = ['all', 'world', 'biz', 'tech', 'sport', 'life', 'edu', 'other'];

if (!in_array($category, $allowed_categories, true)) {
    $category = 'all';
}

$sql = "
    SELECT 
        n.*,
        c.slug AS category,
        c.name AS category_name,
        n.source AS source_name,
        " . ($is_logged ? "IF(b.id IS NOT NULL, 1, 0)" : "0") . " AS is_saved
    FROM articles n
    LEFT JOIN categories c ON n.category_id = c.id
";

$params = [];

if ($is_logged) {
    $sql .= "
        LEFT JOIN bookmarks b 
            ON n.id = b.article_id 
            AND b.session_id = :session_id
    ";
    $params[':session_id'] = $session_id;
}

$where = [
    "n.status = 'Approved'"
];

if ($category !== 'all') {
    $where[] = "c.slug = :category";
    $params[':category'] = $category;
}

$sql .= " WHERE " . implode(' AND ', $where);

$sql .= "
    ORDER BY 
        n.published_at DESC,
        n.created_at DESC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(
        $stmt->fetchAll(PDO::FETCH_ASSOC),
        JSON_UNESCAPED_UNICODE
    );
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Lỗi truy vấn SQL: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}