<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class AdminModel extends Model
{
    public function updateUserRole(int $userId, string $role): void
    {
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $userId]);
    }

    public function deleteUser(int $userId): void
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
    }

    public function updateArticleStatus(int $articleId, string $status): void
    {
        if ($status === 'Approved') {
            $stmt = $this->db->prepare("
                UPDATE articles
                SET status = 'Approved', published_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$articleId]);
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE articles
            SET status = ?, published_at = NULL, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, $articleId]);
    }

    public function deleteArticle(int $articleId): void
    {
        $stmt = $this->db->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$articleId]);
    }

    public function deleteComment(int $commentId): void
    {
        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
    }

    public function userBrief(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, username, email, full_name, role
            FROM users
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function commentsByUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.id,
                c.content,
                c.created_at,
                a.id AS article_id,
                a.title AS article_title,
                a.status AS article_status,
                cat.name AS category_name
            FROM comments c
            INNER JOIN articles a ON a.id = c.article_id
            LEFT JOIN categories cat ON cat.id = a.category_id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC, c.id DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveCategory(array $data): void
    {
        $categoryId = (int)($data['id'] ?? 0);
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        $sortOrder = (int)($data['sort_order'] ?? 0);

        if ($categoryId > 0 && $parentId === $categoryId) {
            throw new \InvalidArgumentException('Danh mục cha không hợp lệ.');
        }

        $slugStmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE slug = ? AND id <> ?");
        $slugStmt->execute([$data['slug'], $categoryId]);
        if ((int)$slugStmt->fetchColumn() > 0) {
            throw new \InvalidArgumentException('Slug danh mục đã tồn tại. Vui lòng dùng slug khác.');
        }

        if ($parentId !== null) {
            $parentStmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE id = ? AND parent_id IS NULL");
            $parentStmt->execute([$parentId]);
            if ((int)$parentStmt->fetchColumn() === 0) {
                throw new \InvalidArgumentException('Danh mục cha không tồn tại hoặc không hợp lệ.');
            }
        }

        if ($sortOrder <= 0) {
            if ($parentId === null) {
                $orderStmt = $this->db->query("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM categories WHERE parent_id IS NULL");
            } else {
                $orderStmt = $this->db->prepare("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM categories WHERE parent_id = ?");
                $orderStmt->execute([$parentId]);
            }
            $sortOrder = (int)$orderStmt->fetchColumn();
        }

        if ($categoryId > 0) {
            $stmt = $this->db->prepare("
                UPDATE categories
                SET name = :name,
                    slug = :slug,
                    parent_id = :parent_id,
                    sort_order = :sort_order,
                    is_active = :is_active
                WHERE id = :id
            ");
            $stmt->execute([
                ':name' => $data['name'],
                ':slug' => $data['slug'],
                ':parent_id' => $parentId,
                ':sort_order' => $sortOrder,
                ':is_active' => (int)$data['is_active'],
                ':id' => $categoryId,
            ]);
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO categories (name, slug, parent_id, sort_order, is_active, color_bg, color_text)
            VALUES (:name, :slug, :parent_id, :sort_order, :is_active, '#EEEDFE', '#534AB7')
        ");
        $stmt->execute([
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':parent_id' => $parentId,
            ':sort_order' => $sortOrder,
            ':is_active' => (int)$data['is_active'],
        ]);
    }

    public function deleteCategory(int $categoryId): void
    {
        $childStmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
        $childStmt->execute([$categoryId]);
        if ((int)$childStmt->fetchColumn() > 0) {
            throw new \InvalidArgumentException('Không thể xóa danh mục đang có danh mục con.');
        }

        $articleStmt = $this->db->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ?");
        $articleStmt->execute([$categoryId]);
        if ((int)$articleStmt->fetchColumn() > 0) {
            throw new \InvalidArgumentException('Không thể xóa danh mục đang có bài viết.');
        }

        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
    }
}
