<?php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';
$page_title = 'Trang chủ — Thoáng.vn';
$active_nav = 'home';
include 'partials/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet" />

<style>
  /* GIỮ NGUYÊN TOÀN BỘ CSS CỦA BẠN - KHÔNG ĐỔI 1 DÒNG NÀO */
  :root {
    --navy:  #1a2744;
    --red:   #c41230;
    --gold:  #f5c518;
    --bg:    #f4f4f0;
    --white: #ffffff;
    --border:#d9d9d3;
    --text:  #1a1a1a;
    --muted: #767676;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Be Vietnam Pro', sans-serif; background: var(--bg); color: var(--text); }

  /* ... (Các phần CSS khác giữ nguyên như cũ của bạn) ... */
  
  .top-bar { background: var(--navy); border-bottom: 3px solid var(--gold); padding: 6px 0; font-size: 12px; color: #cdd3e0; }
  .top-bar a { color: #cdd3e0; text-decoration: none; }
  .top-bar a:hover { color: #fff; }
  .masthead { background: var(--navy); padding: 16px 0 12px; }
  .masthead-logo { font-family: 'Playfair Display', serif; font-size: 2.6rem; font-weight: 800; color: #fff; letter-spacing: -1px; line-height: 1; }
  .masthead-logo span { color: var(--gold); }
  .masthead-tagline { font-size: 11px; color: #9daabf; letter-spacing: .1em; text-transform: uppercase; margin-top: 3px; }
  .masthead-search input { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2); color: #fff; border-radius: 3px; padding: 5px 12px; font-size: 13px; width: 180px; }
  .masthead-search input::placeholder { color: #9daabf; }
  .masthead-search input:focus { outline: none; border-color: var(--gold); }
  .primary-nav { background: var(--navy); border-top: 1px solid rgba(255,255,255,.1); border-bottom: 4px solid var(--gold); }
  .primary-nav .nav-link { color: #fff !important; font-size: 14px; font-weight: 600; padding: 10px 16px !important; border-bottom: 3px solid transparent; transition: all .15s; }
  .primary-nav .nav-link:hover, .primary-nav .nav-link.active { border-bottom-color: var(--gold); color: var(--gold) !important; }
  .secondary-nav { background: var(--white); border-bottom: 1px solid var(--border); overflow-x: auto; white-space: nowrap; }
  .secondary-nav::-webkit-scrollbar { display: none; }
  .secondary-nav a { display: inline-block; font-size: 12px; color: var(--text); text-decoration: none; padding: 7px 13px; border-right: 1px solid var(--border); transition: background .12s; }
  .secondary-nav a:hover { background: var(--bg); }
  .secondary-nav a.active { color: var(--red); font-weight: 600; }
  .breaking-bar { background: var(--red); color: #fff; font-size: 12.5px; font-weight: 600; padding: 7px 0; }
  .breaking-bar .label { background: #fff; color: var(--red); font-size: 9px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: 2px 7px; border-radius: 2px; margin-right: 10px; }
  .page-body { padding: 28px 0 60px; }
  .section-label { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--red); border-top: 3px solid var(--red); padding-top: 6px; margin-bottom: 16px; display: block; }
  .stack-wrap { width: 100%; max-width: 100%; position: relative; margin: 0 auto; }
  .progress-label { font-size: 11px; color: var(--muted); }
  .thoang-progress { height: 3px; background: var(--border); border-radius: 2px; flex: 1; overflow: hidden; }
  .thoang-progress-bar { height: 100%; background: var(--navy); border-radius: 2px; transition: width .35s ease; }
  .news-card { background: var(--white); border: 1px solid var(--border); border-radius: 16px; padding: 22px; cursor: grab; user-select: none; position: relative; box-shadow: 0 2px 12px rgba(0,0,0,.07); width: 100%; min-height: 350px; }
  .news-card:active { cursor: grabbing; }
  .swipe-hint { position: absolute; top: 16px; font-size: 10px; font-weight: 700; letter-spacing: .06em; padding: 3px 9px; border-radius: 3px; opacity: 0; transition: opacity .12s; pointer-events: none; z-index: 10; }
  .hint-skip { left: 16px; background: #fdecea; color: var(--red); border: 1px solid var(--red); }
  .hint-save { right: 16px; background: #e6f4ee; color: #0f6e56; border: 1px solid #0f6e56; }
  .card-cat { display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; padding: 2px 10px; border-radius: 2px; margin-bottom: 14px; }
  .card-read-time { font-size: 11px; color: var(--muted); }
  .card-headline { font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700; line-height: 1.3; color: var(--text); margin-bottom: 12px; }
  .card-summary { font-size: 13.5px; color: #555; line-height: 1.7; margin-bottom: 16px; }
  .card-hr { border-color: var(--border); margin-bottom: 14px; }
  .source-dot { width: 26px; height: 26px; border-radius: 5px; background: var(--bg); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; color: var(--muted); flex-shrink: 0; }
  .source-name { font-size: 12px; font-weight: 600; color: var(--text); }
  .source-time { font-size: 11px; color: var(--muted); }
  .card-tag { font-size: 10px; color: var(--muted); background: var(--bg); border: 1px solid var(--border); padding: 2px 9px; border-radius: 2px; }
  .readmore { font-size: 12px; font-weight: 600; color: var(--navy); text-decoration: none; border-bottom: 1px solid var(--navy); }
  .readmore:hover { color: var(--red); border-color: var(--red); }
  .action-area { display: flex; align-items: center; justify-content: center; gap: 12px; margin-top: 18px; }
  .btn-act { border: 1px solid var(--border); background: var(--white); color: var(--muted); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .15s; font-size: 16px; }
  .btn-act:hover { border-color: var(--text); color: var(--text); background: var(--bg); }
  .btn-skip-act, .btn-save-act { width: 44px; height: 44px; border-radius: 4px; }
  .btn-next-act { width: 56px; height: 56px; border-radius: 4px; background: var(--navy) !important; border-color: var(--navy) !important; color: #fff !important; }
  .btn-next-act:hover { background: #0f1a35 !important; }
  .btn-save-act.saved { background: #e6f4ee !important; border-color: #1d9e75 !important; color: #1d9e75 !important; }
  .sidebar { border-left: 1px solid var(--border); padding-left: 24px; }
  .sidebar-block { margin-bottom: 28px; }
  .sidebar-heading { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--navy); border-top: 3px solid var(--navy); padding-top: 6px; margin-bottom: 14px; }
  .most-read-item { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-top: 1px solid var(--border); }
  .mr-num { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: var(--border); line-height: 1; min-width: 26px; }
  .mr-title { font-size: 13px; font-weight: 600; line-height: 1.4; }
  .mr-title a { color: var(--text); text-decoration: none; }
  .latest-item { padding: 9px 0; border-top: 1px solid var(--border); }
  .latest-title { font-size: 13px; font-weight: 600; margin-bottom: 2px; }
  .latest-title a { color: var(--text); text-decoration: none; }
  .latest-meta { font-size: 11px; color: var(--muted); }
  footer { background: var(--navy); color: #9daabf; padding: 28px 0 16px; font-size: 13px; }
  .footer-logo { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #fff; }
  .footer-logo span { color: var(--gold); }
  footer a { color: #9daabf; text-decoration: none; }
  .footer-links { display: flex; gap: 20px; flex-wrap: wrap; margin: 14px 0; border-top: 1px solid rgba(255,255,255,.1); padding-top: 14px; }
  .footer-copy { font-size: 11px; color: #5d6a80; margin-top: 10px; }

  /* Card backs */
  .card-back1 { position: absolute; top: 8px; left: 0; right: 0; height: 100%; background: #fff; border: 1px solid var(--border); border-radius: 16px; z-index: -1; transform: scale(0.97); }
  .card-back2 { position: absolute; top: 16px; left: 0; right: 0; height: 100%; background: #fff; border: 1px solid var(--border); border-radius: 16px; z-index: -2; transform: scale(0.94); }
</style>

<div class="secondary-nav">
  <div class="container p-0">
    <a href="#" class="active" data-cat="all">Tất cả</a>
    <a href="#" data-cat="tech">Công nghệ</a>
    <a href="#" data-cat="biz">Kinh tế</a>
    <a href="#" data-cat="world">Thế giới</a>
    <a href="#" data-cat="sport">Thể thao</a>
    <a href="#" data-cat="life">Đời sống</a>
    <a href="#" data-cat="edu">Giáo dục</a>
  </div>
</div>

<div class="page-body">
  <div class="container">
    <div class="row">

      <div class="col-lg-7 col-xl-8 mb-4">
        <span class="section-label">Đọc nhanh hôm nay</span>

        <div class="stack-wrap" id="stackWrap">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="progress-label" id="progressLabel">0 / 0</span>
            <div class="thoang-progress">
              <div class="thoang-progress-bar" id="progressBar" style="width:0%"></div>
            </div>
          </div>

          <div id="back2" class="card-back2"></div>
          <div id="back1" class="card-back1"></div>

          <div class="news-card" id="frontCard">
            <span class="swipe-hint hint-skip" id="hintSkip">BỎ QUA</span>
            <span class="swipe-hint hint-save" id="hintSave">LƯU LẠI</span>

            <div class="d-flex justify-content-between align-items-start mb-0">
              <span class="card-cat" id="cardCat">...</span>
              <span class="card-read-time" id="cardReadTime">~ 30 giây</span>
            </div>

            <h2 class="card-headline" id="cardTitle">Đang tải tin tức...</h2>
            <p class="card-summary" id="cardSummary"></p>

            <hr class="card-hr" />

            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="d-flex align-items-center gap-2">
                <div class="source-dot" id="cardSourceInit">--</div>
                <div>
                  <div class="source-name" id="cardSource">Đang tải...</div>
                  <div class="source-time" id="cardTime"></div>
                </div>
              </div>
              <span class="card-tag" id="cardTag"></span>
            </div>

            <a href="#" class="readmore" id="cardLink">Đọc bài đầy đủ →</a>
          </div>

          <div class="news-card text-center py-5 d-none" id="doneCard">
            <i class="bi bi-check-circle" style="font-size:2.5rem;color:#1d9e75;"></i>
            <h5 class="mt-3 mb-2" style="font-family:'Playfair Display',serif;">Đã đọc hết!</h5>
            <p class="text-muted mb-3" style="font-size:13px;">Bạn đã xem qua tất cả tin hôm nay.</p>
            <button class="btn btn-sm" style="background:var(--navy);color:#fff;border-radius:3px;padding:6px 20px;font-size:13px;" onclick="restart()">Đọc lại từ đầu</button>
          </div>

          <div class="action-area" id="actionArea">
            <button class="btn-act btn-skip-act" id="btnSkip" onclick="skipCard()" title="Bỏ qua">
              <i class="bi bi-x-lg"></i>
            </button>
            <button class="btn-act btn-next-act" onclick="skipCard()" title="Tiếp theo">
              <i class="bi bi-arrow-right"></i>
            </button>
            <button class="btn-act btn-save-act" id="btnSave" onclick="toggleSave()" title="Lưu lại">
              <i class="bi bi-bookmark" id="saveIcon"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="col-lg-5 col-xl-4">
        <div class="sidebar">
          <div class="sidebar-block">
            <div class="sidebar-heading">Đọc nhiều nhất</div>
            <div id="trendingList">
                <div class="most-read-item"><div class="mr-num">1</div><div class="mr-title"><a href="article.php">G20 cam kết 500 tỷ USD cho chuyển đổi năng lượng sạch</a></div></div>
                <div class="most-read-item"><div class="mr-num">2</div><div class="mr-title"><a href="article.php">VN-Index tăng mạnh nhờ nhóm ngân hàng</a></div></div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="scripts/script.js"></script>
<?php include 'partials/footer.php'; ?>