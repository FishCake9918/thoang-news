<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="page-body">
  <div class="container">
    <?php if ($error !== ''): ?>
      <div class="account-alert account-alert-error"><?= htmlspecialchars($error) ?></div>
    <?php else: ?>
      <section class="writer-profile-head">
        <img class="account-avatar-xl" src="<?= htmlspecialchars($writer['avatar'] ?: 'images/avatars/avatar-01.svg') ?>" alt="Avatar writer">
        <div>
          <div class="account-eyebrow">Writer</div>
          <h1 class="account-title"><?= htmlspecialchars(($writer['full_name'] ?? '') ?: $writer['username']) ?></h1>
          <p class="account-muted">
            @<?= htmlspecialchars($writer['username']) ?> · Tham gia <?= date('d/m/Y', strtotime($writer['created_at'])) ?>
          </p>
        </div>
      </section>

      <section class="account-panel">
        <div class="account-section-head">
          <div>
            <div class="account-eyebrow">Bài viết</div>
            <h2><?= isAdmin() ? 'Tất cả bài đã viết' : 'Bài viết đã duyệt' ?></h2>
          </div>
          <span class="writer-count"><?= count($articles) ?> bài</span>
        </div>

        <?php if (empty($articles)): ?>
          <div class="empty-state">
            <i class="bi bi-journal-text"></i>
            Chưa có bài viết phù hợp để hiển thị.
          </div>
        <?php else: ?>
          <div class="writer-article-list">
            <?php foreach ($articles as $article): ?>
              <?php
                $time = $article['published_at'] ?: ($article['updated_at'] ?: $article['created_at']);
                $canOpen = $article['status'] === 'Approved' || isAdmin();
              ?>
              <article class="writer-article-item">
                <div>
                  <span class="article-cat"><?= htmlspecialchars($article['category_name'] ?? 'Tin tức') ?></span>
                  <h3>
                    <?php if ($canOpen): ?>
                      <a href="article.php?id=<?= (int)$article['id'] ?>"><?= htmlspecialchars($article['title']) ?></a>
                    <?php else: ?>
                      <?= htmlspecialchars($article['title']) ?>
                    <?php endif; ?>
                  </h3>
                  <p><?= htmlspecialchars($article['summary'] ?? '') ?></p>
                  <div class="account-muted">
                    <?= date('d/m/Y H:i', strtotime($time)) ?> · <?= number_format((int)$article['view_count']) ?> lượt xem
                  </div>
                </div>
                <?php if (isAdmin()): ?>
                  <span class="status-pill status-<?= htmlspecialchars(strtolower($article['status'])) ?>">
                    <?= htmlspecialchars($article['status']) ?>
                  </span>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
