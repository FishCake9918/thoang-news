<?php
// ============================================================
// config/db.example.php — File mẫu kết nối database
// Hướng dẫn: copy file này thành db.php rồi điền thông tin
//   Windows: copy config\db.example.php config\db.php
// KHÔNG sửa file này — chỉ sửa file db.php sau khi copy
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');   // XAMPP mặc định: root
define('DB_PASS', 'your_password');   // XAMPP mặc định: để trống ''
define('DB_NAME', 'thoang_vn');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối CSDL: ' . $e->getMessage()]);
    exit;
}