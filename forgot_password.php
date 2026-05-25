<?php
// forgot_password.php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';
require_once 'config/mailer.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

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
                // Thiết lập thời gian hết hạn mã token là 15 phút sau
                $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                $update = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $update->execute([$token, $expires, $user['id']]);
                
                // Gửi email khôi phục bằng PHPMailer
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                $subject = "Yêu cầu khôi phục mật khẩu — Thoáng.vn";
                $body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #d9d9d3; border-radius: 8px;'>
                        <h3 style='color: #c41230; text-align: center;'>Yêu cầu đặt lại mật khẩu</h3>
                        <p>Xin chào <strong>{$user['full_name']}</strong>,</p>
                        <p>Hệ thống nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Liên kết khôi phục này chỉ có hiệu lực sử dụng trong vòng <strong>15 phút</strong>. Nhấn vào nút bên dưới để đổi mật khẩu:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$reset_link' style='background-color: #c41230; color: #ffffff; text-decoration: none; padding: 12px 25px; font-weight: bold; border-radius: 4px; display: inline-block;'>ĐẶT LẠI MẬT KHẨU</a>
                        </div>
                        <p style='font-size: 11px; color: #767676;'>Nếu bạn không yêu cầu hành động này, vui lòng bỏ qua email bảo mật này một cách an toàn.</p>
                        <hr style='border: none; border-top: 1px solid #d9d9d3; margin: 20px 0;'>
                        <p style='font-size: 11px; color: #767676; text-align: center;'>Thoáng.vn — Lướt nhanh nắm gọn</p>
                    </div>
                ";
                
                sendSystemEmail($email, $subject, $body);
            }
            
            // Luôn thông báo thành công để bảo mật hệ thống tránh rò rỉ thông tin email tồn tại
            $success = 'Nếu email của bạn tồn tại trên hệ thống, một đường dẫn khôi phục mật khẩu đã được gửi đi. Vui lòng kiểm tra hộp thư.';
        } catch (PDOException $e) {
            $error = 'Hệ thống đang bận, vui lòng thử lại sau.';
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Quên mật khẩu — Thoáng.vn</title>
  <link rel="icon" type="image/png" href="images/favicon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="stylesheets/style.css">
</head>
<body>
  <div class="masthead"><div class="container"><a href="index.php" class="masthead-logo">Thoáng<span>.</span>vn</a></div></div>
  <div class="flex-grow-1 d-flex align-items-center justify-content-center py-5 px-3">
    <div class="auth-card">
      <div class="auth-title">Quên mật khẩu</div>
      <div class="auth-sub">Nhập email đăng ký của bạn để khôi phục mật khẩu.</div>

      <?php if ($error): ?><div class="alert alert-danger py-2 px-3" style="font-size:13px;border-radius:2px;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success py-2 px-3" style="font-size:13px;border-radius:2px;"><?= $success ?></div><?php endif; ?>

      <form method="POST">
        <div class="mb-4">
          <label class="form-label">Email tài khoản</label>
          <input type="email" name="email" class="form-control" placeholder="email@example.com" autofocus required/>
        </div>
        <button type="submit" class="btn-login">Gửi liên kết khôi phục</button>
      </form>
      <div class="text-center mt-3" style="font-size:13px;"><a href="login.php" class="link-gold">Quay lại đăng nhập</a></div>
    </div>
  </div>
</body>
</html>