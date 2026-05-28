<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AdminModel;
use Throwable;

class AdminController extends Controller
{
    private AdminModel $admin;

    public function __construct(AdminModel $admin)
    {
        $this->admin = $admin;
    }

    public function handleRequest(): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        try {
            $action = $_POST['action'] ?? '';

            if ($action === 'update_role') {
                $this->updateRole();
                return null;
            }

            if ($action === 'delete_user') {
                $this->deleteUser();
                return null;
            }

            if ($action === 'update_article_status') {
                $this->updateArticleStatus();
                return null;
            }

            if ($action === 'delete_article') {
                $this->deleteArticle();
                return null;
            }

            if ($action === 'delete_comment') {
                $this->deleteComment();
                return null;
            }
        } catch (Throwable $e) {
            return 'Lỗi xử lý: ' . $e->getMessage();
        }

        return null;
    }

    private function updateRole(): void
    {
        $userId = (int)($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'user';
        $allowed = ['admin', 'writer', 'user'];

        if ($userId <= 0 || !in_array($role, $allowed, true)) {
            throw new \InvalidArgumentException('Vai trò không hợp lệ.');
        }

        if ($userId !== (int)($_SESSION['user_id'] ?? 0)) {
            $this->admin->updateUserRole($userId, $role);
        }

        $this->redirect('dashboard.php');
    }

    private function deleteUser(): void
    {
        $userId = (int)($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            throw new \InvalidArgumentException('Người dùng không hợp lệ.');
        }

        if ($userId !== (int)($_SESSION['user_id'] ?? 0)) {
            $this->admin->deleteUser($userId);
        }

        $this->redirect('dashboard.php');
    }

    private function updateArticleStatus(): void
    {
        $articleId = (int)($_POST['article_id'] ?? 0);
        $status = $_POST['status'] ?? 'request';
        $allowed = ['request', 'Approved', 'disapproved'];

        if ($articleId <= 0 || !in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Trạng thái bài viết không hợp lệ.');
        }

        $this->admin->updateArticleStatus($articleId, $status);
        $this->redirect('dashboard.php');
    }

    private function deleteArticle(): void
    {
        $articleId = (int)($_POST['article_id'] ?? 0);

        if ($articleId <= 0) {
            throw new \InvalidArgumentException('Bài viết không hợp lệ.');
        }

        $this->admin->deleteArticle($articleId);
        $this->redirect('dashboard.php');
    }

    private function deleteComment(): void
    {
        $commentId = (int)($_POST['comment_id'] ?? 0);

        if ($commentId <= 0) {
            throw new \InvalidArgumentException('Bình luận không hợp lệ.');
        }

        $this->admin->deleteComment($commentId);
        $this->redirect('dashboard.php');
    }
}
