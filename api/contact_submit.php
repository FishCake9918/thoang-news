<?php
session_start();
require_once '../config/db.php';
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
}

if (!isLoggedIn()) {
    jsonResponse([
        'success' => false,
        'auth_required' => true,
        'message' => 'Vui lòng đăng nhập hoặc đăng ký để gửi góp ý.'
    ], 401);
}

$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($email === '' || $subject === '' || $message === '') {
    jsonResponse(['success' => false, 'message' => 'Vui lòng điền đầy đủ tất cả các trường.']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Địa chỉ email không hợp lệ.']);
}
if (mb_strlen($subject, 'UTF-8') > 200) {
    jsonResponse(['success' => false, 'message' => 'Tiêu đề không được vượt quá 200 ký tự.']);
}
if (mb_strlen($message, 'UTF-8') < 10) {
    jsonResponse(['success' => false, 'message' => 'Nội dung quá ngắn, tối thiểu 10 ký tự.']);
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO feedback (user_id, sender_email, subject, message) VALUES (?,?,?,?)"
    );
    $stmt->execute([(int)$_SESSION['user_id'], $email, $subject, $message]);
    jsonResponse(['success' => true, 'message' => 'Cảm ơn! Chúng tôi đã nhận được góp ý của bạn.']);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Lỗi hệ thống. Vui lòng thử lại sau.'], 500);
}
