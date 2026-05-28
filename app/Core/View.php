<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = []): void
    {
        $path = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($path)) {
            throw new \RuntimeException('View not found: ' . $view);
        }

        extract($data, EXTR_SKIP);
        require $path;
    }
}
