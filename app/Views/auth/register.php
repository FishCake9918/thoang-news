<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Đăng ký - Thoáng.vn</title>
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
      <a href="<?= route('index') ?>" class="masthead-logo">Thoáng<span>.</span>vn</a>
    </div>
  </div>

  <div class="flex-grow-1 d-flex align-items-center justify-content-center py-5 px-3">
    <div class="auth-card" style="max-width:500px;">
      <div class="auth-title">Đăng ký tài khoản</div>
      <div class="auth-sub">Trở thành thành viên của Thoáng.vn ngay hôm nay.</div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 px-3" style="font-size:13px;border-radius:2px">
          <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($success)): ?>
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
        <a href="<?= route('login') ?>" class="link-gold">Đăng nhập</a>
      </div>
      <div class="text-center mt-2" style="font-size:13px;color:#555">
        <a href="<?= route() ?>" style="color:#767676;text-decoration:none">
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
</body>
</html>
