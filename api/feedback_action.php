<?php
// ============================================================
// api/feedback_action.php — Xử lý phản hồi (Admin only)
// action: reply | mark_done | mark_pending | delete
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

$action      = trim($_POST['action'] ?? '');
$feedback_id = (int)($_POST['feedback_id'] ?? 0);

if ($feedback_id <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID không hợp lệ.']);
}

try {
    // Check exists
    $chk = $pdo->prepare("SELECT id FROM feedback WHERE id = ?");
    $chk->execute([$feedback_id]);
    if (!$chk->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Góp ý không tồn tại.'], 404);
    }

    switch ($action) {
        case 'reply':
            $reply = trim($_POST['reply'] ?? '');
            if (empty($reply)) {
                jsonResponse(['success' => false, 'message' => 'Nội dung trả lời không được để trống.']);
            }
            $stmt = $pdo->prepare(
                "UPDATE feedback SET admin_reply = ?, status = 'replied', replied_at = NOW() WHERE id = ?"
            );
            $stmt->execute([$reply, $feedback_id]);
            jsonResponse(['success' => true, 'message' => 'Đã gửi phản hồi.']);
            break;

        case 'mark_done':
            $stmt = $pdo->prepare("UPDATE feedback SET status = 'done' WHERE id = ?");
            $stmt->execute([$feedback_id]);
            jsonResponse(['success' => true, 'message' => 'Đã đánh dấu hoàn tất.']);
            break;

        case 'mark_pending':
            $stmt = $pdo->prepare("UPDATE feedback SET status = 'pending', admin_reply = NULL, replied_at = NULL WHERE id = ?");
            $stmt->execute([$feedback_id]);
            jsonResponse(['success' => true, 'message' => 'Đã chuyển về chờ xử lý.']);
            break;

        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
            $stmt->execute([$feedback_id]);
            jsonResponse(['success' => true, 'message' => 'Đã xoá góp ý.']);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Action không hợp lệ.']);
    }
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()], 500);
}
