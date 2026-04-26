<?php
// ============================================================
// api/about_save.php — Lưu / cập nhật section trang About (Admin)
// AJAX POST: section_key + section_data (JSON string)
// ============================================================
session_start();
require_once '../config/db.php';
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');

if (!isAdmin()) {
    jsonResponse(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
}

$allowed_keys = ['hero','stats','mission','values','story','features','team','cta'];

$section_key  = trim($_POST['section_key']  ?? '');
$section_data = trim($_POST['section_data'] ?? '');

if (!in_array($section_key, $allowed_keys)) {
    jsonResponse(['success' => false, 'message' => 'Section key không hợp lệ.']);
}

// Validate JSON
$decoded = json_decode($section_data, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    jsonResponse(['success' => false, 'message' => 'Dữ liệu JSON không hợp lệ.']);
}

// Re-encode để chuẩn hoá
$clean_data = json_encode($decoded, JSON_UNESCAPED_UNICODE);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO about_sections (section_key, section_data, updated_by)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE
           section_data = VALUES(section_data),
           updated_by   = VALUES(updated_by),
           updated_at   = CURRENT_TIMESTAMP"
    );
    $stmt->execute([$section_key, $clean_data, $_SESSION['user_id']]);
    jsonResponse(['success' => true, 'message' => 'Đã lưu thành công!', 'data' => $decoded]);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()], 500);
}
