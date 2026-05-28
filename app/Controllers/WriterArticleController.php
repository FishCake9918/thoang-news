<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ArticleModel;

class WriterArticleController extends Controller
{
    private ArticleModel $articles;

    public function __construct(ArticleModel $articles)
    {
        $this->articles = $articles;
    }

    public function handleDeleteRequest(int $authorId): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $action = $_POST['action'] ?? '';
        $articleId = (int)($_POST['article_id'] ?? 0);

        if ($action !== 'delete_article') {
            return null;
        }

        if ($articleId <= 0) {
            return 'Bài viết không hợp lệ.';
        }

        $this->articles->deleteByAuthor($articleId, $authorId);
        $this->redirect('dashboard_writer.php');
    }
}
