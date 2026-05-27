<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $article_id = (int)($_POST['article_id'] ?? 0);

    try {
        if ($action === 'delete_article') {
            if ($article_id <= 0) {
                throw new Exception('Bài viết không hợp lệ.');
            }

            // Biến $author_id được lấy từ file dashboard_writer.php gọi file này
            $stmt = $pdo->prepare("
                DELETE FROM articles
                WHERE id = ?
                  AND author_id = ?
            ");
            $stmt->execute([$article_id, $author_id]);

            header('Location: dashboard_writer.php');
            exit;
        }
    } catch (Exception $e) {
        // Biến $error sẽ được hiển thị ở file dashboard_writer.php
        $error = 'Lỗi xử lý: ' . $e->getMessage();
    }
}