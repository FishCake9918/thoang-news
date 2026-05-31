<?php

namespace App\Controllers;

use App\Models\ArticleModel;
use App\Models\CategoryModel;
use PDOException;

class WriterFormController
{
    private ArticleModel $articles;
    private CategoryModel $categories;

    public function __construct(ArticleModel $articles, CategoryModel $categories)
    {
        $this->articles = $articles;
        $this->categories = $categories;
    }

    public function formData(int $articleId, int $authorId): array
    {
        $article = [
            'id' => 0,
            'title' => '',
            'category_id' => '',
            'summary' => '',
            'content' => '',
            'tags' => '',
            'image_url' => '',
        ];

        if ($articleId > 0) {
            try {
                $found = $this->articles->findByAuthor($articleId, $authorId);

                if (!$found) {
                    return ['redirect' => route('dashboard_writer')];
                }

                $article = array_merge($article, $found);
            } catch (PDOException $e) {
                return ['redirect' => route('dashboard_writer')];
            }
        }

        try {
            $this->categories->ensureOtherCategory();
            $categories = $this->categories->all();
            $category_tree = $this->categories->activeTree();
        } catch (PDOException $e) {
            $categories = [];
            $category_tree = [];
        }

        return [
            'article' => $article,
            'categories' => $categories,
            'category_tree' => $category_tree,
            'redirect' => null,
        ];
    }
}
