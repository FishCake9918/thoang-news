<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Quên mật khẩu - Thoáng.vn</title>
  <link rel="icon" type="image/png" href="images/favicon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="stylesheets/style.css">
</head>
<body>
  <div class="masthead"><div class="container"><a href="<?= route() ?>" class="masthead-logo">Thoáng<span>.</span>vn</a></div></div>
  <div class="flex-grow-1 d-flex align-items-center justify-content-center py-5 px-3">
    <div class="auth-card">
      <div class="auth-title">Quên mật khẩu</div>
      <div class="auth-sub">Nhập email đăng ký của bạn để khôi phục mật khẩu.</div>

      <?php if (!empty($error)): ?><div class="alert alert-danger py-2 px-3" style="font-size:13px;border-radius:2px;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if (!empty($success)): ?><div class="alert alert-success py-2 px-3" style="font-size:13px;border-radius:2px;"><?= $success ?></div><?php endif; ?>

      <form method="POST">
        <div class="mb-4">
          <label class="form-label">Email tài khoản</label>
          <input type="email" name="email" class="form-control" placeholder="email@example.com" autofocus required/>
        </div>
        <button type="submit" class="btn-login">Gửi liên kết khôi phục</button>
      </form>
      <div class="text-center mt-3" style="font-size:13px;"><a href="<?= route('login') ?>" class="link-gold">Quay lại đăng nhập</a></div>
    </div>
  </div>
</body>
</html>
