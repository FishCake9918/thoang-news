<?php
// ============================================================
// login.php — Trang đăng nhập
// ============================================================
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

if (isLoggedIn()) {
    header('Location: ' . (isset($_GET['redirect']) ? $_GET['redirect'] : 'index.html'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_id) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1"
            );
            $stmt->execute([$login_id, $login_id]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['full_name']= $user['full_name'];
                $_SESSION['role']     = $user['role'];

                $redirect = $_GET['redirect'] ?? ($user['role'] === 'admin' ? 'about.php' : 'index.html');
                header("Location: $redirect");
                exit;
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
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
  <title>Đăng nhập — Thoáng.vn</title>
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
    .auth-card{background:#fff;border:1px solid var(--border);border-top:4px solid var(--navy);padding:36px 40px;width:100%;max-width:440px;}
    .auth-title{font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:var(--navy);margin-bottom:6px;}
    .auth-sub{font-size:13px;color:#767676;margin-bottom:28px;}
    .form-label{font-size:12px;font-weight:600;color:#444;letter-spacing:0.04em;text-transform:uppercase;}
    .form-control{border-radius:2px;border:1px solid var(--border);font-size:13.5px;padding:9px 12px;}
    .form-control:focus{border-color:var(--navy);box-shadow:0 0 0 2px rgba(26,39,68,.12);}
    .btn-login{background:var(--navy);color:#fff;border:none;padding:10px 28px;font-size:13px;font-weight:700;letter-spacing:.04em;width:100%;cursor:pointer;transition:opacity .15s;}
    .btn-login:hover{opacity:.88;}
    .divider{border:none;border-top:1px solid var(--border);margin:20px 0;}
    .link-gold{color:var(--gold);font-weight:600;text-decoration:none;}
    .link-gold:hover{text-decoration:underline;}
    footer{background:var(--navy);color:#9daabf;padding:20px 0;font-size:12px;margin-top:auto;}
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
      <div class="auth-title">Đăng nhập</div>
      <div class="auth-sub">Chào mừng trở lại! Nhập thông tin để tiếp tục.</div>

      <?php if ($error): ?>
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
        <div class="mb-4">
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
        <a href="index.html" style="color:#767676;text-decoration:none">
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

  <script>
    function togglePwd() {
      const f = document.getElementById('pwdField');
      const i = document.getElementById('eyeIcon');
      if (f.type === 'password') {
        f.type = 'text'; i.className = 'bi bi-eye-slash';
      } else {
        f.type = 'password'; i.className = 'bi bi-eye';
      }
    }
  </script>
</body>
</html>
