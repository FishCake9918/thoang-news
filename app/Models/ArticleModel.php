<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class ArticleModel extends Model
{
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, c.name AS category_name,
                   u.id AS author_user_id,
                   u.username AS author_username,
                   u.full_name AS author_full_name,
                   u.role AS author_role
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function incrementViews(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE articles SET view_count = view_count + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function nextApprovedId(int $currentId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM articles
            WHERE status = 'Approved' AND id > ?
            ORDER BY id ASC
            LIMIT 1
        ");
        $stmt->execute([$currentId]);
        $id = $stmt->fetchColumn();

        if ($id) {
            return (int)$id;
        }

        $stmt = $this->db->prepare("
            SELECT id
            FROM articles
            WHERE status = 'Approved' AND id <> ?
            ORDER BY id ASC
            LIMIT 1
        ");
        $stmt->execute([$currentId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public function previousApprovedId(int $currentId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT id
            FROM articles
            WHERE status = 'Approved' AND id < ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$currentId]);
        $id = $stmt->fetchColumn();

        if ($id) {
            return (int)$id;
        }

        $stmt = $this->db->prepare("
            SELECT id
            FROM articles
            WHERE status = 'Approved' AND id <> ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$currentId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public function approvedFeed(string $category, ?int $userId = null): array
    {
        $sql = "
            SELECT
                n.*,
                c.slug AS category,
                c.name AS category_name,
                c.color_bg,
                c.color_text,
                n.source AS source_name,
                " . ($userId ? "IF(b.id IS NOT NULL, 1, 0)" : "0") . " AS is_saved
            FROM articles n
            LEFT JOIN categories c ON n.category_id = c.id
            LEFT JOIN categories p ON c.parent_id = p.id
        ";

        $params = [];

        if ($userId) {
            $sql .= "
                LEFT JOIN bookmarks b
                    ON n.id = b.article_id
                    AND b.user_id = :user_id
            ";
            $params[':user_id'] = $userId;
        }

        $where = ["n.status = 'Approved'"];

        if ($category === 'hot') {
            $where[] = "DATE(n.published_at) = CURDATE()";
        }

        if ($category !== 'all' && $category !== 'hot') {
            $where[] = "(c.slug = :category_slug OR p.slug = :parent_slug)";
            $params[':category_slug'] = $category;
            $params[':parent_slug'] = $category;
        }

        $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= $category === 'hot'
            ? " ORDER BY n.published_at DESC, n.view_count DESC, n.created_at DESC"
            : " ORDER BY n.published_at DESC, n.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function commentsForArticle(int $articleId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username, u.full_name
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            WHERE c.article_id = ?
            ORDER BY c.created_at DESC, c.id DESC
        ");
        $stmt->execute([$articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createComment(int $articleId, int $userId, string $content): ?array
    {
        $stmt = $this->db->prepare("
            INSERT INTO comments (article_id, user_id, content, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$articleId, $userId, $content]);

        $commentId = (int)$this->db->lastInsertId();
        $stmt = $this->db->prepare("
            SELECT c.*, u.username, u.full_name
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            WHERE c.id = ? AND c.article_id = ?
            LIMIT 1
        ");
        $stmt->execute([$commentId, $articleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deleteComment(int $commentId, int $articleId, int $userId, bool $isAdmin): void
    {
        if ($isAdmin) {
            $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ? AND article_id = ?");
            $stmt->execute([$commentId, $articleId]);
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ? AND article_id = ? AND user_id = ?");
        $stmt->execute([$commentId, $articleId, $userId]);
    }

    public function countComments(int $articleId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM comments WHERE article_id = ?");
        $stmt->execute([$articleId]);
        return (int)$stmt->fetchColumn();
    }

    public function findByAuthor(int $articleId, int $authorId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM articles WHERE id = ? AND author_id = ? LIMIT 1");
        $stmt->execute([$articleId, $authorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function saveDraftForReview(array $data): int
    {
        if (!empty($data[':article_id'])) {
            $stmt = $this->db->prepare("
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
            $stmt->execute($data);
            return (int)$data[':article_id'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO articles
                (category_id, author_id, title, summary, content, source, tags, image_url, status, view_count, created_at, updated_at, published_at)
            VALUES
                (:category_id, :author_id, :title, :summary, :content, :author_name, :tags, :image_url, 'request', 0, NOW(), NOW(), NULL)
        ");
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function deleteByAuthor(int $articleId, int $authorId): void
    {
        $stmt = $this->db->prepare("DELETE FROM articles WHERE id = ? AND author_id = ?");
        $stmt->execute([$articleId, $authorId]);
    }

    public function byWriterForProfile(int $writerId, bool $includeAllStatuses): array
    {
        $where = $includeAllStatuses
            ? "a.author_id = ?"
            : "a.author_id = ? AND a.status = 'Approved'";

        $stmt = $this->db->prepare("
            SELECT a.id, a.title, a.summary, a.status, a.view_count, a.created_at, a.updated_at, a.published_at,
                   c.name AS category_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE {$where}
            ORDER BY FIELD(a.status, 'Approved', 'request', 'disapproved'), a.published_at DESC, a.updated_at DESC, a.created_at DESC
        ");
        $stmt->execute([$writerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
