<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ArticleModel;
use PDOException;

class FeedApiController extends Controller
{
    private ArticleModel $articles;

    public function __construct(ArticleModel $articles)
    {
        $this->articles = $articles;
    }

    public function index(string $category): void
    {
        $allowedCategories = ['all', 'world', 'biz', 'tech', 'sport', 'life', 'edu', 'other'];

        if (!in_array($category, $allowedCategories, true)) {
            $category = 'all';
        }

        try {
            $userId = Auth::check() ? Auth::id() : null;
            $this->json($this->articles->approvedFeed($category, $userId));
        } catch (PDOException $e) {
            $this->json(['error' => 'Lỗi truy vấn SQL: ' . $e->getMessage()], 500);
        }
    }
}
