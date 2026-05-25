<?php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT a.*, c.name AS category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        header('Location: index.php');
        exit;
    }

    $canPreview = isLoggedIn() && (
        ($_SESSION['role'] ?? '') === 'admin' ||
        (
            ($_SESSION['role'] ?? '') === 'writer' &&
            (int)($article['author_id'] ?? 0) === (int)($_SESSION['user_id'] ?? 0)
        )
    );

    if ($article['status'] !== 'Approved' && !$canPreview) {
        header('Location: index.php');
        exit;
    }

    if ($article['status'] === 'Approved') {
        $updateView = $pdo->prepare("
            UPDATE articles
            SET view_count = view_count + 1
            WHERE id = ?
        ");
        $updateView->execute([$id]);

        $article['view_count'] = (int)$article['view_count'] + 1;
    }

} catch (PDOException $e) {
    die("Lỗi kết nối hoặc xử lý dữ liệu hệ thống: " . $e->getMessage());
}

$published_time = !empty($article['published_at']) ? $article['published_at'] : $article['created_at'];
$page_title = htmlspecialchars($article['title']) . ' — Thoáng.vn';

include 'partials/header.php';
?>

<div class="article-body">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9 col-xl-8">
        
        <div class="article-card">
          
          <span class="article-cat">
            <?= htmlspecialchars($article['category_name'] ?? 'Tin tức') ?>
          </span>
          
          <h1 class="article-title">
            <?= htmlspecialchars($article['title']) ?>
          </h1>
          
          <div class="article-meta">
            <div class="meta-item">
              <i class="bi bi-journal-bookmark-fill"></i>
              <span>Nguồn: <strong><?= htmlspecialchars($article['source_name'] ?? $article['source'] ?? 'Nội bộ') ?></strong></span>
            </div>
            <div class="meta-item border-start ps-3">
              <i class="bi bi-calendar3"></i>
              <span><?= date('d/m/Y H:i', strtotime($published_time)) ?></span>
            </div>
            <div class="meta-item border-start ps-3">
              <i class="bi bi-eye-fill"></i>
              <span><?= number_format($article['view_count']) ?> lượt xem</span>
            </div>
          </div>
          
          <?php if (!empty($article['image_url'])): ?>
            <figure class="article-image-wrap">
              <img
                class="article-image"
                src="<?= htmlspecialchars($article['image_url']) ?>"
                alt="<?= htmlspecialchars($article['title']) ?>"
                loading="lazy"
                onerror="this.closest('.article-image-wrap').classList.add('is-broken')"
              >
              <figcaption>Không thể hiển thị hình ảnh bài viết.</figcaption>
            </figure>
          <?php endif; ?>

          <div class="article-summary">
            <?= nl2br(htmlspecialchars($article['summary'])) ?>
          </div>
          
          <div class="article-content">
            <?= nl2br($article['content']) ?> 
          </div>
          
          <div class="border-top mt-5">
            <a href="javascript:history.back()" class="btn-back">
              <i class="bi bi-arrow-left"></i> Quay lại trang trước
            </a>
          </div>

        </div></div>
    </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
