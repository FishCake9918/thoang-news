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
  <style>
    :root{--navy:#1a2744;--gold:#f5c518;--red:#c41230;--bg:#f4f4f0;--border:#d9d9d3;}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Be Vietnam Pro',sans-serif;background:var(--bg);min-height:100vh;display:flex;flex-direction:column;}
    .top-bar{background:var(--navy);border-bottom:3px solid var(--gold);padding:6px 0;font-size:12px;color:#cdd3e0;}
    .masthead{background:var(--navy);padding:16px 0 12px;}
    .masthead-logo{font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:800;color:#fff;letter-spacing:-1px;text-decoration:none;}
    .masthead-logo span{color:var(--gold);}
    .auth-card{background:#fff;border:1px solid var(--border);border-top:4px solid var(--navy);padding:36px 40px;width:100%;max-width:480px;}
    .auth-title{font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:var(--navy);margin-bottom:6px;}
    .auth-sub{font-size:13px;color:#767676;margin-bottom:28px;}
    .form-label{font-size:12px;font-weight:600;color:#444;letter-spacing:0.04em;text-transform:uppercase;}
    .form-control{border-radius:2px;border:1px solid var(--border);font-size:13.5px;padding:9px 12px;}
    .form-control:focus{border-color:var(--navy);box-shadow:0 0 0 2px rgba(26,39,68,.12);}
    .btn-register{background:var(--navy);color:#fff;border:none;padding:10px 28px;font-size:13px;font-weight:700;letter-spacing:.04em;width:100%;cursor:pointer;transition:opacity .15s;}
    .btn-register:hover{opacity:.88;}
    .divider{border:none;border-top:1px solid var(--border);margin:20px 0;}
    .link-gold{color:var(--gold);font-weight:600;text-decoration:none;}
    .link-gold:hover{text-decoration:underline;}
    footer{background:var(--navy);color:#9daabf;padding:20px 0;font-size:12px;margin-top:auto;}
    .strength-bar{height:3px;background:var(--border);margin-top:6px;overflow:hidden;}
    .strength-bar-fill{height:100%;width:0;transition:width .3s,background .3s;}
  </style>
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
