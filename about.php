<?php
// ============================================================
// about.php — Trang Giới thiệu (PHP, dynamic, full-featured)
// ============================================================
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

$is_admin    = isAdmin();
$is_logged   = isLoggedIn();
$cur_user    = getCurrentUser();
$user_email  = $cur_user ? $cur_user['email'] : '';

// ── Default section data ───────────────────────────────────
$defaults = [
  'hero' => [
    'eyebrow' => 'Về chúng tôi',
    'title'   => 'Tin tức thời <em>thoáng qua,</em><br>kiến thức ở lại.',
    'lead'    => 'Thoáng.vn là ứng dụng đọc tin hiện đại, được thiết kế để bạn nắm bắt thông tin quan trọng trong 30 giây — không cuộn vô tận, không bị phân tâm.',
  ],
  'stats' => [
    ['num'=>'30','unit'=>'s','label'=>'Thời gian đọc mỗi tin'],
    ['num'=>'5','unit'=>'+','label'=>'Chủ đề tin tức'],
    ['num'=>'100','unit'=>'%','label'=>'Không quảng cáo xâm phạm'],
    ['num'=>'0','unit'=>'đ','label'=>'Hoàn toàn miễn phí'],
  ],
  'mission' => [
    'quote' => '"Mỗi người xứng đáng được tiếp cận thông tin rõ ràng, trung thực và nhanh chóng — dù bận đến đâu."',
    'body'  => 'Trong thời đại thông tin bùng nổ, chúng ta thường bị cuốn vào vòng xoáy cuộn mãi không dứt. Thoáng.vn ra đời để phá vỡ điều đó. Thay vì đẩy bạn đọc thêm và thêm, chúng tôi giúp bạn <strong>nắm ngay điều cốt lõi</strong> rồi tiếp tục ngày của mình.<br><br>Mọi bài viết trên Thoáng.vn đều được biên tập để đọc trong vòng 30 giây — đủ để hiểu, đủ để nhớ, không dư thừa.',
  ],
  'values' => [
    ['icon'=>'bi-lightning-charge','title'=>'Nhanh mà không nông','desc'=>'Cô đọng không có nghĩa là bỏ qua chiều sâu. Chúng tôi chắt lọc bản chất, không cắt xén ngữ cảnh.'],
    ['icon'=>'bi-shield-check','title'=>'Trung thực trước tiên','desc'=>'Mỗi tin đều có nguồn rõ ràng. Chúng tôi không giật tít câu view hay bóp méo sự thật để tăng traffic.'],
    ['icon'=>'bi-person-heart','title'=>'Người dùng làm trọng','desc'=>'Không thuật toán gây nghiện, không thông báo quấy rầy. Bạn kiểm soát những gì bạn đọc.'],
    ['icon'=>'bi-leaf','title'=>'Nhẹ nhàng & bền vững','desc'=>'Thoáng được thiết kế gọn nhẹ, tiết kiệm pin và dữ liệu — tốt cho bạn lẫn môi trường.'],
  ],
  'story' => [
    ['title'=>'Bắt đầu từ nỗi bực bội','desc'=>'Nhóm sáng lập nhận ra rằng mình dành hàng giờ cuộn đọc tin mà không nhớ được gì. Quá nhiều nội dung, quá ít giá trị thực sự.'],
    ['title'=>'Ý tưởng về "thoáng"','desc'=>'Lấy cảm hứng từ cơ chế swipe của các ứng dụng hiện đại, chúng tôi nghĩ: nếu tin tức cũng có thể được tiếp nhận nhẹ nhàng và tức thì như vậy thì sao?'],
    ['title'=>'Xây dựng cho người Việt','desc'=>'Thoáng.vn được tối ưu cho thói quen đọc tin của người Việt: nhanh, trên điện thoại, trong những khoảng thời gian ngắn của ngày.'],
  ],
  'features' => [
    ['icon'=>'bi-hand-index','text'=>'Swipe để lưu / bỏ qua'],
    ['icon'=>'bi-clock','text'=>'Đọc trong 30 giây'],
    ['icon'=>'bi-funnel','text'=>'Lọc theo chủ đề'],
    ['icon'=>'bi-bookmark-check','text'=>'Lưu tin quan trọng'],
    ['icon'=>'bi-layout-text-sidebar','text'=>'Giao diện sạch'],
    ['icon'=>'bi-moon-stars','text'=>'Không gây nghiện'],
    ['icon'=>'bi-broadcast','text'=>'Cập nhật liên tục'],
  ],
  'team' => [
    ['initials'=>'NK','name'=>'Nguyễn Khánh Hoàng','role'=>'Frontend Developer'],
    ['initials'=>'HN','name'=>'Hứa Đức Nghĩa','role'=>'Backend Developer'],
    ['initials'=>'LV','name'=>'Lê Hoàng Việt','role'=>'UI/UX Designer'],
    ['initials'=>'MT','name'=>'Nguyễn Kiều Minh Trí','role'=>'Content & SEO'],
  ],
  'cta' => [
    'title'    => 'Thoáng qua là nắm ngay.',
    'desc'     => 'Hãy thử một lần đọc tin theo cách khác — nhanh hơn, rõ hơn, không tốn thêm một giây nào thừa.',
    'btn1_text'=> 'Đọc tin ngay',
    'btn1_url' => 'index.html',
    'btn2_text'=> 'Gửi góp ý',
    'btn2_url' => '#',
  ],
];

// ── Load sections from DB ──────────────────────────────────
$sections = [];
foreach ($defaults as $key => $def) {
  try {
    $st = $pdo->prepare("SELECT section_data FROM about_sections WHERE section_key = ?");
    $st->execute([$key]);
    $row = $st->fetch();
    $sections[$key] = ($row && $v = json_decode($row['section_data'], true)) ? $v : $def;
  } catch (Exception $e) { $sections[$key] = $def; }
}

// ── Load feedbacks (admin) ────────────────────────────────
$feedbacks = []; $fcnt = ['pending'=>0,'replied'=>0,'done'=>0];
if ($is_admin) {
  try {
    $st = $pdo->query(
      "SELECT f.*, u.username as sender_name FROM feedback f
       LEFT JOIN users u ON f.user_id = u.id
       ORDER BY FIELD(f.status,'pending','replied','done'), f.created_at DESC"
    );
    $feedbacks = $st->fetchAll();
    foreach ($feedbacks as $fb) $fcnt[$fb['status']]++;
  } catch (Exception $e) {}
}

$status_labels = ['pending'=>'Chờ xử lý','replied'=>'Đã trả lời','done'=>'Hoàn tất'];
$status_colors = ['pending'=>'#f0ad4e','replied'=>'#5bc0de','done'=>'#5cb85c'];
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Về chúng tôi — Thoáng.vn</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--navy:#1a2744;--red:#c41230;--gold:#f5c518;--bg:#f4f4f0;--white:#ffffff;--border:#d9d9d3;--text:#1a1a1a;--muted:#767676;}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Be Vietnam Pro',sans-serif;background:var(--bg);color:var(--text);}
    /* TOP BAR */
    .top-bar{background:var(--navy);border-bottom:3px solid var(--gold);padding:6px 0;font-size:12px;color:#cdd3e0;}
    /* MASTHEAD */
    .masthead{background:var(--navy);padding:16px 0 12px;}
    .masthead-logo{font-family:'Playfair Display',serif;font-size:2.6rem;font-weight:800;color:#fff;letter-spacing:-1px;line-height:1;text-decoration:none;display:inline-block;}
    .masthead-logo span{color:var(--gold);}
    .masthead-tagline{font-size:11px;color:#9daabf;letter-spacing:.1em;text-transform:uppercase;margin-top:3px;}
    .masthead-search input{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;border-radius:3px;padding:5px 12px;font-size:13px;width:180px;}
    .masthead-search input::placeholder{color:#9daabf;}
    .masthead-search input:focus{outline:none;border-color:var(--gold);}
    .auth-link{color:#cdd3e0;font-size:13px;text-decoration:none;display:flex;align-items:center;gap:5px;padding:4px 10px;border:1px solid rgba(255,255,255,.2);border-radius:2px;transition:.15s;}
    .auth-link:hover{color:var(--gold);border-color:var(--gold);}
    .auth-link.register{background:var(--gold);color:var(--navy);border-color:var(--gold);font-weight:700;}
    .auth-link.register:hover{opacity:.88;color:var(--navy);}
    .user-badge{background:rgba(255,255,255,.12);color:#fff;font-size:12px;padding:4px 10px;border-radius:2px;display:flex;align-items:center;gap:5px;}
    .user-badge .role-pill{background:var(--gold);color:var(--navy);font-size:10px;font-weight:700;padding:1px 6px;border-radius:2px;}
    /* PRIMARY NAV */
    .primary-nav{background:var(--navy);border-top:1px solid rgba(255,255,255,.1);border-bottom:4px solid var(--gold);}
    .primary-nav .nav-link{color:#fff!important;font-size:14px;font-weight:600;padding:10px 16px!important;border-bottom:3px solid transparent;transition:.15s;}
    .primary-nav .nav-link:hover,.primary-nav .nav-link.active{border-bottom-color:var(--gold);color:var(--gold)!important;}
    /* SECTION LABEL */
    .section-label{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--red);border-top:3px solid var(--red);padding-top:6px;margin-bottom:16px;display:block;}
    /* PAGE BODY */
    .page-body{padding:36px 0 60px;}
    /* HERO */
    .about-hero{background:var(--navy);padding:56px 0 48px;margin-bottom:36px;position:relative;overflow:hidden;}
    .about-hero::after{content:"";position:absolute;bottom:0;left:0;right:0;height:4px;background:var(--gold);}
    .about-hero-eyebrow{font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--gold);margin-bottom:16px;}
    .about-hero-title{font-family:'Playfair Display',serif;font-size:clamp(2.2rem,5vw,3.8rem);font-weight:800;color:#fff;line-height:1.1;letter-spacing:-1px;margin-bottom:20px;}
    .about-hero-title em{font-style:italic;color:var(--gold);}
    .about-hero-lead{font-size:15px;color:#9daabf;line-height:1.8;max-width:580px;}
    /* STATS BAR */
    .stats-bar{background:var(--white);border:1px solid var(--border);border-top:3px solid var(--navy);margin-bottom:36px;}
    .stat-cell{padding:20px 24px;text-align:center;border-right:1px solid var(--border);}
    .stat-cell:last-child{border-right:none;}
    .stat-num{font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:800;color:var(--navy);line-height:1;}
    .stat-unit{font-size:1rem;color:var(--gold);font-weight:700;}
    .stat-label{font-size:11px;color:var(--muted);margin-top:4px;}
    /* CONTENT CARD */
    .content-card{background:var(--white);border:1px solid var(--border);padding:28px;margin-bottom:24px;}
    .content-card-body{font-size:13.5px;line-height:1.85;color:#444;}
    .content-card-body p{margin-bottom:12px;}
    /* BLOCKQUOTE */
    .about-quote{border-left:4px solid var(--gold);padding:16px 20px;background:#fffdf0;margin-bottom:24px;}
    .about-quote p{font-family:'Playfair Display',serif;font-size:1.1rem;font-style:italic;color:var(--navy);line-height:1.6;margin:0;}
    /* VALUES */
    .value-item{display:flex;gap:16px;padding:16px 0;border-top:1px solid var(--border);}
    .value-item:first-child{border-top:none;padding-top:0;}
    .value-icon{width:36px;height:36px;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;border-radius:3px;}
    .value-title{font-size:13px;font-weight:600;color:var(--text);margin-bottom:4px;}
    .value-desc{font-size:12.5px;color:var(--muted);line-height:1.6;}
    /* TIMELINE */
    .timeline-item{display:flex;gap:20px;padding-bottom:28px;position:relative;}
    .timeline-item::before{content:"";position:absolute;left:19px;top:40px;bottom:0;width:1px;background:var(--border);}
    .timeline-item:last-child::before{display:none;}
    .timeline-num{width:40px;height:40px;background:var(--white);border:2px solid var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:var(--navy);flex-shrink:0;}
    .timeline-content{flex:1;padding-top:8px;}
    .timeline-title{font-size:13px;font-weight:600;color:var(--text);margin-bottom:5px;}
    .timeline-desc{font-size:12.5px;color:var(--muted);line-height:1.65;}
    /* FEATURES */
    .feat-pill{display:inline-flex;align-items:center;gap:7px;background:var(--bg);border:1px solid var(--border);padding:7px 14px;font-size:12px;color:var(--text);margin:4px;}
    .feat-pill i{color:var(--navy);font-size:13px;}
    /* TEAM */
    .team-card{background:var(--white);border:1px solid var(--border);border-top:3px solid var(--navy);padding:20px 16px;text-align:center;}
    .team-avatar{width:52px;height:52px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--gold);margin:0 auto 12px;}
    .team-name{font-size:13px;font-weight:600;color:var(--text);margin-bottom:3px;}
    .team-role{font-size:11px;color:var(--muted);}
    /* SIDEBAR */
    .sidebar{border-left:1px solid var(--border);padding-left:24px;}
    .sidebar-block{margin-bottom:28px;}
    .sidebar-heading{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--navy);border-top:3px solid var(--navy);padding-top:6px;margin-bottom:14px;}
    .quick-fact{display:flex;gap:10px;align-items:flex-start;padding:10px 0;border-top:1px solid var(--border);font-size:13px;}
    .quick-fact:first-of-type{border-top:none;padding-top:0;}
    .quick-fact i{color:var(--navy);font-size:15px;margin-top:1px;flex-shrink:0;}
    .quick-fact span{color:#444;line-height:1.5;}
    .contact-box{background:var(--white);border:1px solid var(--border);border-top:3px solid var(--red);padding:16px;}
    .contact-box h6{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--red);margin-bottom:10px;}
    .contact-link{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--navy);text-decoration:none;padding:7px 0;border-top:1px solid var(--border);cursor:pointer;}
    .contact-link:first-of-type{border-top:none;}
    .contact-link:hover{color:var(--red);}
    /* CTA BOX */
    .cta-box{background:var(--navy);padding:32px;text-align:center;margin-top:24px;position:relative;}
    .cta-box::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:var(--gold);}
    .cta-box h3{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:#fff;margin-bottom:10px;}
    .cta-box p{font-size:13px;color:#9daabf;margin-bottom:20px;line-height:1.6;}
    .btn-cta-gold{background:var(--gold);color:var(--navy);border:none;padding:9px 24px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-block;transition:opacity .15s;}
    .btn-cta-gold:hover{opacity:.88;color:var(--navy);}
    .btn-cta-outline{background:transparent;color:#fff;border:1px solid rgba(255,255,255,.3);padding:9px 24px;font-size:13px;font-weight:600;text-decoration:none;display:inline-block;transition:.15s;cursor:pointer;}
    .btn-cta-outline:hover{border-color:#fff;color:#fff;}
    /* FOOTER */
    footer{background:var(--navy);color:#9daabf;padding:28px 0 16px;font-size:13px;}
    .footer-logo{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:#fff;}
    .footer-logo span{color:var(--gold);}
    footer a{color:#9daabf;text-decoration:none;}
    footer a:hover{color:#fff;}
    .footer-links{display:flex;gap:20px;flex-wrap:wrap;margin:14px 0;border-top:1px solid rgba(255,255,255,.1);padding-top:14px;}
    .footer-copy{font-size:11px;color:#5d6a80;margin-top:10px;}

    /* ── ADMIN EDIT OVERLAY ─────────────────────────── */
    .admin-section{position:relative;}
    .admin-edit-btn{display:none;position:absolute;top:8px;right:8px;z-index:20;background:var(--navy);color:var(--gold);border:1px solid var(--gold);padding:4px 12px;font-size:11px;font-weight:700;letter-spacing:.06em;cursor:pointer;align-items:center;gap:5px;text-transform:uppercase;}
    .admin-edit-btn:hover{background:var(--gold);color:var(--navy);}
    .admin-section:hover .admin-edit-btn{display:flex;}
    .admin-bar{background:#1a2744;border-bottom:2px solid var(--gold);padding:8px 0;font-size:12px;color:var(--gold);}

    /* ── FEEDBACK PANEL ────────────────────────────── */
    .feedback-panel{background:var(--white);border:1px solid var(--border);border-top:4px solid var(--navy);padding:24px;margin-top:32px;}
    .feedback-stat-box{background:var(--bg);border:1px solid var(--border);padding:12px 20px;text-align:center;min-width:100px;}
    .feedback-stat-num{font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:800;line-height:1;}
    .feedback-stat-label{font-size:11px;color:var(--muted);margin-top:3px;}
    .fb-item{border:1px solid var(--border);margin-bottom:8px;overflow:hidden;}
    .fb-header{display:flex;align-items:center;gap:12px;padding:10px 14px;cursor:pointer;background:#fafafa;flex-wrap:wrap;}
    .fb-header:hover{background:#f0f0ec;}
    .fb-status{font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:2px 8px;border-radius:2px;flex-shrink:0;}
    .fb-status.pending{background:#fff3cd;color:#856404;}
    .fb-status.replied{background:#cff4fc;color:#055160;}
    .fb-status.done{background:#d1e7dd;color:#0a3622;}
    .fb-email{font-size:12px;font-weight:600;color:var(--navy);}
    .fb-subject{font-size:13px;color:var(--text);flex:1;}
    .fb-time{font-size:11px;color:var(--muted);white-space:nowrap;}
    .fb-body{padding:16px;border-top:1px solid var(--border);display:none;}
    .fb-message{font-size:13px;line-height:1.7;color:#444;background:var(--bg);padding:12px;border-left:3px solid var(--border);margin-bottom:12px;}
    .fb-reply-box{background:#fffdf0;border:1px solid #f5e79e;padding:12px;border-left:3px solid var(--gold);margin-bottom:12px;font-size:13px;}
    .fb-reply-box strong{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--gold);margin-bottom:5px;}
    .fb-actions{display:flex;gap:8px;flex-wrap:wrap;}
    .btn-fb{font-size:12px;font-weight:600;padding:5px 14px;border:none;cursor:pointer;border-radius:2px;}
    .btn-fb-reply{background:var(--navy);color:#fff;}
    .btn-fb-done{background:#198754;color:#fff;}
    .btn-fb-pending{background:#6c757d;color:#fff;}
    .btn-fb-del{background:#dc3545;color:#fff;}
    textarea.fb-reply-input{width:100%;border:1px solid var(--border);padding:8px 10px;font-size:13px;font-family:'Be Vietnam Pro',sans-serif;resize:vertical;min-height:80px;border-radius:2px;margin-bottom:8px;}
    textarea.fb-reply-input:focus{outline:none;border-color:var(--navy);}

    /* ── MODAL STYLES ─────────────────────────────── */
    .modal-content{border-radius:0;border:none;}
    .modal-header{background:var(--navy);color:#fff;border-radius:0;border-bottom:3px solid var(--gold);}
    .modal-title{font-family:'Playfair Display',serif;font-weight:700;}
    .modal-header .btn-close{filter:invert(1);}
    .form-label-sm{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#444;margin-bottom:4px;}
    .form-control-sm{font-size:13px;border-radius:2px;border:1px solid var(--border);}
    .form-control-sm:focus{border-color:var(--navy);box-shadow:0 0 0 2px rgba(26,39,68,.12);}
    .btn-save-section{background:var(--navy);color:#fff;border:none;padding:8px 24px;font-size:13px;font-weight:700;cursor:pointer;}
    .btn-save-section:hover{opacity:.88;}
    .list-item-row{background:var(--bg);border:1px solid var(--border);padding:12px;margin-bottom:8px;position:relative;}
    .btn-remove-item{position:absolute;top:8px;right:8px;background:none;border:none;color:#dc3545;cursor:pointer;font-size:14px;}
    .btn-add-item{background:transparent;border:1px dashed var(--navy);color:var(--navy);padding:6px 16px;font-size:12px;font-weight:700;cursor:pointer;width:100%;margin-top:4px;}
    .btn-add-item:hover{background:var(--navy);color:#fff;}
    .toast-msg{position:fixed;bottom:24px;right:24px;background:var(--navy);color:#fff;padding:12px 20px;font-size:13px;border-left:4px solid var(--gold);z-index:9999;display:none;max-width:320px;}

    @media(max-width:768px){
      .masthead-logo{font-size:2rem;}
      .sidebar{border-left:none;padding-left:0;border-top:1px solid var(--border);padding-top:24px;margin-top:8px;}
      .masthead-search{display:none;}
      .stat-cell{border-right:none;border-bottom:1px solid var(--border);}
      .stat-cell:last-child{border-bottom:none;}
      .fb-subject{display:none;}
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
    <span style="color:#9daabf;font-size:11px">— Di chuột vào các section để chỉnh sửa nội dung</span>
    <span class="ms-auto" style="color:#9daabf">
      <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($cur_user['full_name'] ?: $cur_user['username']) ?>
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
        Đã đăng nhập với tư cách
        <?= $is_admin ? '<strong style="color:var(--gold)">Admin</strong>' : '<strong>' . htmlspecialchars($cur_user['username']) . '</strong>' ?>
      <?php else: ?>
        Chào mừng! <a href="login.php" style="color:var(--gold)">Đăng nhập</a> để lưu tin và gửi góp ý.
      <?php endif; ?>
    </span>
  </div>
</div>

<!-- MASTHEAD -->
<div class="masthead">
  <div class="container d-flex justify-content-between align-items-end">
    <div>
      <a href="index.html" class="masthead-logo">Thoáng<span>.</span>vn</a>
      <div class="masthead-tagline">Lướt qua là nắm ngay</div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <div class="masthead-search">
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
      <li class="nav-item"><a class="nav-link" href="index.html">Tin mới</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Thế giới</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Kinh tế</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Công nghệ</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Thể thao</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Đời sống</a></li>
      <li class="nav-item"><a class="nav-link active" href="about.php">Về chúng tôi</a></li>
    </ul>
  </div>
</div>

<!-- ═══════════════ HERO ═══════════════ -->
<div class="about-hero admin-section" id="sec-hero">
  <?php if ($is_admin): ?>
    <button class="admin-edit-btn" onclick="openModal('hero')">
      <i class="bi bi-pencil-fill"></i> Sửa Hero
    </button>
  <?php endif; ?>
  <div class="container">
    <div class="about-hero-eyebrow" id="hero-eyebrow"><?= htmlspecialchars($sections['hero']['eyebrow']) ?></div>
    <h1 class="about-hero-title" id="hero-title"><?= $sections['hero']['title'] ?></h1>
    <p class="about-hero-lead" id="hero-lead"><?= htmlspecialchars($sections['hero']['lead']) ?></p>
  </div>
</div>

<!-- ═══════════════ STATS BAR ═══════════════ -->
<div class="container mb-4 admin-section" id="sec-stats">
  <?php if ($is_admin): ?>
    <button class="admin-edit-btn" onclick="openModal('stats')">
      <i class="bi bi-pencil-fill"></i> Sửa thống kê
    </button>
  <?php endif; ?>
  <div class="stats-bar">
    <div class="row g-0" id="stats-container">
      <?php foreach ($sections['stats'] as $stat): ?>
        <div class="col-6 col-md-3">
          <div class="stat-cell">
            <div class="stat-num"><?= htmlspecialchars($stat['num']) ?><span class="stat-unit"><?= htmlspecialchars($stat['unit']) ?></span></div>
            <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ═══════════════ PAGE BODY ═══════════════ -->
<div class="page-body">
  <div class="container">
    <div class="row">

      <!-- ── MAIN CONTENT ── -->
      <div class="col-lg-7 col-xl-8 mb-4">

        <!-- MISSION -->
        <div class="admin-section" id="sec-mission">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" onclick="openModal('mission')">
              <i class="bi bi-pencil-fill"></i> Sửa sứ mệnh
            </button>
          <?php endif; ?>
          <span class="section-label">Sứ mệnh</span>
          <div class="about-quote">
            <p id="mission-quote"><?= htmlspecialchars($sections['mission']['quote']) ?></p>
          </div>
          <div class="content-card">
            <div class="content-card-body" id="mission-body"><?= $sections['mission']['body'] ?></div>
          </div>
        </div>

        <!-- VALUES -->
        <div class="admin-section" id="sec-values">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" onclick="openModal('values')">
              <i class="bi bi-pencil-fill"></i> Sửa giá trị
            </button>
          <?php endif; ?>
          <span class="section-label" style="margin-top:32px">Giá trị cốt lõi</span>
          <div class="content-card" id="values-container">
            <?php foreach ($sections['values'] as $v): ?>
              <div class="value-item">
                <div class="value-icon"><i class="bi <?= htmlspecialchars($v['icon']) ?>"></i></div>
                <div>
                  <div class="value-title"><?= htmlspecialchars($v['title']) ?></div>
                  <div class="value-desc"><?= htmlspecialchars($v['desc']) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- STORY -->
        <div class="admin-section" id="sec-story">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" onclick="openModal('story')">
              <i class="bi bi-pencil-fill"></i> Sửa câu chuyện
            </button>
          <?php endif; ?>
          <span class="section-label" style="margin-top:32px">Câu chuyện</span>
          <div class="content-card" id="story-container">
            <?php foreach ($sections['story'] as $i => $step): ?>
              <div class="timeline-item">
                <div class="timeline-num"><?= $i+1 ?></div>
                <div class="timeline-content">
                  <div class="timeline-title"><?= htmlspecialchars($step['title']) ?></div>
                  <div class="timeline-desc"><?= htmlspecialchars($step['desc']) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- FEATURES -->
        <div class="admin-section" id="sec-features">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" onclick="openModal('features')">
              <i class="bi bi-pencil-fill"></i> Sửa tính năng
            </button>
          <?php endif; ?>
          <span class="section-label" style="margin-top:32px">Tính năng nổi bật</span>
          <div class="content-card">
            <div class="d-flex flex-wrap" id="features-container">
              <?php foreach ($sections['features'] as $feat): ?>
                <span class="feat-pill">
                  <i class="bi <?= htmlspecialchars($feat['icon']) ?>"></i>
                  <?= htmlspecialchars($feat['text']) ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- TEAM -->
        <div class="admin-section" id="sec-team">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" onclick="openModal('team')">
              <i class="bi bi-pencil-fill"></i> Sửa đội ngũ
            </button>
          <?php endif; ?>
          <span class="section-label" style="margin-top:32px">Đội ngũ</span>
          <div class="row g-3 mb-4" id="team-container">
            <?php foreach ($sections['team'] as $m): ?>
              <div class="col-6 col-md-3">
                <div class="team-card">
                  <div class="team-avatar"><?= htmlspecialchars($m['initials']) ?></div>
                  <div class="team-name"><?= htmlspecialchars($m['name']) ?></div>
                  <div class="team-role"><?= htmlspecialchars($m['role'] ?? '') ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- CTA -->
        <div class="admin-section" id="sec-cta">
          <?php if ($is_admin): ?>
            <button class="admin-edit-btn" onclick="openModal('cta')">
              <i class="bi bi-pencil-fill"></i> Sửa CTA
            </button>
          <?php endif; ?>
          <div class="cta-box">
            <h3 id="cta-title"><?= htmlspecialchars($sections['cta']['title']) ?></h3>
            <p id="cta-desc"><?= htmlspecialchars($sections['cta']['desc']) ?></p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
              <a href="<?= htmlspecialchars($sections['cta']['btn1_url']) ?>" class="btn-cta-gold" id="cta-btn1">
                <i class="bi bi-play-fill me-1"></i><?= htmlspecialchars($sections['cta']['btn1_text']) ?>
              </a>
              <button class="btn-cta-outline" id="cta-btn2" onclick="openContactModal()">
                <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($sections['cta']['btn2_text']) ?>
              </button>
            </div>
          </div>
        </div>

        <!-- ═══ ADMIN: FEEDBACK PANEL ═══ -->
        <?php if ($is_admin): ?>
        <div class="feedback-panel mt-4">
          <span class="section-label">Góp ý từ người dùng</span>

          <!-- Summary stats -->
          <div class="d-flex gap-3 mb-4 flex-wrap">
            <div class="feedback-stat-box">
              <div class="feedback-stat-num" style="color:#f0ad4e"><?= $fcnt['pending'] ?></div>
              <div class="feedback-stat-label">Chờ xử lý</div>
            </div>
            <div class="feedback-stat-box">
              <div class="feedback-stat-num" style="color:#5bc0de"><?= $fcnt['replied'] ?></div>
              <div class="feedback-stat-label">Đã trả lời</div>
            </div>
            <div class="feedback-stat-box">
              <div class="feedback-stat-num" style="color:#5cb85c"><?= $fcnt['done'] ?></div>
              <div class="feedback-stat-label">Hoàn tất</div>
            </div>
            <div class="feedback-stat-box">
              <div class="feedback-stat-num" style="color:var(--navy)"><?= count($feedbacks) ?></div>
              <div class="feedback-stat-label">Tổng cộng</div>
            </div>
          </div>

          <?php if (empty($feedbacks)): ?>
            <div class="text-center py-4" style="color:var(--muted);font-size:13px;">
              <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
              Chưa có góp ý nào.
            </div>
          <?php else: ?>
            <div id="feedback-list">
              <?php foreach ($feedbacks as $fb): ?>
              <div class="fb-item" id="fb-item-<?= $fb['id'] ?>">
                <div class="fb-header" onclick="toggleFb(<?= $fb['id'] ?>)">
                  <span class="fb-status <?= $fb['status'] ?>"><?= $status_labels[$fb['status']] ?></span>
                  <span class="fb-email"><?= htmlspecialchars($fb['sender_email']) ?></span>
                  <span class="fb-subject"><?= htmlspecialchars($fb['subject']) ?></span>
                  <span class="fb-time"><?= date('d/m/Y H:i', strtotime($fb['created_at'])) ?></span>
                  <i class="bi bi-chevron-down ms-auto" id="fb-icon-<?= $fb['id'] ?>"></i>
                </div>
                <div class="fb-body" id="fb-body-<?= $fb['id'] ?>">
                  <p class="mb-1" style="font-size:11px;color:var(--muted)">
                    <?= $fb['sender_name'] ? 'Người dùng: <strong>' . htmlspecialchars($fb['sender_name']) . '</strong> · ' : 'Khách vãng lai · ' ?>
                    <?= htmlspecialchars($fb['subject']) ?> ·
                    <?= date('d/m/Y H:i', strtotime($fb['created_at'])) ?>
                  </p>
                  <div class="fb-message"><?= nl2br(htmlspecialchars($fb['message'])) ?></div>
                  <?php if ($fb['admin_reply']): ?>
                    <div class="fb-reply-box">
                      <strong>Phản hồi của Admin <span style="font-weight:400;text-transform:none;font-size:11px">(<?= $fb['replied_at'] ? date('d/m/Y', strtotime($fb['replied_at'])) : '' ?>)</span></strong>
                      <?= nl2br(htmlspecialchars($fb['admin_reply'])) ?>
                    </div>
                  <?php endif; ?>
                  <!-- Reply area -->
                  <div id="reply-area-<?= $fb['id'] ?>" style="<?= $fb['status'] === 'done' ? 'display:none' : '' ?>">
                    <textarea class="fb-reply-input" id="reply-text-<?= $fb['id'] ?>"
                      placeholder="Nhập nội dung trả lời..."><?= htmlspecialchars($fb['admin_reply'] ?? '') ?></textarea>
                  </div>
                  <div class="fb-actions">
                    <?php if ($fb['status'] !== 'done'): ?>
                      <button class="btn-fb btn-fb-reply" onclick="fbReply(<?= $fb['id'] ?>)">
                        <i class="bi bi-reply me-1"></i>Gửi phản hồi
                      </button>
                      <button class="btn-fb btn-fb-done" onclick="fbMarkDone(<?= $fb['id'] ?>)">
                        <i class="bi bi-check2-circle me-1"></i>Hoàn tất
                      </button>
                    <?php else: ?>
                      <button class="btn-fb btn-fb-pending" onclick="fbMarkPending(<?= $fb['id'] ?>)">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Mở lại
                      </button>
                    <?php endif; ?>
                    <button class="btn-fb btn-fb-del" onclick="fbDelete(<?= $fb['id'] ?>)">
                      <i class="bi bi-trash me-1"></i>Xoá
                    </button>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div><!-- /main -->

      <!-- ── SIDEBAR ── -->
      <div class="col-lg-5 col-xl-4">
        <div class="sidebar">
          <div class="sidebar-block">
            <div class="sidebar-heading">Thông tin nhanh</div>
            <div class="quick-fact"><i class="bi bi-calendar3"></i><span>Ra mắt năm 2026, xây dựng bởi sinh viên UEH</span></div>
            <div class="quick-fact"><i class="bi bi-geo-alt"></i><span>TP. Hồ Chí Minh, Việt Nam</span></div>
            <div class="quick-fact"><i class="bi bi-people"></i><span>Đội ngũ <?= count($sections['team']) ?> thành viên</span></div>
            <div class="quick-fact"><i class="bi bi-translate"></i><span>Ngôn ngữ: Tiếng Việt</span></div>
            <div class="quick-fact"><i class="bi bi-code-slash"></i><span>HTML · CSS · Bootstrap · PHP · MySQL</span></div>
          </div>
          <div class="sidebar-block">
            <div class="contact-box">
              <h6>Liên hệ</h6>
              <a class="contact-link" onclick="openContactModal()">
                <i class="bi bi-envelope"></i> hello@thoang.vn
              </a>
              <a href="#" class="contact-link"><i class="bi bi-github"></i> github.com/thoang-vn</a>
              <a href="#" class="contact-link"><i class="bi bi-facebook"></i> facebook.com/thoangvn</a>
            </div>
          </div>
          <div class="sidebar-block">
            <div class="sidebar-heading">Khám phá thêm</div>
            <div class="quick-fact"><i class="bi bi-house"></i><span><a href="index.html" style="color:var(--navy);text-decoration:none">Trang chủ — Đọc tin mới nhất</a></span></div>
            <div class="quick-fact"><i class="bi bi-bookmark"></i><span><a href="saved.html" style="color:var(--navy);text-decoration:none">Tin đã lưu của bạn</a></span></div>
            <div class="quick-fact"><i class="bi bi-newspaper"></i><span><a href="article.html" style="color:var(--navy);text-decoration:none">Đọc bài viết đầy đủ</a></span></div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- FOOTER -->
<footer>
  <div class="container">
    <div class="footer-logo">Thoáng<span>.</span>vn</div>
    <p style="font-size:12px;margin-top:4px">Tin tức nhanh — Đọc ngay — Hiểu liền</p>
    <div class="footer-links">
      <a href="index.html">Trang chủ</a>
      <a href="article.html">Bài viết</a>
      <a href="saved.html">Đã lưu</a>
      <a href="about.php">Giới thiệu</a>
    </div>
    <div class="footer-copy">© 2026 Thoáng.vn — Dự án môn Lập trình Web · UEH</div>
  </div>
</footer>

<!-- TOAST NOTIFICATION -->
<div class="toast-msg" id="toastMsg"></div>


<!-- ═══════════════════════════════════════════════════════
     CONTACT MODAL (User + Guest only)
════════════════════════════════════════════════════════ -->
<?php if (!$is_admin): ?>
<div class="modal fade" id="contactModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-envelope me-2"></i>Gửi góp ý</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <p style="font-size:13px;color:var(--muted);margin-bottom:20px">
          Ý kiến của bạn giúp Thoáng.vn phát triển tốt hơn. Chúng tôi sẽ phản hồi sớm nhất có thể.
        </p>
        <div id="contactAlert"></div>
        <div class="mb-3">
          <label class="form-label-sm">Email nhận phản hồi <span style="color:var(--red)">*</span></label>
          <input type="email" id="c_email" class="form-control form-control-sm"
                 placeholder="email@example.com"
                 value="<?= htmlspecialchars($user_email) ?>"/>
        </div>
        <div class="mb-3">
          <label class="form-label-sm">Tiêu đề vấn đề <span style="color:var(--red)">*</span></label>
          <input type="text" id="c_subject" class="form-control form-control-sm"
                 placeholder="Ví dụ: Lỗi hiển thị, Góp ý giao diện..."/>
        </div>
        <div class="mb-4">
          <label class="form-label-sm">Nội dung <span style="color:var(--red)">*</span></label>
          <textarea id="c_message" class="form-control form-control-sm" rows="5"
                    placeholder="Mô tả chi tiết vấn đề hoặc góp ý của bạn..."></textarea>
        </div>
        <button class="btn-save-section w-100" onclick="submitContact()">
          <i class="bi bi-send me-2"></i>Gửi góp ý
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════
     ADMIN EDIT MODALS
════════════════════════════════════════════════════════ -->
<?php if ($is_admin): ?>

<!-- ── HERO ── -->
<div class="modal fade" id="modalHero" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Chỉnh sửa Hero</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body p-4">
        <div class="mb-3"><label class="form-label-sm">Eyebrow (nhãn nhỏ trên cùng)</label><input type="text" id="h_eyebrow" class="form-control form-control-sm"/></div>
        <div class="mb-3"><label class="form-label-sm">Tiêu đề chính <small>(hỗ trợ HTML: &lt;em&gt;, &lt;br&gt;, &lt;strong&gt;)</small></label><textarea id="h_title" class="form-control form-control-sm" rows="3"></textarea></div>
        <div class="mb-3"><label class="form-label-sm">Đoạn mô tả ngắn</label><textarea id="h_lead" class="form-control form-control-sm" rows="3"></textarea></div>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button><button class="btn-save-section" onclick="saveSection('hero')">Lưu thay đổi</button></div>
    </div>
  </div>
</div>

<!-- ── STATS ── -->
<div class="modal fade" id="modalStats" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-bar-chart me-2"></i>Chỉnh sửa Thống kê</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body p-4">
        <div id="stats-items"></div>
        <button class="btn-add-item" onclick="addItem('stats')"><i class="bi bi-plus-lg me-1"></i>Thêm chỉ số</button>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button><button class="btn-save-section" onclick="saveSection('stats')">Lưu thay đổi</button></div>
    </div>
  </div>
</div>

<!-- ── MISSION ── -->
<div class="modal fade" id="modalMission" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-quote me-2"></i>Chỉnh sửa Sứ mệnh</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body p-4">
        <div class="mb-3"><label class="form-label-sm">Câu quote (blockquote)</label><textarea id="m_quote" class="form-control form-control-sm" rows="2"></textarea></div>
        <div class="mb-3"><label class="form-label-sm">Nội dung chính <small>(hỗ trợ HTML cơ bản)</small></label><textarea id="m_body" class="form-control form-control-sm" rows="5"></textarea></div>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button><button class="btn-save-section" onclick="saveSection('mission')">Lưu thay đổi</button></div>
    </div>
  </div>
</div>

<!-- ── VALUES ── -->
<div class="modal fade" id="modalValues" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-stars me-2"></i>Chỉnh sửa Giá trị cốt lõi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body p-4">
        <p style="font-size:12px;color:var(--muted)">Icon: dùng tên class Bootstrap Icons, vd: <code>bi-shield-check</code></p>
        <div id="values-items"></div>
        <button class="btn-add-item" onclick="addItem('values')"><i class="bi bi-plus-lg me-1"></i>Thêm giá trị</button>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button><button class="btn-save-section" onclick="saveSection('values')">Lưu thay đổi</button></div>
    </div>
  </div>
</div>

<!-- ── STORY ── -->
<div class="modal fade" id="modalStory" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-clock-history me-2"></i>Chỉnh sửa Câu chuyện</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body p-4">
        <div id="story-items"></div>
        <button class="btn-add-item" onclick="addItem('story')"><i class="bi bi-plus-lg me-1"></i>Thêm bước</button>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button><button class="btn-save-section" onclick="saveSection('story')">Lưu thay đổi</button></div>
    </div>
  </div>
</div>

<!-- ── FEATURES ── -->
<div class="modal fade" id="modalFeatures" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-tags me-2"></i>Chỉnh sửa Tính năng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body p-4">
        <p style="font-size:12px;color:var(--muted)">Icon: tên class Bootstrap Icons. Text: nhãn hiển thị.</p>
        <div id="features-items"></div>
        <button class="btn-add-item" onclick="addItem('features')"><i class="bi bi-plus-lg me-1"></i>Thêm tính năng</button>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button><button class="btn-save-section" onclick="saveSection('features')">Lưu thay đổi</button></div>
    </div>
  </div>
</div>

<!-- ── TEAM ── -->
<div class="modal fade" id="modalTeam" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-people me-2"></i>Chỉnh sửa Đội ngũ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body p-4">
        <div id="team-items"></div>
        <button class="btn-add-item" onclick="addItem('team')"><i class="bi bi-person-plus me-1"></i>Thêm thành viên</button>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button><button class="btn-save-section" onclick="saveSection('team')">Lưu thay đổi</button></div>
    </div>
  </div>
</div>

<!-- ── CTA ── -->
<div class="modal fade" id="modalCta" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-megaphone me-2"></i>Chỉnh sửa CTA</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body p-4">
        <div class="mb-3"><label class="form-label-sm">Tiêu đề</label><input type="text" id="cta_title" class="form-control form-control-sm"/></div>
        <div class="mb-3"><label class="form-label-sm">Mô tả</label><textarea id="cta_desc" class="form-control form-control-sm" rows="2"></textarea></div>
        <div class="row g-3">
          <div class="col-6"><label class="form-label-sm">Nút 1 — Nhãn</label><input type="text" id="cta_btn1_text" class="form-control form-control-sm"/></div>
          <div class="col-6"><label class="form-label-sm">Nút 1 — URL</label><input type="text" id="cta_btn1_url" class="form-control form-control-sm"/></div>
          <div class="col-6"><label class="form-label-sm">Nút 2 — Nhãn</label><input type="text" id="cta_btn2_text" class="form-control form-control-sm"/></div>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button><button class="btn-save-section" onclick="saveSection('cta')">Lưu thay đổi</button></div>
    </div>
  </div>
</div>

<?php endif; // end admin modals ?>

<!-- ═══════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── PHP → JS data bridge ───────────────────────────────
var IS_ADMIN = <?= $is_admin ? 'true' : 'false' ?>;
var SDATA    = <?= json_encode($sections, JSON_UNESCAPED_UNICODE) ?>;

// ── Toast helper ───────────────────────────────────────
function showToast(msg, ok) {
  var t = document.getElementById('toastMsg');
  t.textContent = msg;
  t.style.display = 'block';
  t.style.borderLeftColor = ok ? '#5cb85c' : '#dc3545';
  clearTimeout(t._tid);
  t._tid = setTimeout(function(){ t.style.display='none'; }, 3200);
}

// ── CONTACT MODAL ──────────────────────────────────────
function openContactModal() {
  var m = document.getElementById('contactModal');
  if (!m) { alert('Vui lòng đăng nhập để gửi góp ý.'); return; }
  new bootstrap.Modal(m).show();
}

function submitContact() {
  var email   = document.getElementById('c_email').value.trim();
  var subject = document.getElementById('c_subject').value.trim();
  var message = document.getElementById('c_message').value.trim();
  var alert   = document.getElementById('contactAlert');

  if (!email || !subject || !message) {
    alert.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px">Vui lòng điền đầy đủ tất cả các trường.</div>';
    return;
  }

  var fd = new FormData();
  fd.append('email', email);
  fd.append('subject', subject);
  fd.append('message', message);

  fetch('api/contact_submit.php', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(res) {
      if (res.success) {
        alert.innerHTML = '<div class="alert alert-success py-2 px-3 mb-3" style="font-size:13px"><i class="bi bi-check-circle me-1"></i>' + res.message + '</div>';
        document.getElementById('c_subject').value = '';
        document.getElementById('c_message').value = '';
      } else {
        alert.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px">' + res.message + '</div>';
      }
    })
    .catch(function(){ alert.innerHTML = '<div class="alert alert-danger py-2 px-3">Có lỗi xảy ra.</div>'; });
}

// ══════════════════════════════════════════════════════
// ADMIN — EDIT MODALS
// ══════════════════════════════════════════════════════
var MODAL_MAP = {
  hero:'modalHero', stats:'modalStats', mission:'modalMission',
  values:'modalValues', story:'modalStory', features:'modalFeatures',
  team:'modalTeam', cta:'modalCta'
};

function openModal(key) {
  var d = SDATA[key];

  if (key === 'hero') {
    document.getElementById('h_eyebrow').value = d.eyebrow || '';
    document.getElementById('h_title').value   = d.title   || '';
    document.getElementById('h_lead').value    = d.lead    || '';
  }
  else if (key === 'mission') {
    document.getElementById('m_quote').value = d.quote || '';
    document.getElementById('m_body').value  = d.body  || '';
  }
  else if (key === 'cta') {
    document.getElementById('cta_title').value    = d.title     || '';
    document.getElementById('cta_desc').value     = d.desc      || '';
    document.getElementById('cta_btn1_text').value= d.btn1_text || '';
    document.getElementById('cta_btn1_url').value = d.btn1_url  || '';
    document.getElementById('cta_btn2_text').value= d.btn2_text || '';
  }
  else if (key === 'stats') {
    buildListUI('stats-items', d, [
      {f:'num',  l:'Số',   ph:'30'},
      {f:'unit', l:'Đơn vị', ph:'s'},
      {f:'label',l:'Nhãn', ph:'Thời gian...'}
    ]);
  }
  else if (key === 'values') {
    buildListUI('values-items', d, [
      {f:'icon', l:'Icon (bi-xxx)', ph:'bi-shield-check'},
      {f:'title',l:'Tiêu đề', ph:''},
      {f:'desc', l:'Mô tả',   ph:'', wide:true}
    ]);
  }
  else if (key === 'story') {
    buildListUI('story-items', d, [
      {f:'title',l:'Tiêu đề bước', ph:''},
      {f:'desc', l:'Mô tả',        ph:'', wide:true}
    ]);
  }
  else if (key === 'features') {
    buildListUI('features-items', d, [
      {f:'icon',l:'Icon (bi-xxx)', ph:'bi-star'},
      {f:'text',l:'Tên tính năng',ph:''}
    ]);
  }
  else if (key === 'team') {
    buildListUI('team-items', d, [
      {f:'initials',l:'Ký tự (2-3)',ph:'NK'},
      {f:'name',    l:'Họ và tên', ph:''},
      {f:'role',    l:'Vai trò',   ph:'Developer'}
    ]);
  }

  new bootstrap.Modal('#' + MODAL_MAP[key]).show();
}

// Build a dynamic list UI inside a container
function buildListUI(containerId, items, fields) {
  var c = document.getElementById(containerId);
  c.innerHTML = '';
  (items || []).forEach(function(item) { appendRow(c, item, fields); });
}

function appendRow(container, item, fields) {
  var div = document.createElement('div');
  div.className = 'list-item-row';
  div.setAttribute('data-fields', JSON.stringify(fields.map(function(f){ return f.f; })));

  var row = '<div class="row g-2">';
  fields.forEach(function(f) {
    var col = f.wide ? 'col-12' : (fields.length === 2 ? 'col-6' : (fields.length >= 3 ? 'col-4' : 'col-12'));
    if (f.wide) {
      row += '<div class="' + col + '"><label class="form-label-sm">' + f.l + '</label>';
      row += '<textarea class="form-control form-control-sm item-field" data-field="' + f.f + '" rows="2" placeholder="' + f.ph + '">' + esc(item[f.f] || '') + '</textarea></div>';
    } else {
      row += '<div class="' + col + '"><label class="form-label-sm">' + f.l + '</label>';
      row += '<input type="text" class="form-control form-control-sm item-field" data-field="' + f.f + '" value="' + esc(item[f.f] || '') + '" placeholder="' + f.ph + '"/></div>';
    }
  });
  row += '<div class="col-12 d-flex justify-content-end"><button type="button" class="btn-remove-item" onclick="removeRow(this)" title="Xoá"><i class="bi bi-trash3"></i></button></div>';
  row += '</div>';
  div.innerHTML = row;
  container.appendChild(div);
}

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function addItem(key) {
  var containerId = key + '-items';
  var c = document.getElementById(containerId);
  var fieldsMap = {
    stats:    [{f:'num',l:'Số',ph:''},{f:'unit',l:'Đơn vị',ph:''},{f:'label',l:'Nhãn',ph:''}],
    values:   [{f:'icon',l:'Icon',ph:'bi-star'},{f:'title',l:'Tiêu đề',ph:''},{f:'desc',l:'Mô tả',ph:'',wide:true}],
    story:    [{f:'title',l:'Tiêu đề bước',ph:''},{f:'desc',l:'Mô tả',ph:'',wide:true}],
    features: [{f:'icon',l:'Icon',ph:'bi-star'},{f:'text',l:'Tên tính năng',ph:''}],
    team:     [{f:'initials',l:'Ký tự',ph:'NK'},{f:'name',l:'Họ và tên',ph:''},{f:'role',l:'Vai trò',ph:''}]
  };
  appendRow(c, {}, fieldsMap[key] || []);
  c.lastElementChild.scrollIntoView({behavior:'smooth', block:'nearest'});
}

function removeRow(btn) {
  btn.closest('.list-item-row').remove();
}

// Collect list items from a container
function collectItems(containerId) {
  var rows = document.querySelectorAll('#' + containerId + ' .list-item-row');
  return Array.from(rows).map(function(row) {
    var obj = {};
    row.querySelectorAll('.item-field').forEach(function(el) {
      obj[el.getAttribute('data-field')] = el.value.trim();
    });
    return obj;
  });
}

// ── SAVE SECTION ──────────────────────────────────────
function saveSection(key) {
  var data;

  if (key === 'hero') {
    data = {
      eyebrow: document.getElementById('h_eyebrow').value.trim(),
      title:   document.getElementById('h_title').value.trim(),
      lead:    document.getElementById('h_lead').value.trim()
    };
  } else if (key === 'mission') {
    data = {
      quote: document.getElementById('m_quote').value.trim(),
      body:  document.getElementById('m_body').value.trim()
    };
  } else if (key === 'cta') {
    data = {
      title:    document.getElementById('cta_title').value.trim(),
      desc:     document.getElementById('cta_desc').value.trim(),
      btn1_text:document.getElementById('cta_btn1_text').value.trim(),
      btn1_url: document.getElementById('cta_btn1_url').value.trim(),
      btn2_text:document.getElementById('cta_btn2_text').value.trim(),
      btn2_url: '#'
    };
  } else {
    data = collectItems(key + '-items');
  }

  var fd = new FormData();
  fd.append('section_key',  key);
  fd.append('section_data', JSON.stringify(data));

  fetch('api/about_save.php', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(res) {
      if (res.success) {
        SDATA[key] = res.data;
        bootstrap.Modal.getInstance(document.getElementById(MODAL_MAP[key])).hide();
        showToast('✓ Đã lưu thành công!', true);
        setTimeout(function(){ location.reload(); }, 800);
      } else {
        showToast('✗ ' + res.message, false);
      }
    })
    .catch(function(){ showToast('✗ Lỗi kết nối.', false); });
}

// ══════════════════════════════════════════════════════
// ADMIN — FEEDBACK PANEL
// ══════════════════════════════════════════════════════
function toggleFb(id) {
  var body = document.getElementById('fb-body-' + id);
  var icon = document.getElementById('fb-icon-' + id);
  var open = body.style.display !== 'none' && body.style.display !== '';
  if (open) {
    body.style.display = 'none';
    icon.className = 'bi bi-chevron-down ms-auto';
  } else {
    body.style.display = 'block';
    icon.className = 'bi bi-chevron-up ms-auto';
  }
}

function fbAction(id, action, extra) {
  var fd = new FormData();
  fd.append('action', action);
  fd.append('feedback_id', id);
  if (extra) fd.append('reply', extra);

  fetch('api/feedback_action.php', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(res) {
      if (res.success) {
        showToast('✓ ' + res.message, true);
        setTimeout(function(){ location.reload(); }, 700);
      } else {
        showToast('✗ ' + res.message, false);
      }
    })
    .catch(function(){ showToast('✗ Lỗi kết nối.', false); });
}

function fbReply(id) {
  var txt = document.getElementById('reply-text-' + id).value.trim();
  if (!txt) { showToast('Vui lòng nhập nội dung phản hồi.', false); return; }
  fbAction(id, 'reply', txt);
}
function fbMarkDone(id)    { fbAction(id, 'mark_done'); }
function fbMarkPending(id) { fbAction(id, 'mark_pending'); }
function fbDelete(id) {
  if (!confirm('Xoá góp ý này?')) return;
  fbAction(id, 'delete');
}
</script>
</body>
</html>
