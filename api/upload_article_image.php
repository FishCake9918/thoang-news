<?php
session_start();
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || $_SESSION['role'] !== 'writer') {
    jsonResponse(['success' => false, 'message' => 'Bạn không có quyền tải ảnh.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) {
    jsonResponse(['success' => false, 'message' => 'Vui lòng chọn hình ảnh cần tải lên.'], 400);
}

$file = $_FILES['image'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(['success' => false, 'message' => 'Không thể tải ảnh lên.'], 400);
}

if ($file['size'] > 3 * 1024 * 1024) {
    jsonResponse(['success' => false, 'message' => 'Ảnh không được vượt quá 3MB.'], 400);
}

$info = @getimagesize($file['tmp_name']);
$allowed = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG => 'png',
    IMAGETYPE_WEBP => 'webp',
    IMAGETYPE_GIF => 'gif',
];

if (!$info || !isset($allowed[$info[2]])) {
    jsonResponse(['success' => false, 'message' => 'File tải lên phải là ảnh JPG, PNG, WEBP hoặc GIF.'], 400);
}

$uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'articles';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
    jsonResponse(['success' => false, 'message' => 'Không thể tạo thư mục lưu ảnh.'], 500);
}

$filename = 'article_' . (int)$_SESSION['user_id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$info[2]];
$target = $uploadDir . DIRECTORY_SEPARATOR . $filename;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    jsonResponse(['success' => false, 'message' => 'Không thể lưu ảnh lên máy chủ.'], 500);
}

jsonResponse([
    'success' => true,
    'url' => 'uploads/articles/' . $filename,
]);
