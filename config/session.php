<?php
// ============================================================
// config/session.php — Hàm trợ giúp xác thực / session
// ============================================================

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(string $redirect = 'login.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function requireAdmin(string $redirect = 'login.php'): void {
    if (!isAdmin()) {
        header("Location: $redirect");
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'        => $_SESSION['user_id'],
        'username'  => $_SESSION['username'],
        'email'     => $_SESSION['email'],
        'full_name' => $_SESSION['full_name'] ?? '',
        'role'      => $_SESSION['role'],
    ];
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function viDate(): string {
    $days = ['Chủ Nhật','Thứ Hai','Thứ Ba','Thứ Tư','Thứ Năm','Thứ Sáu','Thứ Bảy'];
    return $days[date('w')] . ', ' . date('d') . ' tháng ' . date('m') . ', ' . date('Y');
}
