<?php

namespace App\Models;

use App\Core\Model;

class BookmarkModel extends Model
{
    public function toggle(int $articleId, int $userId, string $sessionId): string
    {
        $articleStmt = $this->db->prepare("SELECT id FROM articles WHERE id = ? AND status = 'Approved' LIMIT 1");
        $articleStmt->execute([$articleId]);

        if (!$articleStmt->fetch()) {
            return 'not_found';
        }

        $stmt = $this->db->prepare("SELECT id FROM bookmarks WHERE user_id = :user_id AND article_id = :article_id");
        $stmt->execute([':user_id' => $userId, ':article_id' => $articleId]);

        if ($stmt->fetch()) {
            $delStmt = $this->db->prepare("DELETE FROM bookmarks WHERE user_id = :user_id AND article_id = :article_id");
            $delStmt->execute([':user_id' => $userId, ':article_id' => $articleId]);
            return 'removed';
        }

        $insStmt = $this->db->prepare("INSERT INTO bookmarks (session_id, article_id, user_id) VALUES (:session_id, :article_id, :user_id)");
        $insStmt->execute([
            ':session_id' => $sessionId,
            ':article_id' => $articleId,
            ':user_id' => $userId,
        ]);

        return 'saved';
    }

    public function remove(int $articleId, int $userId): void
    {
        $stmt = $this->db->prepare("DELETE FROM bookmarks WHERE article_id = ? AND user_id = ?");
        $stmt->execute([$articleId, $userId]);
    }

    public function removeAllForUser(int $userId): void
    {
        $stmt = $this->db->prepare("DELETE FROM bookmarks WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
}
