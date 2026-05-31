<?php

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin(): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function isWriter(): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'writer';
    }

    public static function id(): int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    }

    public static function role(): string
    {
        return $_SESSION['role'] ?? '';
    }

    public static function requireLogin(string $redirect = 'login'): void
    {
        if (!self::check()) {
            $location = $redirect;
            $path = parse_url($redirect, PHP_URL_PATH) ?? '';
            $queryString = parse_url($redirect, PHP_URL_QUERY) ?? '';
            $fragment = parse_url($redirect, PHP_URL_FRAGMENT) ?? '';

            if ($path !== '' && strpos($redirect, '://') === false && !str_starts_with($redirect, 'mailto:') && !str_starts_with($redirect, 'tel:')) {
                if (str_ends_with(strtolower($path), '.php')) {
                    $path = preg_replace('/\.php$/i', '', trim($path, '/'));
                }

                if ($path !== '' && !str_contains($redirect, '?route=')) {
                    parse_str($queryString, $queryParams);
                    $location = route(trim($path, '/'), $queryParams, $fragment);
                }
            }

            header('Location: ' . $location);
            exit;
        }
    }

    public static function requireAdmin(string $redirect = 'login'): void
    {
        if (!self::isAdmin()) {
            $location = $redirect;
            $path = parse_url($redirect, PHP_URL_PATH) ?? '';
            $queryString = parse_url($redirect, PHP_URL_QUERY) ?? '';
            $fragment = parse_url($redirect, PHP_URL_FRAGMENT) ?? '';

            if ($path !== '' && strpos($redirect, '://') === false && !str_starts_with($redirect, 'mailto:') && !str_starts_with($redirect, 'tel:')) {
                if (str_ends_with(strtolower($path), '.php')) {
                    $path = preg_replace('/\.php$/i', '', trim($path, '/'));
                }

                if ($path !== '' && !str_contains($redirect, '?route=')) {
                    parse_str($queryString, $queryParams);
                    $location = route(trim($path, '/'), $queryParams, $fragment);
                }
            }

            header('Location: ' . $location);
            exit;
        }
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => self::id(),
            'username' => $_SESSION['username'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'full_name' => $_SESSION['full_name'] ?? '',
            'avatar' => $_SESSION['avatar'] ?? 'images/avatars/avatar-01.svg',
            'role' => self::role(),
            'theme_preference' => $_SESSION['theme_preference'] ?? 'light',
            'article_font_size' => (int)($_SESSION['article_font_size'] ?? 16),
        ];
    }
}
