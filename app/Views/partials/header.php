<?php
if (!isset($page_title)) $page_title = 'Thoáng.vn';
if (!isset($active_nav)) $active_nav = '';
$is_logged = isLoggedIn();
$is_admin  = isAdmin();
$cur_user  = getCurrentUser();
$current_page = basename($_SERVER['PHP_SELF'] ?? '');
$nav_categories = $nav_categories ?? [];

if ($nav_categories === [] && isset($pdo)) {
    try {
        $nav_categories = (new \App\Models\CategoryModel($pdo))->activeTree();
    } catch (Throwable $e) {
        $nav_categories = [];
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($page_title) ?></title>
  <link rel="icon" type="image/png" href="images/favicon.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link href="stylesheets/style.css" rel="stylesheet"/>
</head>
<body>

<?php if ($is_admin): ?>
<div class="admin-bar">
  <div class="container d-flex align-items-center gap-3">
    <i class="bi bi-shield-fill-check"></i>
    <strong>Chế độ Admin</strong>
    <span style="color:#9daabf;font-size:11px">- Bạn có toàn quyền quản trị hệ thống</span>
    <span class="ms-auto" style="color:#9daabf">
      <i class="bi bi-person-fill me-1"></i>
      <?= htmlspecialchars(($cur_user['full_name'] ?? '') ?: ($cur_user['username'] ?? 'Admin')) ?>
    </span>
  </div>
</div>
<?php endif; ?>

<div class="top-bar">
  <div class="container d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2" style="font-size: 13px;">
      <span><?= viDate() ?></span>
      <span class="opacity-50 d-none d-md-inline">|</span>
      <div id="weatherWidget" class="d-none d-md-flex align-items-center gap-1">
        <span class="opacity-75" style="font-size: 12px;">Đang tải thời tiết...</span>
      </div>
    </div>
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

<div class="masthead">
  <div class="container d-flex justify-content-between align-items-end flex-wrap gap-2">
    <div>
      <a href="index.php" class="masthead-logo">Thoáng<span>.</span>vn</a>
      <div class="masthead-tagline">Lướt qua là nắm ngay</div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <form action="search.php" method="GET" class="masthead-search d-none d-md-block mb-0">
        <input type="text" name="q" placeholder="Tìm kiếm..." required />
      </form>
      <?php if ($is_logged): ?>
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="dashboard.php" class="auth-link" style="border-color: var(--gold); color: var(--gold);">
            <i class="bi bi-speedometer2 me-1"></i>Admin Panel
          </a>
          <a href="dashboard.php?view=categories#category-manager" class="auth-link" style="border-color: var(--gold); color: var(--gold);">
            <i class="bi bi-tags me-1"></i>Danh mục
          </a>
        <?php elseif ($_SESSION['role'] === 'writer'): ?>
          <a href="dashboard_writer.php" class="auth-link" style="border-color: var(--gold); color: var(--gold);">
            <i class="bi bi-pen me-1"></i>Trang tác giả
          </a>
          <?php if ($current_page !== 'dashboard_writer.php'): ?>
            <a href="vietbai.php" class="auth-link" style="border-color: var(--gold); color: var(--gold); font-weight: 600;">
              <i class="bi bi-pencil-square me-1"></i>Viết bài
            </a>
          <?php endif; ?>
        <?php endif; ?>

        <div class="user-badge">
          <img class="nav-avatar" src="<?= htmlspecialchars($cur_user['avatar'] ?? 'images/avatars/avatar-01.svg') ?>" alt="">
          <?= htmlspecialchars($cur_user['username']) ?>
          <span class="role-pill"><?= ucfirst($_SESSION['role']) ?></span>
        </div>
        <a href="account.php" class="auth-link">
          <i class="bi bi-person-gear"></i>Tài khoản
        </a>
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

<div class="primary-nav">
  <div class="container">
    <ul class="nav">
      <li class="nav-item">
        <a class="nav-link nav-home" href="index.php?category=all" data-cat="all" title="Trang chủ">
          <i class="bi bi-house-door-fill"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $active_nav === 'hot' ? 'active' : '' ?> nav-hot"
           href="index.php?category=hot"
           data-cat="hot">
          Nóng <i class="bi bi-fire blink-icon"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $active_nav === 'all' ? 'active' : '' ?>"
           href="index.php?category=all"
           data-cat="all">
          Tất cả
        </a>
      </li>
      <?php foreach ($nav_categories as $category): ?>
        <?php
          $slug = $category['slug'] ?? '';
          $children = $category['children'] ?? [];
          $isActive = $active_nav === $slug || in_array($active_nav, array_column($children, 'slug'), true);
        ?>
        <li class="nav-item nav-dropdown">
          <a class="nav-link <?= $isActive ? 'active' : '' ?>"
             href="index.php?category=<?= urlencode($slug) ?>"
             data-cat="<?= htmlspecialchars($slug) ?>">
            <?= htmlspecialchars($category['name'] ?? '') ?>
            <?php if (!empty($children)): ?>
              <i class="bi bi-chevron-down nav-caret"></i>
            <?php endif; ?>
          </a>
          <?php if (!empty($children)): ?>
            <div class="category-dropdown">
              <?php foreach ($children as $child): ?>
                <a href="index.php?category=<?= urlencode($child['slug']) ?>"
                   data-cat="<?= htmlspecialchars($child['slug']) ?>">
                  <?= htmlspecialchars($child['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
