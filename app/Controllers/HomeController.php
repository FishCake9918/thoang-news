<?php

namespace App\Controllers;

use App\Models\HomeModel;
use PDOException;

class HomeController
{
    private HomeModel $home;

    public function __construct(HomeModel $home)
    {
        $this->home = $home;
    }

    public function index(): array
    {
        try {
            return [
                'top_articles' => $this->home->topArticles(),
                'latest_articles' => $this->home->latestArticles(),
            ];
        } catch (PDOException $e) {
            return [
                'top_articles' => [],
                'latest_articles' => [],
            ];
        }
    }
}
