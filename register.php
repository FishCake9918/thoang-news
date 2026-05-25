<?php
// ============================================================
// register.php — Trang đăng ký tài khoản mới
// ============================================================
session_start();
require_once 'config/db.php';
require_once 'config/session.php';
require_once 'config/mailer.php'; // Nhúng cấu hình mailer

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = '';

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
                
                // Mặc định thiết lập is_verified = 0
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
                            <div style='text-align: center; margin: 30px 0;'>
                                <a href='$verify_link' style='background-color: #1a2744; color: #f5c518; text-decoration: none; padding: 12px 25px; font-weight: bold; border-radius: 4px; display: inline-block;'>KÍCH HOẠT TÀI KHOẢN</a>
                            </div>
                            <p style='font-size: 12px; color: #767676;'>Nếu nút trên không hoạt động, bạn có thể sao chép liên kết này dán vào trình duyệt: <br> $verify_link</p>
                            <hr style='border: none; border-top: 1px solid #d9d9d3; margin: 20px 0;'>
                            <p style='font-size: 11px; color: #767676; text-align: center;'>© 2026 Thoáng.vn — Dự án môn Lập trình Web UEH</p>
                        </div>
                    ";
                    
                    sendSystemEmail($email, $subject, $body);
                    $success = 'Đăng ký thành công! Một email xác thực đã được gửi. Vui lòng kiểm tra hộp thư (hoặc mục Spam) để kích hoạt tài khoản.';
                }
            }
        } catch (PDOException $e) {
            $error = 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Đăng ký — Thoáng.vn</title>
  <link rel="icon" type="image/png" href="images/favicon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="stylesheets/style.css">
</head>
<body>
  <div class="top-bar">
    <div class="container">
      <span><?php if (function_exists('viDate')) echo viDate(); ?></span>
    </div>
  </div>

  <div class="masthead">
    <div class="container">
      <a href="index.php" class="masthead-logo">Thoáng<span>.</span>vn</a>
    </div>
  </div>

  <div class="flex-grow-1 d-flex align-items-center justify-content-center py-5 px-3">
    <div class="auth-card" style="max-width:500px;"> <div class="auth-title">Đăng ký tài khoản</div>
      <div class="auth-sub">Trở thành thành viên của Thoáng.vn ngay hôm nay.</div>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3" style="font-size:13px;border-radius:2px">
          <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success py-2 px-3" style="font-size:13px;border-radius:2px">
          <i class="bi bi-check-circle me-1"></i><?= $success ?>
        </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label">Họ và tên</label>
          <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="VD: Nguyễn Văn A" required/>
        </div>
        <div class="mb-3">
          <label class="form-label">Tên đăng nhập</label>
          <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="Viết liền không dấu" required/>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="email@example.com" required/>
        </div>
        <div class="row g-2 mb-4">
          <div class="col-sm-6">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required/>
          </div>
          <div class="col-sm-6">
            <label class="form-label">Xác nhận mật khẩu</label>
            <input type="password" name="confirm" class="form-control" placeholder="Nhập lại mật khẩu" required/>
          </div>
        </div>
        <button type="submit" class="btn-login w-100">
          <i class="bi bi-person-plus me-1"></i>Đăng ký
        </button>
      </form>

      <hr class="divider"/>
      <div class="text-center" style="font-size:13px;color:#555">
        Đã có tài khoản?
        <a href="login.php" class="link-gold">Đăng nhập</a>
      </div>
      <div class="text-center mt-2" style="font-size:13px;color:#555">
        <a href="index.php" style="color:#767676;text-decoration:none">
          <i class="bi bi-arrow-left me-1"></i>Quay về trang chủ
        </a>
      </div>
    </div>
  </div>

  <footer>
    <div class="container text-center">
      © 2026 Thoáng.vn — Dự án môn Lập trình Web · UEH
    </div>
  </footer>
</body>
</html>