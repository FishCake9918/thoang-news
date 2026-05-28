<?php

namespace App\Controllers;

use App\Models\ArticleModel;
use PDOException;

class ArticlePageController
{
    private ArticleModel $articles;

    public function __construct(ArticleModel $articles)
    {
        $this->articles = $articles;
    }

    public function show(int $articleId, bool $canPreview): array
    {
        $article = $this->articles->find($articleId);

        if (!$article) {
            return ['error_status' => 404, 'error_message' => 'Không tìm thấy bài viết.'];
        }

        if ($article['status'] !== 'Approved' && !$canPreview) {
            return ['error_status' => 403, 'error_message' => 'Bạn không có quyền xem bài viết này.'];
        }

        $comments = [];
        $nextArticle = null;
        $prevArticle = null;

        if ($article['status'] === 'Approved') {
            $this->articles->incrementViews($articleId);
            $article['view_count'] = (int)$article['view_count'] + 1;
            $comments = $this->articles->commentsForArticle($articleId);

            $nextId = $this->articles->nextApprovedId($articleId);
            $prevId = $this->articles->previousApprovedId($articleId);
            $nextArticle = $nextId ? ['id' => $nextId] : null;
            $prevArticle = $prevId ? ['id' => $prevId] : null;
        }

        return [
            'article' => $article,
            'comments' => $comments,
            'nextArticle' => $nextArticle,
            'prevArticle' => $prevArticle,
            'error_status' => null,
            'error_message' => '',
        ];
    }

    public function safeShow(int $articleId, bool $canPreview): array
    {
        try {
            return $this->show($articleId, $canPreview);
        } catch (PDOException $e) {
            return ['error_status' => 500, 'error_message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }
}
