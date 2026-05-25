<?php
// verify.php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

$page_title = 'Xác thực tài khoản — Thoáng.vn';
include 'partials/header.php';

$message = '';
$status_class = 'alert-danger';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    
    // Tìm kiếm xem token có hợp lệ không
    $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE verify_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        if ($user['is_verified'] == 1) {
            $message = 'Tài khoản này đã được kích hoạt từ trước.';
            $status_class = 'alert-warning';
        } else {
            // Cập nhật trạng thái thành công, xóa verify_token
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE id = ?");
            if ($update->execute([$user['id']])) {
                $message = 'Chúc mừng! Tài khoản của bạn đã được xác thực thành công. Bây giờ bạn có thể đăng nhập vào hệ thống.';
                $status_class = 'alert-success';
            } else {
                $message = 'Có lỗi trong quá trình xử lý xác thực trên hệ thống.';
            }
        }
    } else {
        $message = 'Mã xác thực không hợp lệ hoặc liên kết kích hoạt này đã hết hạn.';
    }
} else {
    $message = 'Yêu cầu không hợp lệ. Thiếu mã xác thực kích hoạt.';
}
?>

<div class="flex-grow-1 d-flex align-items-center justify-content-center py-5 px-3">
    <div class="auth-card text-center" style="max-width: 500px;">
        <div class="auth-title mb-3">Xác thực tài khoản</div>
        <div class="alert <?= $status_class ?> py-3 px-4" style="font-size:14px; border-radius:4px;">
            <?= $message ?>
        </div>
        <div class="mt-4">
            <a href="login.php" class="btn-login text-decoration-none d-inline-block">Đăng nhập ngay</a>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>