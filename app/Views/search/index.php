<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="page-body">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9 col-xl-8">

        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 style="font-family: 'Playfair Display', serif; font-weight: 700; color: var(--navy); margin: 0;">
            Kết quả tìm kiếm
          </h2>
        </div>

        <div class="filter-bar mb-4">
          <form method="GET" action="search.php" class="m-0">
            <div class="d-flex gap-2 flex-wrap align-items-center">
              <input
                type="text"
                name="q"
                placeholder="Nhập từ khóa tìm kiếm..."
                value="<?= htmlspecialchars($search_kw) ?>"
                style="flex:1; min-width:180px;"
                required
              >
              <button type="submit" class="btn-filter">
                <i class="bi bi-search me-1"></i>Tìm kiếm
              </button>
            </div>
          </form>
        </div>

        <?php if ($search_kw !== ''): ?>
          <div class="mb-3">
            <span class="section-label mb-0">Tìm thấy <?= $total ?> bài viết cho "<?= htmlspecialchars($search_kw) ?>"</span>
          </div>
        <?php endif; ?>

        <div class="saved-list">
          <?php if ($search_kw === ''): ?>
            <div class="empty-state">
              <i class="bi bi-search"></i>
              <p>Nhập từ khóa để tìm kiếm các bài viết.</p>
            </div>
          <?php elseif (empty($search_results)): ?>
            <div class="empty-state">
              <i class="bi bi-journal-x"></i>
              <p>Không tìm thấy bài viết nào phù hợp với từ khóa "<?= htmlspecialchars($search_kw) ?>".</p>
              <a href="index.php" style="font-size:13px; color:var(--navy);">Quay về trang chủ -></a>
            </div>
          <?php else: ?>
            <?php foreach ($search_results as $article): ?>
              <?php
              $cat_bg = $article['color_bg'] ?: '#eeeeee';
              $cat_text = $article['color_text'] ?: '#555555';
              $cat_label = $article['category_name'] ?: ($article['category'] ?: 'Tin tức');

              $pub_time = strtotime($article['published_at'] ?: $article['created_at']);
              $diff = $pub_time ? time() - $pub_time : 0;
              if ($diff < 3600) {
                  $relative_time = max(1, floor($diff / 60)) . ' phút trước';
              } elseif ($diff < 86400) {
                  $relative_time = floor($diff / 3600) . ' giờ trước';
              } else {
                  $relative_time = floor($diff / 86400) . ' ngày trước';
              }
              ?>
              <div class="saved-card">
                <div class="d-flex justify-content-between align-items-start">
                  <span class="card-cat" style="background:<?= htmlspecialchars($cat_bg) ?>;color:<?= htmlspecialchars($cat_text) ?>">
                    <?= htmlspecialchars($cat_label) ?>
                  </span>
                </div>

                <h3 class="saved-headline">
                  <a href="article.php?id=<?= (int)$article['id'] ?>"><?= htmlspecialchars($article['title']) ?></a>
                </h3>
                <p class="saved-summary"><?= htmlspecialchars($article['summary']) ?></p>

                <div class="d-flex align-items-center gap-2">
                  <div class="source-dot"><?= htmlspecialchars(strtoupper(substr($article['source_name'] ?: 'N', 0, 2))) ?></div>
                  <div class="source-name"><?= htmlspecialchars($article['source_name'] ?: 'Nguồn tin') ?></div>
                  <div class="source-time ms-2 border-start ps-2"><?= htmlspecialchars($relative_time) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
