<?php
// File này được nhúng trong article.php, nên có thể truy cập các biến $pdo, $id
// và các hàm như isLoggedIn(), is_ajax_request(), send_json(), render_comment_item().

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_comment') {
        if (!isLoggedIn()) {
            if (is_ajax_request()) {
                send_json(['success' => false, 'message' => 'Bạn cần đăng nhập để bình luận.'], 401);
            }
            header('Location: login.php');
            exit;
        }

        $comment_content = trim($_POST['comment_content'] ?? '');

        if ($comment_content === '') {
            $comment_error = 'Vui lòng nhập nội dung bình luận.';
            if (is_ajax_request()) {
                send_json(['success' => false, 'message' => $comment_error], 422);
            }
        } elseif (strlen($comment_content) > 1000) {
            $comment_error = 'Bình luận không được vượt quá 1000 ký tự.';
            if (is_ajax_request()) {
                send_json(['success' => false, 'message' => $comment_error], 422);
            }
        } else {
            $commentStmt = $pdo->prepare("
                INSERT INTO comments (article_id, user_id, content, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $commentStmt->execute([$id, (int)$_SESSION['user_id'], $comment_content]);
            $newCommentId = (int)$pdo->lastInsertId();

            $newCommentStmt = $pdo->prepare("
                SELECT c.*, u.username, u.full_name
                FROM comments c
                INNER JOIN users u ON c.user_id = u.id
                WHERE c.id = ? AND c.article_id = ?
                LIMIT 1
            ");
            $newCommentStmt->execute([$newCommentId, $id]);
            $newComment = $newCommentStmt->fetch(PDO::FETCH_ASSOC);

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE article_id = ?");
            $countStmt->execute([$id]);
            $commentCount = (int)$countStmt->fetchColumn();

            if (is_ajax_request()) {
                send_json([
                    'success' => true,
                    'message' => 'Đã gửi bình luận.',
                    'html' => $newComment ? render_comment_item($newComment) : '',
                    'count' => $commentCount
                ]);
            }

            header('Location: article.php?id=' . $id . '#comments');
            exit;
        }
    }

    if ($action === 'delete_comment') {
        if (!isLoggedIn()) {
            if (is_ajax_request()) {
                send_json(['success' => false, 'message' => 'Bạn cần đăng nhập để xoá bình luận.'], 401);
            }
            header('Location: login.php');
            exit;
        }

        $comment_id = (int)($_POST['comment_id'] ?? 0);
        if ($comment_id <= 0) {
            if (is_ajax_request()) {
                send_json(['success' => false, 'message' => 'Bình luận không hợp lệ.'], 400);
            }
            header('Location: article.php?id=' . $id . '#comments');
            exit;
        }

        if (($_SESSION['role'] ?? '') === 'admin') {
            $deleteStmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND article_id = ?");
            $deleteStmt->execute([$comment_id, $id]);
        } else {
            $deleteStmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND article_id = ? AND user_id = ?");
            $deleteStmt->execute([$comment_id, $id, (int)$_SESSION['user_id']]);
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE article_id = ?");
        $countStmt->execute([$id]);
        $commentCount = (int)$countStmt->fetchColumn();

        if (is_ajax_request()) {
            send_json(['success' => true, 'message' => 'Đã xoá bình luận.', 'comment_id' => $comment_id, 'count' => $commentCount]);
        }

        header('Location: article.php?id=' . $id . '#comments');
        exit;
    }
}