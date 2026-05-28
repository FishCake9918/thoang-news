<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ArticleModel;
use App\Views\Components\CommentComponent;

class ArticleController extends Controller
{
    private ArticleModel $articles;

    public function __construct(ArticleModel $articles)
    {
        $this->articles = $articles;
    }

    public function handleCommentRequest(int $articleId): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'add_comment') {
            return $this->addComment($articleId);
        }

        if ($action === 'delete_comment') {
            $this->deleteComment($articleId);
        }

        return null;
    }

    private function addComment(int $articleId): ?string
    {
        if (!\isLoggedIn()) {
            if (\is_ajax_request()) {
                $this->json(['success' => false, 'message' => 'Bạn cần đăng nhập để bình luận.'], 401);
            }
            $this->redirect('login.php');
        }

        $content = trim($_POST['comment_content'] ?? '');

        if ($content === '') {
            if (\is_ajax_request()) {
                $this->json(['success' => false, 'message' => 'Vui lòng nhập nội dung bình luận.'], 422);
            }
            return 'Vui lòng nhập nội dung bình luận.';
        }

        if (strlen($content) > 1000) {
            if (\is_ajax_request()) {
                $this->json(['success' => false, 'message' => 'Bình luận không được vượt quá 1000 ký tự.'], 422);
            }
            return 'Bình luận không được vượt quá 1000 ký tự.';
        }

        $comment = $this->articles->createComment($articleId, (int)$_SESSION['user_id'], $content);
        $count = $this->articles->countComments($articleId);

        if (\is_ajax_request()) {
            $this->json([
                'success' => true,
                'message' => 'Đã gửi bình luận.',
                'html' => $comment ? CommentComponent::render($comment) : '',
                'count' => $count,
            ]);
        }

        $this->redirect('article.php?id=' . $articleId . '#comments');
    }

    private function deleteComment(int $articleId): void
    {
        if (!\isLoggedIn()) {
            if (\is_ajax_request()) {
                $this->json(['success' => false, 'message' => 'Bạn cần đăng nhập để xoá bình luận.'], 401);
            }
            $this->redirect('login.php');
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);

        if ($commentId <= 0) {
            if (\is_ajax_request()) {
                $this->json(['success' => false, 'message' => 'Bình luận không hợp lệ.'], 400);
            }
            $this->redirect('article.php?id=' . $articleId . '#comments');
        }

        $this->articles->deleteComment(
            $commentId,
            $articleId,
            (int)($_SESSION['user_id'] ?? 0),
            ($_SESSION['role'] ?? '') === 'admin'
        );

        $count = $this->articles->countComments($articleId);

        if (\is_ajax_request()) {
            $this->json(['success' => true, 'message' => 'Đã xoá bình luận.', 'comment_id' => $commentId, 'count' => $count]);
        }

        $this->redirect('article.php?id=' . $articleId . '#comments');
    }
}
