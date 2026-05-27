<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Vui lòng cung cấp một địa chỉ email hợp lệ.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $update->execute([$token, $expires, $user['id']]);
                
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                $subject = "Yêu cầu khôi phục mật khẩu — Thoáng.vn";
                $body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #d9d9d3; border-radius: 8px;'>
                        <h3 style='color: #c41230; text-align: center;'>Yêu cầu đặt lại mật khẩu</h3>
                        <p>Xin chào <strong>{$user['full_name']}</strong>,</p>
                        <p>Hệ thống nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Liên kết khôi phục này chỉ có hiệu lực sử dụng trong vòng <strong>15 phút</strong>. Nhấn vào nút bên dưới để đổi mật khẩu:</p>
                        <div style='text-align: center; margin: 30px 0;'><a href='$reset_link' style='background-color: #c41230; color: #ffffff; text-decoration: none; padding: 12px 25px; font-weight: bold; border-radius: 4px; display: inline-block;'>ĐẶT LẠI MẬT KHẨU</a></div>
                        <p style='font-size: 11px; color: #767676;'>Nếu bạn không yêu cầu hành động này, vui lòng bỏ qua email bảo mật này một cách an toàn.</p>
                        <hr style='border: none; border-top: 1px solid #d9d9d3; margin: 20px 0;'><p style='font-size: 11px; color: #767676; text-align: center;'>Thoáng.vn — Lướt nhanh nắm gọn</p>
                    </div>";
                
                sendSystemEmail($email, $subject, $body);
            }
            
            $success = 'Nếu email của bạn tồn tại trên hệ thống, một đường dẫn khôi phục mật khẩu đã được gửi đi. Vui lòng kiểm tra hộp thư.';
        } catch (PDOException $e) {
            $error = 'Hệ thống đang bận, vui lòng thử lại sau.';
        }
    }
}