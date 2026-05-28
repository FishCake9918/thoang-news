<?php include __DIR__ . '/../partials/header.php'; ?>

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
            <span class="swipe-hint hint-skip" id="hintSkip">TIN TIẾP</span>
            <span class="swipe-hint hint-save" id="hintSave">TIN TRƯỚC</span>

            <div class="d-flex justify-content-between align-items-start mb-0">
              <span class="card-cat" id="cardCat">...</span>
              <span class="card-read-time" id="cardReadTime">~ 30 giây</span>
            </div>

            <figure class="card-image-wrap d-none" id="cardImageWrap">
              <img id="cardImage" class="card-image" src="" alt="" draggable="false">
            </figure>

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
            <button class="btn-act btn-skip-act" id="btnSkip" onclick="skipCard()" title="Tin trước">
              <i class="bi bi-arrow-left"></i>
            </button>

            <button class="btn-act btn-next-act" onclick="nextCard()" title="Tiếp theo">
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
            <div class="sidebar-heading">Mới cập nhật</div>

            <div id="latestList">
              <?php if (empty($latest_articles)): ?>
                <div class="text-muted" style="font-size:13px;">Chưa có bài viết mới.</div>
              <?php else: ?>
                <?php foreach ($latest_articles as $latest): ?>
                  <?php
                    $pub_time = strtotime($latest['published_at'] ?: $latest['created_at']);
                    $diff = $pub_time ? time() - $pub_time : 0;
                    if ($diff < 3600) {
                        $relative_time = max(1, floor($diff / 60)) . ' phút trước';
                    } elseif ($diff < 86400) {
                        $relative_time = floor($diff / 3600) . ' giờ trước';
                    } else {
                        $relative_time = floor($diff / 86400) . ' ngày trước';
                    }
                  ?>
                  <div class="most-read-item" style="gap:8px;">
                    <i class="bi bi-clock text-muted mt-1" style="font-size:13px;"></i>
                    <div class="mr-title">
                      <a href="article.php?id=<?= (int)$latest['id'] ?>" style="font-weight: 500;">
                        <?= htmlspecialchars($latest['title']) ?>
                      </a>
                      <div class="text-muted mt-1" style="font-size:11px;">
                        <?= htmlspecialchars($relative_time) ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

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

<?php include __DIR__ . '/../partials/footer.php'; ?>
