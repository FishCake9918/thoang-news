<?php

namespace App\Controllers;

use App\Models\SavedArticleModel;
use PDOException;

class SavedController
{
    private SavedArticleModel $savedArticles;

    public function __construct(SavedArticleModel $savedArticles)
    {
        $this->savedArticles = $savedArticles;
    }

    public function index(int $userId, string $category, string $keyword): array
    {
        try {
            $articles = $this->savedArticles->savedByUser($userId, $category, $keyword);
        } catch (PDOException $e) {
            $articles = [];
        }

        return [
            'saved_articles' => $articles,
            'total' => count($articles),
        ];
    }
}
