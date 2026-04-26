<?php
// ============================================================
// partials/header.php — Header dùng chung cho toàn bộ trang
// Biến cần set trước khi include:
//   $page_title — tiêu đề tab
//   $active_nav — 'home' | 'world' | 'biz' | 'tech' | 'sport' | 'life' | 'about'
// ============================================================
if (!isset($page_title)) $page_title = 'Thoáng.vn';
if (!isset($active_nav)) $active_nav = '';
$is_logged = isLoggedIn();
$is_admin  = isAdmin();
$cur_user  = getCurrentUser();
$nav_items = [
    'home'  => ['label' => 'Tin mới',      'href' => 'index.php'],
    'world' => ['label' => 'Thế giới',     'href' => '#'],
    'biz'   => ['label' => 'Kinh tế',      'href' => '#'],
    'tech'  => ['label' => 'Công nghệ',    'href' => '#'],
    'sport' => ['label' => 'Thể thao',     'href' => '#'],
    'life'  => ['label' => 'Đời sống',     'href' => '#'],
    'about' => ['label' => 'Về chúng tôi', 'href' => 'about.php'],
];
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($page_title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    /* ── BIẾN MÀU TOÀN CỤC — dùng được ở mọi trang ── */
    :root {
      --navy:   #1a2744;
      --red:    #c41230;
      --gold:   #f5c518;
      --bg:     #f4f4f0;
      --white:  #ffffff;
      --border: #d9d9d3;
      --text:   #1a1a1a;
      --muted:  #767676;
    }

    /* ── RESET ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Be Vietnam Pro', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

    /* ── ADMIN BAR ── */
    .admin-bar { background: var(--navy); border-bottom: 2px solid var(--gold); padding: 8px 0; font-size: 12px; color: var(--gold); }

    /* ── TOP BAR ── */
    .top-bar { background: var(--navy); border-bottom: 3px solid var(--gold); padding: 6px 0; font-size: 12px; color: #cdd3e0; }
    .top-bar a { color: var(--gold); text-decoration: none; }
    .top-bar a:hover { text-decoration: underline; }

    /* ── MASTHEAD ── */
    .masthead { background: var(--navy); padding: 16px 0 12px; }
    .masthead-logo { font-family: 'Playfair Display', serif; font-size: 2.6rem; font-weight: 800; color: #fff; letter-spacing: -1px; line-height: 1; text-decoration: none; display: inline-block; transition: opacity .15s; }
    .masthead-logo:hover { opacity: .9; }
    .masthead-logo span { color: var(--gold); }
    .masthead-tagline { font-size: 11px; color: #9daabf; letter-spacing: .1em; text-transform: uppercase; margin-top: 3px; }
    .masthead-search input { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; border-radius: 3px; padding: 5px 12px; font-size: 13px; width: 180px; transition: .15s; }
    .masthead-search input::placeholder { color: #9daabf; }
    .masthead-search input:focus { outline: none; border-color: var(--gold); background: rgba(255,255,255,.15); }

    /* ── AUTH BUTTONS ── */
    .auth-link { color: #cdd3e0; font-size: 13px; text-decoration: none; display: flex; align-items: center; gap: 5px; padding: 5px 12px; border: 1px solid rgba(255,255,255,.2); border-radius: 2px; transition: .15s; white-space: nowrap; }
    .auth-link:hover { color: var(--gold); border-color: var(--gold); }
    .auth-link.register { background: var(--gold); color: var(--navy); border-color: var(--gold); font-weight: 700; }
    .auth-link.register:hover { opacity: .88; color: var(--navy); }

    /* ── USER BADGE ── */
    .user-badge { background: rgba(255,255,255,.12); color: #fff; font-size: 12px; padding: 5px 10px; border-radius: 2px; display: flex; align-items: center; gap: 5px; white-space: nowrap; }
    .user-badge .role-pill { background: var(--gold); color: var(--navy); font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 2px; }

    /* ── PRIMARY NAV ── */
    .primary-nav { background: var(--navy); border-top: 1px solid rgba(255,255,255,.1); border-bottom: 4px solid var(--gold); }
    .primary-nav .nav-link { color: #fff !important; font-size: 14px; font-weight: 600; padding: 10px 16px !important; border-bottom: 3px solid transparent; transition: .15s; }
    .primary-nav .nav-link:hover,
    .primary-nav .nav-link.active { border-bottom-color: var(--gold); color: var(--gold) !important; }

    /* ── SECONDARY NAV (thanh lọc danh mục — dùng trong index.php) ── */
    .secondary-nav { background: var(--white); border-bottom: 1px solid var(--border); padding: 0; }
    .secondary-nav a { display: inline-block; padding: 8px 14px; font-size: 12px; font-weight: 600; color: var(--muted); text-decoration: none; border-bottom: 2px solid transparent; transition: .15s; }
    .secondary-nav a:hover { color: var(--navy); }
    .secondary-nav a.active { color: var(--navy); border-bottom-color: var(--navy); }

    /* ── FOOTER ── */
    .site-footer { background: var(--navy); color: #9daabf; padding: 28px 0 16px; font-size: 13px; margin-top: auto; }
    .site-footer .footer-logo { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #fff; }
    .site-footer .footer-logo span { color: var(--gold); }
    .site-footer .footer-tagline { font-size: 12px; margin-top: 4px; color: #9daabf; }
    .site-footer .footer-links { display: flex; gap: 20px; flex-wrap: wrap; margin: 14px 0 0; border-top: 1px solid rgba(255,255,255,.1); padding-top: 14px; }
    .site-footer .footer-links a { color: #9daabf; text-decoration: none; transition: color .15s; }
    .site-footer .footer-links a:hover { color: #fff; }
    .site-footer .footer-copy { font-size: 11px; color: #5d6a80; margin-top: 12px; }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      .masthead-logo { font-size: 2rem; }
      .masthead-tagline { display: none; }
      .primary-nav .nav-link { padding: 8px 10px !important; font-size: 13px; }
    }
    @media (max-width: 576px) {
      .primary-nav .nav { flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; }
      .primary-nav .nav-link { white-space: nowrap; }
    }
  </style>
</head>
<body>

<?php if ($is_admin): ?>
<!-- ADMIN BAR -->
<div class="admin-bar">
  <div class="container d-flex align-items-center gap-3">
    <i class="bi bi-shield-fill-check"></i>
    <strong>Chế độ Admin</strong>
    <span style="color:#9daabf;font-size:11px">— Di chuột vào các section để chỉnh sửa</span>
    <span class="ms-auto" style="color:#9daabf">
      <i class="bi bi-person-fill me-1"></i>
      <?= htmlspecialchars($cur_user['full_name'] ?: $cur_user['username']) ?>
    </span>
  </div>
</div>
<?php endif; ?>

<!-- TOP BAR -->
<div class="top-bar">
  <div class="container d-flex justify-content-between align-items-center">
    <span><?= viDate() ?></span>
    <span>
      <?php if ($is_logged): ?>
        <i class="bi bi-circle-fill me-1" style="color:var(--gold);font-size:8px"></i>
        Xin chào,
        <?= $is_admin
            ? '<strong style="color:var(--gold)">Admin</strong>'
            : '<strong>' . htmlspecialchars($cur_user['username']) . '</strong>' ?>
      <?php else: ?>
        <a href="login.php">Đăng nhập</a> để lưu tin và gửi góp ý.
      <?php endif; ?>
    </span>
  </div>
</div>

<!-- MASTHEAD -->
<div class="masthead">
  <div class="container d-flex justify-content-between align-items-end flex-wrap gap-2">
    <div>
      <a href="index.php" class="masthead-logo">Thoáng<span>.</span>vn</a>
      <div class="masthead-tagline">Lướt qua là nắm ngay</div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <div class="masthead-search d-none d-md-block">
        <input type="text" placeholder="Tìm kiếm..."/>
      </div>
      <?php if ($is_logged): ?>
        <div class="user-badge">
          <i class="bi bi-person-fill"></i>
          <?= htmlspecialchars($cur_user['username']) ?>
          <span class="role-pill"><?= $is_admin ? 'Admin' : 'User' ?></span>
        </div>
        <a href="logout.php" class="auth-link">
          <i class="bi bi-box-arrow-right"></i>Đăng xuất
        </a>
      <?php else: ?>
        <a href="login.php" class="auth-link">
          <i class="bi bi-box-arrow-in-right"></i>Đăng nhập
        </a>
        <a href="register.php" class="auth-link register">
          <i class="bi bi-person-plus"></i>Đăng ký
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- PRIMARY NAV -->
<div class="primary-nav">
  <div class="container">
    <ul class="nav">
      <?php foreach ($nav_items as $key => $item): ?>
        <li class="nav-item">
          <a class="nav-link <?= $active_nav === $key ? 'active' : '' ?>"
             href="<?= $item['href'] ?>">
            <?= $item['label'] ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
