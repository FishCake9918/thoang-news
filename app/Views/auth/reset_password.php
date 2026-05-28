<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Đặt lại mật khẩu - Thoáng.vn</title>
  <link rel="icon" type="image/png" href="images/favicon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="stylesheets/style.css">
</head>
<body>
  <div class="masthead"><div class="container"><a href="index.php" class="masthead-logo">Thoáng<span>.</span>vn</a></div></div>
  <div class="flex-grow-1 d-flex align-items-center justify-content-center py-5 px-3">
    <div class="auth-card">
      <div class="auth-title">Mật khẩu mới</div>
      <div class="auth-sub">Vui lòng thiết lập mật khẩu bảo mật mới của bạn.</div>

      <?php if (!empty($error)): ?><div class="alert alert-danger py-2 px-3" style="font-size:13px;border-radius:2px;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if (!empty($success)): ?><div class="alert alert-success py-2 px-3" style="font-size:13px;border-radius:2px;"><?= $success ?></div><?php endif; ?>

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
