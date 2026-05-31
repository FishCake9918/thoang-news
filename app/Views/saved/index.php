<?php include __DIR__ . '/../partials/header.php'; ?>

<?php if (!$is_logged_in): ?>
  <div class="page-body">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
          <div class="empty-state" style="background:var(--white);border:1px solid var(--border);border-radius:12px;padding:36px 24px;">
            <i class="bi bi-lock"></i>
            <p>Bạn cần đăng nhập hoặc đăng ký để xem các bài viết đã lưu.</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap mt-3">
              <a href="<?= route('login') ?>" class="auth-link" style="background:var(--navy);color:#fff;text-decoration:none;">
                <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
              </a>
              <a href="<?= route('register') ?>" class="auth-link register" style="text-decoration:none;">
                <i class="bi bi-person-plus"></i> Đăng ký
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php include __DIR__ . '/../partials/footer.php'; ?>
  <?php return; ?>
<?php endif; ?>

<div class="secondary-nav">
  <div class="container p-0">
    <a href="<?= route('saved', ['cat' => 'all']) ?>" class="<?= $filter_cat === 'all' ? 'active' : '' ?>">Tất cả</a>
    <?php
    $cats = [
        'tech' => 'Công nghệ',
        'biz' => 'Kinh tế',
        'world' => 'Thế giới',
        'sport' => 'Thể thao',
        'life' => 'Đời sống',
        'edu' => 'Giáo dục',
    ];
    foreach ($cats as $key => $label):
    ?>
      <a href="<?= route('saved', ['cat' => $key]) ?>" class="<?= $filter_cat === $key ? 'active' : '' ?>">
        <?= htmlspecialchars($label) ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="page-body">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9 col-xl-8">
        <div class="filter-bar">
          <form method="GET" action="<?= route('saved') ?>">
            <input type="hidden" name="cat" value="<?= htmlspecialchars($filter_cat) ?>">
            <div class="d-flex gap-2 flex-wrap align-items-center">
              <input
                type="text"
                name="q"
                placeholder="Tìm kiếm bài đã lưu..."
                value="<?= htmlspecialchars($search_kw) ?>"
                style="flex:1; min-width:180px;"
              >
              <button type="submit" class="btn-filter">
                <i class="bi bi-search me-1"></i>Tìm kiếm
              </button>
            </div>
          </form>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="section-label mb-0">Tin đã lưu (<?= $total ?>)</span>
          <?php if ($total > 0): ?>
            <a href="#" class="text-muted" style="font-size:12px; text-decoration:none;" onclick="deleteAll(); return false;">
              <i class="bi bi-trash3 me-1"></i>Xóa tất cả
            </a>
          <?php endif; ?>
        </div>

        <div class="saved-list" id="savedList">
          <?php if (empty($saved_articles)): ?>
            <div class="empty-state">
              <i class="bi bi-bookmark-x"></i>
              <p>Chưa có bài nào được lưu<?= $search_kw ? ' phù hợp với "' . htmlspecialchars($search_kw) . '"' : '' ?>.</p>
              <a href="<?= route() ?>" style="font-size:13px; color:var(--navy);">Khám phá tin tức -></a>
            </div>
          <?php else: ?>
            <?php foreach ($saved_articles as $article): ?>
              <?php
              $cat_bg = $article['color_bg'] ?: '#eeeeee';
              $cat_text = $article['color_text'] ?: '#555555';
              $cat_label = $article['category_name'] ?: ($article['category'] ?: 'Tin tức');
              $saved_time = strtotime($article['saved_at']);
              $diff = $saved_time ? time() - $saved_time : 0;
              if ($diff < 3600) {
                  $relative_time = 'Đã lưu ' . max(1, floor($diff / 60)) . ' phút trước';
              } elseif ($diff < 86400) {
                  $relative_time = 'Đã lưu ' . floor($diff / 3600) . ' giờ trước';
              } else {
                  $relative_time = 'Đã lưu ' . floor($diff / 86400) . ' ngày trước';
              }
              ?>
              <div class="saved-card" id="card-<?= (int)$article['id'] ?>">
                <div class="d-flex justify-content-between align-items-start">
                  <span class="card-cat" style="background:<?= htmlspecialchars($cat_bg) ?>;color:<?= htmlspecialchars($cat_text) ?>">
                    <?= htmlspecialchars($cat_label) ?>
                  </span>
                  <button class="btn-remove-saved" title="Bỏ lưu" onclick="removeArticle(<?= (int)$article['id'] ?>)">
                    <i class="bi bi-bookmark-fill"></i>
                  </button>
                </div>

                <h3 class="saved-headline">
                  <a href="<?= route('article', ['id' => (int)$article['id']]) ?>"><?= htmlspecialchars($article['title']) ?></a>
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

<script>
function removeArticle(id) {
  if (!confirm('Bỏ lưu bài viết này?')) return;

  fetch('api/saved_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'remove', article_id: id})
  })
  .then(r => r.json())
  .then(data => {
    if (!data.success) {
      alert(data.message || 'Không thể bỏ lưu bài viết.');
      return;
    }

    const card = document.getElementById('card-' + id);
    if (card) card.remove();
    showToast('Đã bỏ lưu bài viết.');
  });
}

function deleteAll() {
  if (!confirm('Xóa tất cả bài đã lưu?')) return;

  fetch('api/saved_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'remove_all'})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
  });
}

function showToast(msg) {
  const toast = document.createElement('div');
  toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1a2744;color:#fff;padding:10px 20px;border-radius:8px;font-size:13px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 2500);
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
