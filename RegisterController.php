<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm'] ?? '';

    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ tất cả các trường.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Địa chỉ email không hợp lệ.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirm) {
        $error = 'Xác nhận mật khẩu không khớp.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $error = 'Tên đăng nhập chỉ gồm chữ cái, số, dấu gạch dưới (3–30 ký tự).';
    } else {
        try {
            $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $chk->execute([$username, $email]);
            if ($chk->fetch()) {
                $error = 'Tên đăng nhập hoặc email đã được sử dụng.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $token = bin2hex(random_bytes(32)); 
                
                $ins  = $pdo->prepare(
                    "INSERT INTO users (username, email, password, full_name, role, is_verified, verify_token) VALUES (?,?,?,?,'user', 0, ?)"
                );
                
                if ($ins->execute([$username, $email, $hash, $full_name, $token])) {
                    
                    $verify_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify.php?token=" . $token;
                    $subject = "Kích hoạt tài khoản thành viên của bạn — Thoáng.vn";
                    $body = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #d9d9d3; border-radius: 8px;'>
                            <h2 style='color: #1a2744; text-align: center;'>Chào mừng bạn đến với Thoáng.vn</h2>
                            <p>Xin chào <strong>$full_name</strong>,</p>
                            <p>Cảm ơn bạn đã đăng ký tài khoản độc giả tại hệ thống của chúng tôi. Để hoàn tất quy trình và kích hoạt tài khoản, vui lòng nhấn vào nút bên dưới:</p>
                            <div style='text-align: center; margin: 30px 0;'><a href='$verify_link' style='background-color: #1a2744; color: #f5c518; text-decoration: none; padding: 12px 25px; font-weight: bold; border-radius: 4px; display: inline-block;'>KÍCH HOẠT TÀI KHOẢN</a></div><p style='font-size: 12px; color: #767676;'>Nếu nút trên không hoạt động, bạn có thể sao chép liên kết này dán vào trình duyệt: <br> $verify_link</p><hr style='border: none; border-top: 1px solid #d9d9d3; margin: 20px 0;'><p style='font-size: 11px; color: #767676; text-align: center;'>© 2026 Thoáng.vn — Dự án môn Lập trình Web UEH</p>
                        </div>";
                    
                    sendSystemEmail($email, $subject, $body);
                    $success = 'Đăng ký thành công! Một email xác thực đã được gửi. Vui lòng kiểm tra hộp thư (hoặc mục Spam) để kích hoạt tài khoản.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}