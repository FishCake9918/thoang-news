<?php

namespace App\Controllers;

use App\Models\SearchModel;
use PDOException;

class SearchController
{
    private SearchModel $search;

    public function __construct(SearchModel $search)
    {
        $this->search = $search;
    }

    public function index(string $keyword): array
    {
        try {
            $results = $this->search->articles($keyword);
        } catch (PDOException $e) {
            $results = [];
        }

        return [
            'search_results' => $results,
            'total' => count($results),
        ];
    }
}
