<?php
// ============================================================
// api/add_article.php — API xử lý thêm bài viết mới (AJAX POST)
// ============================================================
session_start();
require_once '../config/db.php';
require_once '../config/session.php';

// Ép kiểu phản hồi đầu ra luôn luôn là JSON chuẩn UTF-8
header('Content-Type: application/json; charset=utf-8');

// Kiểm tra quyền hạn (Chỉ bảo vệ cấp Admin và Writer được quyền gọi API này)
if (!isLoggedIn() || ($_SESSION['role'] !== 'writer')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Lỗi bảo mật: Bạn không có quyền truy cập chức năng này.']);
    exit;
}

// Kiểm tra phương thức yêu cầu phải là POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức truyền tải dữ liệu trái phép.']);
    exit;
}

// Tiếp nhận chuỗi JSON thô gửi từ trình duyệt và giải mã thành mảng
$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Định dạng gói tin dữ liệu không hợp lệ.']);
    exit;
}

// Làm sạch dữ liệu đầu vào chống các lỗ hổng Injection cơ bản
$title       = isset($inputData['title']) ? trim($inputData['title']) : '';
$category_id = isset($inputData['category_id']) ? intval($inputData['category_id']) : 0;
$source      = isset($inputData['source']) && trim($inputData['source']) !== '' ? trim($inputData['source']) : 'Thoáng.vn';
$summary     = isset($inputData['summary']) ? trim($inputData['summary']) : '';
$content     = isset($inputData['content']) ? trim($inputData['content']) : '';
$author_id   = intval($_SESSION['user_id']);

// Xác thực nghiệp vụ dữ liệu bắt buộc (Validation)
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tiêu đề bài viết.']);
    exit;
}
if ($category_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn chủ đề danh mục hợp lệ.']);
    exit;
}
if (empty($summary)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đoạn tóm tắt bài viết.']);
    exit;
}
if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập nội dung chi tiết bài viết.']);
    exit;
}

try {
    // Câu lệnh SQL với thứ tự các cột được sắp xếp rõ ràng, tường minh
    $stmt = $pdo->prepare("
        INSERT INTO articles (category_id, title, summary, content, source, status, view_count, created_at, updated_at) 
        VALUES (:category_id, :title, :summary, :content, :source, :status, :view_count, NOW(), NOW())
    ");
    
    // Sử dụng liên kết tham số bằng tên (Named Parameters) để đảm bảo dữ liệu không bao giờ bị truyền lệch cột
    $result = $stmt->execute([
        ':category_id' => $category_id,
        ':title'       => $title,
        ':summary'     => $summary,
        ':content'     => $content,
        ':source'      => $source,
        ':status'      => 'published',
        ':view_count'  => 0
    ]);

    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Bài viết đã được xuất bản thành công! Bạn có thể ra trang chủ để kiểm tra.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Không thể ghi nhận thông tin bài viết vào cơ sở dữ liệu.'
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (PDOException $e) {
    // Trả về thông báo lỗi chi tiết của MySQL để dễ dàng bắt bệnh nếu có lỗi phát sinh
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi CSDL: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>