<?php
// ============================================================
// api/contact_submit.php — Tiếp nhận form liên hệ (AJAX POST)
// ============================================================
session_start();
require_once '../config/db.php';
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
}

$email   = trim($_POST['email']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
if (empty($email) || empty($subject) || empty($message)) {
    jsonResponse(['success' => false, 'message' => 'Vui lòng điền đầy đủ tất cả các trường.']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Địa chỉ email không hợp lệ.']);
}
if (strlen($subject) > 200) {
    jsonResponse(['success' => false, 'message' => 'Tiêu đề không được vượt quá 200 ký tự.']);
}
if (strlen($message) < 10) {
    jsonResponse(['success' => false, 'message' => 'Nội dung quá ngắn (tối thiểu 10 ký tự).']);
}

$user_id = isLoggedIn() ? $_SESSION['user_id'] : null;

try {
    $stmt = $pdo->prepare(
        "INSERT INTO feedback (user_id, sender_email, subject, message) VALUES (?,?,?,?)"
    );
    $stmt->execute([$user_id, $email, $subject, $message]);
    jsonResponse(['success' => true, 'message' => 'Cảm ơn! Chúng tôi đã nhận được góp ý của bạn.']);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Lỗi hệ thống. Vui lòng thử lại sau.'], 500);
}
