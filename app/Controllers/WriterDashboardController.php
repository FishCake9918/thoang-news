<?php

namespace App\Controllers;

use App\Models\WriterDashboardModel;
use PDOException;

class WriterDashboardController
{
    private WriterDashboardModel $dashboard;

    public function __construct(WriterDashboardModel $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    public function show(int $authorId): array
    {
        try {
            return $this->dashboard->buildDashboardData($authorId) + ['error' => ''];
        } catch (PDOException $e) {
            return [
                'articles_by_status' => [
                    'request' => [],
                    'Approved' => [],
                    'disapproved' => [],
                ],
                'stats' => [
                    'total' => 0,
                    'request' => 0,
                    'Approved' => 0,
                    'disapproved' => 0,
                    'total_views' => 0,
                ],
                'cat_views_data' => [],
                'error' => 'Không thể tải danh sách bài viết: ' . $e->getMessage(),
            ];
        }
    }
}
