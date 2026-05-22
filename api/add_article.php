<?php
session_start();
require_once '../config/db.php';
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || $_SESSION['role'] !== 'writer') {
    jsonResponse(['success' => false, 'message' => 'Bạn không có quyền dùng chức năng viết bài.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Phương thức gửi dữ liệu không hợp lệ.'], 405);
}

$inputData = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($inputData)) {
    jsonResponse(['success' => false, 'message' => 'Định dạng dữ liệu không hợp lệ.'], 400);
}

$title       = trim($inputData['title'] ?? '');
$category_id = (int)($inputData['category_id'] ?? 0);
$summary     = trim($inputData['summary'] ?? '');
$content     = trim($inputData['content'] ?? '');
$tags        = trim($inputData['tags'] ?? '');
$image_url   = trim($inputData['image_url'] ?? '');
$article_id  = (int)($inputData['article_id'] ?? 0);
$author_id   = (int)$_SESSION['user_id'];
$author_name = trim($_SESSION['full_name'] ?? '') ?: trim($_SESSION['username'] ?? 'Tác giả');

if ($title === '') {
    jsonResponse(['success' => false, 'message' => 'Vui lòng nhập tiêu đề bài viết.']);
}
if ($category_id <= 0) {
    jsonResponse(['success' => false, 'message' => 'Vui lòng chọn danh mục hợp lệ.']);
}
if ($summary === '') {
    jsonResponse(['success' => false, 'message' => 'Vui lòng nhập đoạn tóm tắt bài viết.']);
}
if ($content === '') {
    jsonResponse(['success' => false, 'message' => 'Vui lòng nhập nội dung chi tiết bài viết.']);
}
if ($image_url !== '' && !preg_match('#^uploads/articles/[A-Za-z0-9_.-]+$#', $image_url)) {
    jsonResponse(['success' => false, 'message' => 'Đường dẫn hình ảnh không hợp lệ.']);
}

try {
    if ($article_id > 0) {
        $check = $pdo->prepare("SELECT id, status FROM articles WHERE id = ? AND author_id = ? LIMIT 1");
        $check->execute([$article_id, $author_id]);
        $existingArticle = $check->fetch(PDO::FETCH_ASSOC);
        if (!$existingArticle) {
            jsonResponse(['success' => false, 'message' => 'Không tìm thấy bài viết hoặc bạn không có quyền chỉnh sửa.'], 404);
        }

        $stmt = $pdo->prepare("
            UPDATE articles
            SET category_id = :category_id,
                title = :title,
                summary = :summary,
                content = :content,
                source = :author_name,
                tags = :tags,
                image_url = :image_url,
                status = 'request',
                published_at = NULL,
                updated_at = NOW()
            WHERE id = :article_id AND author_id = :author_id
        ");

        $stmt->execute([
            ':category_id' => $category_id,
            ':title'       => $title,
            ':summary'     => $summary,
            ':content'     => $content,
            ':author_name' => $author_name,
            ':tags'        => $tags !== '' ? $tags : null,
            ':image_url'   => $image_url !== '' ? $image_url : null,
            ':article_id'  => $article_id,
            ':author_id'   => $author_id,
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Bài viết đã được cập nhật và chuyển về trạng thái chờ duyệt.'
        ]);
    }

    $stmt = $pdo->prepare("
        INSERT INTO articles
            (category_id, author_id, title, summary, content, source, tags, image_url, status, view_count, created_at, updated_at)
        VALUES
            (:category_id, :author_id, :title, :summary, :content, :author_name, :tags, :image_url, 'request', 0, NOW(), NOW())
    ");

    $stmt->execute([
        ':category_id' => $category_id,
        ':author_id'   => $author_id,
        ':title'       => $title,
        ':summary'     => $summary,
        ':content'     => $content,
        ':author_name' => $author_name,
        ':tags'        => $tags !== '' ? $tags : null,
        ':image_url'   => $image_url !== '' ? $image_url : null,
    ]);

    jsonResponse([
        'success' => true,
        'message' => 'Bài viết đã được gửi và đang chờ Admin duyệt.'
    ]);
} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Lỗi CSDL: ' . $e->getMessage()
    ], 500);
}
