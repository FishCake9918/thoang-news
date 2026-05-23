<?php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

$page_title = 'Trang chủ — Thoáng.vn';

$active_nav = $_GET['category'] ?? 'all';
$allowed_nav = ['all', 'world', 'biz', 'tech', 'sport', 'life', 'edu', 'other'];

if (!in_array($active_nav, $allowed_nav, true)) {
    $active_nav = 'all';
}

$top_articles = [];

try {
    $stmt = $pdo->query("
        SELECT id, title, view_count
        FROM articles
        WHERE status = 'Approved'
        ORDER BY view_count DESC, published_at DESC, created_at DESC
        LIMIT 5
    ");
    $top_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $top_articles = [];
}

include 'partials/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet" />
<link href="stylesheets/style.css" rel="stylesheet" />

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
              <?php if (empty($top_articles)): ?>
                <div class="text-muted" style="font-size:13px;">Chưa có bài viết đã duyệt.</div>
              <?php else: ?>
                <?php foreach ($top_articles as $idx => $top): ?>
                  <div class="most-read-item">
                    <div class="mr-num"><?= $idx + 1 ?></div>
                    <div class="mr-title">
                      <a href="article.php?id=<?= (int)$top['id'] ?>">
                        <?= htmlspecialchars($top['title']) ?>
                      </a>
                      <div class="text-muted mt-1" style="font-size:11px;">
                        <?= number_format((int)$top['view_count']) ?> lượt xem
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="scripts/script.js"></script>

<?php include 'partials/footer.php'; ?>
