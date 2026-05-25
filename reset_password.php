<?php
// reset_password.php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = ''; $success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) { die("Yêu cầu khôi phục mật khẩu không hợp lệ."); }

try {
    // Tìm token hợp lệ và đảm bảo thời gian chưa vượt quá reset_expires
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        die("<div style='text-align:center; padding: 50px; font-family:sans-serif;'><h2>Yêu cầu khôi phục đã hết hạn!</h2><p>Liên kết bảo mật này đã hết hạn sau 15 phút. Vui lòng quay lại <a href='forgot_password.php'>Trang quên mật khẩu</a> để yêu cầu mã mới.</p></div>");
    }
} catch (PDOException $e) { die("Lỗi hệ thống."); }

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
            // Cập nhật mật khẩu và triệt tiêu luôn token khôi phục
            $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            if ($update->execute([$new_hash, $user['id']])) {
                $success = 'Đặt lại mật khẩu thành công! Bạn đang được chuyển hướng về trang đăng nhập...';
                header("refresh:2;url=login.php");
            }
        } catch (PDOException $e) { $error = 'Đổi mật khẩu thất bại. Vui lòng thử lại.'; }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Đặt lại mật khẩu — Thoáng.vn</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="stylesheets/style.css">
</head>
<body>
  <div class="masthead"><div class="container"><a href="index.php" class="masthead-logo">Thoáng<span>.</span>vn</a></div></div>
  <div class="flex-grow-1 d-flex align-items-center justify-content-center py-5 px-3">
    <div class="auth-card">
      <div class="auth-title">Mật khẩu mới</div>
      <div class="auth-sub">Vui lòng thiết lập mật khẩu bảo mật mới của bạn.</div>

      <?php if ($error): ?><div class="alert alert-danger py-2 px-3" style="font-size:13px;border-radius:2px;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success py-2 px-3" style="font-size:13px;border-radius:2px;"><?= $success ?></div><?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Mật khẩu mới</label>
          <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required/>
        </div>
        <div class="mb-4">
          <label class="form-label">Xác nhận lại mật khẩu</label>
          <input type="password" name="confirm" class="form-control" placeholder="Nhập lại giống hệt ô trên" required/>
        </div>
        <button type="submit" class="btn-login">Cập nhật mật khẩu</button>
      </form>
    </div>
  </div>
</body>
</html>