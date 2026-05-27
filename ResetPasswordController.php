<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    
    if (strlen($password) < 6) {
        $error = 'Mật khẩu mới phải có tối thiểu từ 6 ký tự.';
    } elseif ($password !== $confirm) {
        $error = 'Mật khẩu xác nhận nhập lại không trùng khớp.';
    } else {
        try {
            $new_hash = password_hash($password, PASSWORD_BCRYPT);
            // Biến $user được lấy từ file reset_password.php gọi file này
            $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            if ($update->execute([$new_hash, $user['id']])) {
                $success = 'Đặt lại mật khẩu thành công! Bạn đang được chuyển hướng về trang đăng nhập...';
                header("refresh:2;url=login.php");
            }
        } catch (PDOException $e) { $error = 'Đổi mật khẩu thất bại. Vui lòng thử lại.'; }
    }
}