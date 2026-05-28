<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Đăng nhập - Thoáng.vn</title>
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
    <div class="auth-card">
      <div class="auth-title">Đăng nhập</div>
      <div class="auth-sub">Chào mừng trở lại! Nhập thông tin để tiếp tục.</div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 px-3" style="font-size:13px;border-radius:2px">
          <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="mb-3">
          <label class="form-label">Tên đăng nhập hoặc Email</label>
          <input type="text" name="login_id" class="form-control"
                 value="<?= htmlspecialchars($_POST['login_id'] ?? '') ?>"
                 placeholder="admin hoặc email@example.com" autofocus required/>
        </div>
        <div class="mb-2">
          <label class="form-label">Mật khẩu</label>
          <div class="position-relative">
            <input type="password" name="password" id="pwdField" class="form-control pe-5"
                   placeholder="••••••••" required/>
            <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-1 p-0 px-2 text-secondary border-0 bg-transparent"
                    onclick="togglePwd()">
              <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <div class="text-end mb-4" style="font-size: 12px;">
          <a href="forgot_password.php" class="text-secondary text-decoration-none">Quên mật khẩu?</a>
        </div>

        <button type="submit" class="btn-login">
          <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập
        </button>
      </form>

      <hr class="divider"/>
      <div class="text-center" style="font-size:13px;color:#555">
        Chưa có tài khoản?
        <a href="register.php" class="link-gold">Đăng ký ngay</a>
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
      © 2026 Thoáng.vn - Dự án môn Lập trình Web · UEH
    </div>
  </footer>

  <script>
    function togglePwd() {
      const field = document.getElementById('pwdField');
      const icon = document.getElementById('eyeIcon');
      if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'bi bi-eye-slash';
      } else {
        field.type = 'password';
        icon.className = 'bi bi-eye';
      }
    }
  </script>
</body>
</html>
