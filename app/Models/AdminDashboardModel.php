<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class AdminDashboardModel extends Model
{
    public function buildDashboardData(): array
    {
        return [
            'users' => $this->users(),
            'articles' => $this->articles(),
            'feedbacks' => $this->feedbacks(),
            'fcnt' => $this->feedbackCounts(),
            'stats' => $this->stats(),
            'cat_stats' => $this->categoryStats(),
            'top_articles' => $this->topArticles(),
            'writer_stats' => $this->writerStats(),
            'top_bookmarked' => $this->topBookmarked(),
            'recent_comments' => $this->recentComments(),
            'categories' => $this->categories(),
        ];
    }

    private function users(): array
    {
        $stmt = $this->db->query("SELECT id, username, email, full_name, role, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function articles(): array
    {
        $stmt = $this->db->query("
            SELECT
                a.id,
                a.title,
                a.status,
                a.created_at,
                a.updated_at,
                a.published_at,
                a.source,
                a.view_count,
                u.username,
                u.full_name,
                c.name AS category_name
            FROM articles a
            LEFT JOIN users u ON a.author_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            ORDER BY FIELD(a.status, 'request', 'Approved', 'disapproved'), a.updated_at DESC, a.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function feedbacks(): array
    {
        $stmt = $this->db->query("
            SELECT f.*, u.username AS sender_name
            FROM feedback f
            LEFT JOIN users u ON f.user_id = u.id
            ORDER BY FIELD(f.status, 'pending', 'replied', 'done'), f.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function feedbackCounts(): array
    {
        $counts = ['pending' => 0, 'replied' => 0, 'done' => 0];

        foreach ($this->feedbacks() as $feedback) {
            $status = $feedback['status'] ?? 'pending';
            if (isset($counts[$status])) {
                $counts[$status]++;
            }
        }

        return $counts;
    }

    private function stats(): array
    {
        return [
            'total_views' => (int)($this->db->query("SELECT COALESCE(SUM(view_count), 0) FROM articles WHERE status = 'Approved'")->fetchColumn() ?: 0),
            'total_articles' => (int)($this->db->query("SELECT COUNT(id) FROM articles WHERE status = 'Approved'")->fetchColumn() ?: 0),
            'total_users' => (int)($this->db->query("SELECT COUNT(id) FROM users")->fetchColumn() ?: 0),
            'total_bookmarks' => (int)($this->db->query("SELECT COUNT(id) FROM bookmarks")->fetchColumn() ?: 0),
            'pending_articles' => (int)($this->db->query("SELECT COUNT(id) FROM articles WHERE status = 'request'")->fetchColumn() ?: 0),
            'disapproved_articles' => (int)($this->db->query("SELECT COUNT(id) FROM articles WHERE status = 'disapproved'")->fetchColumn() ?: 0),
        ];
    }

    private function categoryStats(): array
    {
        $stmt = $this->db->query("
            SELECT
                parent.id,
                parent.name,
                COUNT(a.id) AS article_count,
                COALESCE(SUM(a.view_count), 0) AS total_views
            FROM categories parent
            LEFT JOIN categories child
                ON child.parent_id = parent.id
            LEFT JOIN articles a
                ON a.status = 'Approved'
                AND (
                    a.category_id = parent.id
                    OR a.category_id = child.id
                )
            WHERE parent.parent_id IS NULL
              AND parent.is_active = 1
            GROUP BY parent.id, parent.name
            ORDER BY total_views DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function topArticles(): array
    {
        $stmt = $this->db->query("
            SELECT id, title, view_count, published_at, created_at
            FROM articles
            WHERE status = 'Approved'
            ORDER BY view_count DESC, published_at DESC, created_at DESC
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function writerStats(): array
    {
        $stmt = $this->db->query("
            SELECT u.username, u.full_name,
                   SUM(CASE WHEN a.status = 'Approved' THEN 1 ELSE 0 END) AS approved_count,
                   SUM(CASE WHEN a.status = 'request' THEN 1 ELSE 0 END) AS pending_count,
                   SUM(CASE WHEN a.status = 'disapproved' THEN 1 ELSE 0 END) AS disapproved_count
            FROM users u
            JOIN articles a ON u.id = a.author_id
            WHERE u.role IN ('writer', 'admin')
            GROUP BY u.id, u.username, u.full_name
            ORDER BY approved_count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function topBookmarked(): array
    {
        $stmt = $this->db->query("
            SELECT a.id, a.title, COUNT(b.id) AS saves_count
            FROM articles a
            JOIN bookmarks b ON a.id = b.article_id
            WHERE a.status = 'Approved'
            GROUP BY a.id, a.title
            ORDER BY saves_count DESC
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function recentComments(): array
    {
        $stmt = $this->db->query("
            SELECT c.id, c.content, c.created_at, u.username, u.full_name, a.title, a.id AS article_id
            FROM comments c
            JOIN users u ON c.user_id = u.id
            JOIN articles a ON c.article_id = a.id
            ORDER BY c.created_at DESC
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function categories(): array
    {
        $stmt = $this->db->query("
            SELECT
                c.id,
                c.name,
                c.slug,
                c.parent_id,
                c.sort_order,
                c.is_active,
                p.name AS parent_name,
                COUNT(a.id) AS article_count
            FROM categories c
            LEFT JOIN categories p ON c.parent_id = p.id
            LEFT JOIN articles a ON a.category_id = c.id
            GROUP BY c.id, c.name, c.slug, c.parent_id, c.sort_order, c.is_active, p.name
            ORDER BY c.parent_id IS NOT NULL, c.sort_order ASC, c.id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
