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

            if ($action === 'save_category') {
                $this->saveCategory();
                return null;
            }

            if ($action === 'delete_category') {
                $this->deleteCategory();
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

    private function saveCategory(): void
    {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        if ($name === '') {
            throw new \InvalidArgumentException('Tên danh mục không được để trống.');
        }

        $slug = $this->slugify($slug !== '' ? $slug : $name);
        if ($slug === '') {
            throw new \InvalidArgumentException('Slug danh mục không hợp lệ.');
        }

        $this->admin->saveCategory([
            'id' => (int)($_POST['category_id'] ?? 0),
            'name' => $name,
            'slug' => $slug,
            'parent_id' => (int)($_POST['parent_id'] ?? 0),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);

        $this->redirect('dashboard.php?view=categories#category-manager');
    }

    private function deleteCategory(): void
    {
        $categoryId = (int)($_POST['category_id'] ?? 0);

        if ($categoryId <= 0) {
            throw new \InvalidArgumentException('Danh mục không hợp lệ.');
        }

        $this->admin->deleteCategory($categoryId);
        $this->redirect('dashboard.php?view=categories#category-manager');
    }

    private function slugify(string $value): string
    {
        $value = trim(mb_strtolower($value, 'UTF-8'));
        $value = strtr($value, [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
        ]);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }
}
