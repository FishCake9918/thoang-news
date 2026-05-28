<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class WriterDashboardModel extends Model
{
    public function buildDashboardData(int $authorId): array
    {
        $articlesByStatus = [
            'request' => [],
            'Approved' => [],
            'disapproved' => [],
        ];

        $stats = [
            'total' => 0,
            'request' => 0,
            'Approved' => 0,
            'disapproved' => 0,
            'total_views' => 0,
        ];

        $catViewsData = [];

        $stmt = $this->db->prepare("
            SELECT
                a.id,
                a.title,
                a.summary,
                a.status,
                a.view_count,
                a.created_at,
                a.updated_at,
                a.published_at,
                a.tags,
                a.source,
                c.name AS category_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.author_id = ?
            ORDER BY
                FIELD(a.status, 'request', 'Approved', 'disapproved'),
                a.updated_at DESC,
                a.created_at DESC
        ");
        $stmt->execute([$authorId]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($articles as $article) {
            if ($article['status'] === 'Approved') {
                $key = 'Approved';
                $categoryName = $article['category_name'] ?? 'Chưa phân loại';
                $catViewsData[$categoryName] = ($catViewsData[$categoryName] ?? 0) + (int)$article['view_count'];
            } elseif ($article['status'] === 'disapproved') {
                $key = 'disapproved';
            } else {
                $key = 'request';
            }

            $articlesByStatus[$key][] = $article;
            $stats['total']++;
            $stats[$key]++;
            $stats['total_views'] += (int)$article['view_count'];
        }

        return [
            'articles_by_status' => $articlesByStatus,
            'stats' => $stats,
            'cat_views_data' => $catViewsData,
        ];
    }
}
