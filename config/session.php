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

function requireLogin(string $redirect = 'login.php'): void
{
    Auth::requireLogin($redirect);
}

function requireAdmin(string $redirect = 'login.php'): void
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

function viDate(): string
{
    return DateHelper::vietnameseToday();
}
