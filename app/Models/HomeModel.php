<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class HomeModel extends Model
{
    public function topArticles(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT id, title, view_count
            FROM articles
            WHERE status = 'Approved'
            ORDER BY view_count DESC, published_at DESC, created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function latestArticles(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT id, title, created_at, published_at
            FROM articles
            WHERE status = 'Approved'
            ORDER BY published_at DESC, created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
