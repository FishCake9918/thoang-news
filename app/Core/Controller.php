<?php

namespace App\Core;

abstract class Controller
{
    protected function redirect(string $url): void
    {
        $location = $url;

        if (strpos($url, '://') === false && strpos($url, 'mailto:') === false && strpos($url, 'tel:') === false) {
            $path = parse_url($url, PHP_URL_PATH) ?? '';
            $queryString = parse_url($url, PHP_URL_QUERY) ?? '';
            $fragment = parse_url($url, PHP_URL_FRAGMENT) ?? '';

            if ($path !== '' && str_ends_with(strtolower($path), '.php')) {
                $routePath = preg_replace('/\.php$/i', '', trim($path, '/'));
                parse_str($queryString, $queryParams);
                $location = route($routePath, $queryParams, $fragment);
            }
        }

        header('Location: ' . $location);
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
