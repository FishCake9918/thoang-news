<?php

namespace App\Controllers;

use App\Models\AdminDashboardModel;
use PDOException;

class AdminDashboardController
{
    private AdminDashboardModel $dashboard;

    public function __construct(AdminDashboardModel $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    public function show(): array
    {
        try {
            return $this->dashboard->buildDashboardData() + ['error' => ''];
        } catch (PDOException $e) {
            return [
                'users' => [],
                'articles' => [],
                'feedbacks' => [],
                'fcnt' => ['pending' => 0, 'replied' => 0, 'done' => 0],
                'stats' => [
                    'total_views' => 0,
                    'total_articles' => 0,
                    'total_users' => 0,
                    'total_bookmarks' => 0,
                    'pending_articles' => 0,
                    'disapproved_articles' => 0,
                ],
                'cat_stats' => [],
                'top_articles' => [],
                'writer_stats' => [],
                'top_bookmarked' => [],
                'recent_comments' => [],
                'categories' => [],
                'error' => 'Không thể tải dữ liệu dashboard: ' . $e->getMessage(),
            ];
        }
    }
}
