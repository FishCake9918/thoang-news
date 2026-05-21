<?php
// ============================================================
// register.php — Trang đăng ký tài khoản user
// ============================================================
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

if (isLoggedIn()) {
    header('Location: index.html');
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
            // Check duplicate
            $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $chk->execute([$username, $email]);
            if ($chk->fetch()) {
                $error = 'Tên đăng nhập hoặc email đã được sử dụng.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins  = $pdo->prepare(
                    "INSERT INTO users (username, email, password, full_name, role) VALUES (?,?,?,?,'user')"
                );
                $ins->execute([$username, $email, $hash, $full_name]);
                $success = 'Đăng ký thành công! <a href="login.php" style="color:var(--gold);font-weight:600">Đăng nhập ngay →</a>';
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link href="stylesheets/style.css" rel="stylesheet"/>
</head>
<body>
  <div class="top-bar">
    <div class="container"><span><?= viDate() ?></span></div>
  </div>
  <div class="masthead">
    <div class="container">
      <a href="index.html" class="masthead-logo">Thoáng<span>.</span>vn</a>
    </div>
  </div>

  <div class="flex-grow-1 d-flex align-items-center justify-content-center py-5 px-3">
    <div class="auth-card">
      <div class="auth-title">Đăng ký tài khoản</div>
      <div class="auth-sub">Tạo tài khoản miễn phí để lưu tin và gửi góp ý.</div>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;border-radius:2px">
          <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success py-2 px-3 mb-3" style="font-size:13px;border-radius:2px">
          <i class="bi bi-check-circle me-1"></i><?= $success ?>
        </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label">Họ và tên</label>
          <input type="text" name="full_name" class="form-control"
                 value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                 placeholder="Nguyễn Văn A" autofocus required/>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6">
            <label class="form-label">Tên đăng nhập</label>
            <input type="text" name="username" class="form-control"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                   placeholder="nguyenvana" required/>
          </div>
          <div class="col-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="email@example.com" required/>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Mật khẩu</label>
          <input type="password" name="password" id="pwdField" class="form-control"
                 placeholder="Tối thiểu 6 ký tự" required
                 oninput="checkStrength(this.value)"/>
          <div class="strength-bar"><div class="strength-bar-fill" id="strengthBar"></div></div>
          <div id="strengthLabel" style="font-size:11px;color:#767676;margin-top:3px;"></div>
        </div>
        <div class="mb-4">
          <label class="form-label">Xác nhận mật khẩu</label>
          <input type="password" name="confirm" class="form-control"
                 placeholder="Nhập lại mật khẩu" required/>
        </div>
        <button type="submit" class="btn-register">
          <i class="bi bi-person-plus me-1"></i>Tạo tài khoản
        </button>
      </form>

      <hr class="divider"/>
      <div class="text-center" style="font-size:13px;color:#555">
        Đã có tài khoản? <a href="login.php" class="link-gold">Đăng nhập</a>
      </div>
      <div class="text-center mt-2" style="font-size:13px;">
        <a href="index.html" style="color:#767676;text-decoration:none">
          <i class="bi bi-arrow-left me-1"></i>Quay về trang chủ
        </a>
      </div>
    </div>
  </div>

  <footer><div class="container text-center">© 2026 Thoáng.vn — Dự án môn Lập trình Web · UEH</div></footer>

  <script>
    function checkStrength(val) {
      const bar = document.getElementById('strengthBar');
      const lbl = document.getElementById('strengthLabel');
      let score = 0;
      if (val.length >= 6) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;
      const levels = [
        {w:'0%', c:'transparent', t:''},
        {w:'25%', c:'#dc3545', t:'Yếu'},
        {w:'50%', c:'#fd7e14', t:'Trung bình'},
        {w:'75%', c:'#ffc107', t:'Khá mạnh'},
        {w:'100%', c:'#198754', t:'Mạnh'}
      ];
      const l = levels[score] || levels[0];
      bar.style.width = l.w; bar.style.background = l.c; lbl.textContent = l.t;
    }
  </script>
</body>
</html>
