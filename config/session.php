<?php

require_once __DIR__ . '/../app/autoload.php';

use App\Core\Auth;
use App\Core\DateHelper;
use App\Core\Request;
use App\Core\Response;

function isLoggedIn(): bool
{
    return Auth::check();
}

function isAdmin(): bool
{
    return Auth::isAdmin();
}

function requireLogin(string $redirect = 'login'): void
{
    Auth::requireLogin($redirect);
}

function requireAdmin(string $redirect = 'login'): void
{
    Auth::requireAdmin($redirect);
}

function getCurrentUser(): ?array
{
    return Auth::user();
}

function jsonResponse(array $data, int $code = 200): void
{
    Response::json($data, $code);
}

function is_ajax_request(): bool
{
    return Request::isAjax();
}

function currentRequestPage(): string
{
    if (!empty($_GET['url'])) {
        $path = parse_url((string)$_GET['url'], PHP_URL_PATH) ?? '';
        return basename(trim($path, '/')) ?: 'index.php';
    }

    if (!empty($_GET['route'])) {
        $path = parse_url((string)$_GET['route'], PHP_URL_PATH) ?? '';
        return basename(trim($path, '/')) ?: 'index.php';
    }

    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    $current = basename(trim($uri, '/')) ?: 'index.php';

    return $current;
}

function route(string $path = '', array $params = [], string $fragment = ''): string
{
    $path = trim($path, '/');
    if (str_ends_with(strtolower($path), '.php')) {
        $path = preg_replace('/\.php$/i', '', $path);
    }

    if ($path === '' || $path === 'index.php') {
        $url = 'index.php';
    } else {
        $url = 'index.php?route=' . urlencode($path);
    }

    if (!empty($params)) {
        $separator = strpos($url, '?') === false ? '?' : '&';
        $url .= $separator . http_build_query($params);
    }

    if ($fragment !== '') {
        $url .= '#' . ltrim($fragment, '#');
    }

    return $url;
}

function viDate(): string
{
    return DateHelper::vietnameseToday();
}
