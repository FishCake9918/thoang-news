<?php

namespace App\Models;

use App\Core\Model;

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
}
