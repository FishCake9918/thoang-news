<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class SearchModel extends Model
{
    public function articles(string $keyword): array
    {
        if ($keyword === '') {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT
                a.id,
                a.title,
                a.summary,
                a.source AS source_name,
                a.created_at,
                a.published_at,
                c.slug AS category,
                c.name AS category_name,
                c.color_bg,
                c.color_text
            FROM articles a
            LEFT JOIN categories c ON c.id = a.category_id
            WHERE a.status = 'Approved'
              AND (a.title LIKE ? OR a.summary LIKE ?)
            ORDER BY a.published_at DESC, a.created_at DESC
        ");
        $like = "%$keyword%";
        $stmt->execute([$like, $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
