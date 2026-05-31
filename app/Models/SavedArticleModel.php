<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SavedArticleModel extends Model
{
    public function savedByUser(int $userId, string $category = 'all', string $keyword = ''): array
    {
        $where = ["a.status = 'Approved'", "b.user_id = ?"];
        $params = [$userId];

        if ($category !== 'all' && $category !== '') {
            $where[] = "c.slug = ?";
            $params[] = $category;
        }

        $keyword = trim($keyword);
        if ($keyword !== '') {
            $words = preg_split('/\s+/', $keyword);
            foreach ($words as $word) {
                $where[] = "(a.title LIKE ?)";
                $like = "%$word%";
                $params[] = $like;
            }
        }

        $sql = "
            SELECT
                a.id,
                a.title,
                a.summary,
                a.source AS source_name,
                a.created_at,
                c.slug AS category,
                c.name AS category_name,
                c.color_bg,
                c.color_text,
                MAX(b.created_at) AS saved_at
            FROM bookmarks b
            INNER JOIN articles a ON a.id = b.article_id
            LEFT JOIN categories c ON c.id = a.category_id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY a.id, a.title, a.summary, a.source, a.created_at, c.slug, c.name, c.color_bg, c.color_text
            ORDER BY saved_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
