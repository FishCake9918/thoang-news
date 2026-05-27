<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_role') {
            $user_id = (int)($_POST['user_id'] ?? 0);
            $new_role = $_POST['role'] ?? 'user';

            $allowed_roles = ['admin', 'writer', 'user'];

            if ($user_id <= 0 || !in_array($new_role, $allowed_roles, true)) {
                throw new Exception('Vai trò không hợp lệ.');
            }

            if ($user_id !== (int)($_SESSION['user_id'] ?? 0)) {
                $stmt = $pdo->prepare("
                    UPDATE users
                    SET role = ?
                    WHERE id = ?
                ");
                $stmt->execute([$new_role, $user_id]);
            }

            header('Location: dashboard.php');
            exit;
        }

        if ($action === 'delete_user') {
            $user_id = (int)($_POST['user_id'] ?? 0);

            if ($user_id <= 0) {
                throw new Exception('Người dùng không hợp lệ.');
            }

            if ($user_id !== (int)($_SESSION['user_id'] ?? 0)) {
                $stmt = $pdo->prepare("
                    DELETE FROM users
                    WHERE id = ?
                ");
                $stmt->execute([$user_id]);
            }

            header('Location: dashboard.php');
            exit;
        }

        if ($action === 'update_article_status') {
            $article_id = (int)($_POST['article_id'] ?? 0);
            $new_status = $_POST['status'] ?? 'request';

            $allowed_statuses = ['request', 'Approved', 'disapproved'];

            if ($article_id <= 0 || !in_array($new_status, $allowed_statuses, true)) {
                throw new Exception('Trạng thái bài viết không hợp lệ.');
            }

            if ($new_status === 'Approved') {
                $stmt = $pdo->prepare("
                    UPDATE articles
                    SET status = 'Approved',
                        published_at = NOW(),
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$article_id]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE articles
                    SET status = ?,
                        published_at = NULL,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$new_status, $article_id]);
            }

            header('Location: dashboard.php');
            exit;
        }

        if ($action === 'delete_article') {
            $article_id = (int)($_POST['article_id'] ?? 0);

            if ($article_id <= 0) {
                throw new Exception('Bài viết không hợp lệ.');
            }

            $stmt = $pdo->prepare("
                DELETE FROM articles
                WHERE id = ?
            ");
            $stmt->execute([$article_id]);

            header('Location: dashboard.php');
            exit;
        }
        
        if ($action === 'delete_comment') {
            $comment_id = (int)($_POST['comment_id'] ?? 0);

            if ($comment_id <= 0) {
                throw new Exception('Bình luận không hợp lệ.');
            }

            $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->execute([$comment_id]);

            header('Location: dashboard.php');
            exit;
        }

    } catch (Throwable $e) {
        $error = 'Lỗi xử lý: ' . $e->getMessage();
    }
}