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

    /*
        Người được xem trước bài chưa duyệt:
        - Admin
        - Writer là tác giả của bài viết đó
    */
    $canPreview = isLoggedIn() && (
        $_SESSION['role'] === 'admin' ||
        (
            $_SESSION['role'] === 'writer' &&
            (int)($article['author_id'] ?? 0) === (int)$_SESSION['user_id']
        )
    );

    /*
        Người đọc thường chỉ được xem bài Approved.
        Bài request / disapproved không được mở trực tiếp bằng link.
    */
    if ($article['status'] !== 'Approved' && !$canPreview) {
        header('Location: index.php');
        exit;
    }

    /*
        Chỉ tăng lượt xem cho bài đã được duyệt.
        Bài đang chờ duyệt hoặc bị từ chối không tính view preview.
    */
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
$tags = array_filter(array_map('trim', explode(',', $article['tags'] ?? '')));

$back_url = 'index.php';

if (isLoggedIn() && $_SESSION['role'] === 'admin') {
    $back_url = 'dashboard.php';
} elseif (isLoggedIn() && $_SESSION['role'] === 'writer') {
    $back_url = 'dashboard_writer.php';
}

$page_title = htmlspecialchars($article['title']) . ' - Thoáng.vn';

include 'partials/header.php';
?>

<div class="article-body">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9 col-xl-8">
        <div class="article-card">

          <?php if ($article['status'] !== 'Approved'): ?>
            <div class="alert alert-warning py-2 px-3 mb-4" style="font-size:13px;">
              <i class="bi bi-eye me-1"></i>
              Bản xem trước - bài viết này chưa hiển thị với người đọc.

              <?php if ($article['status'] === 'request'): ?>
                <strong>Trạng thái: Chờ duyệt.</strong>
              <?php elseif ($article['status'] === 'disapproved'): ?>
                <strong>Trạng thái: Không được duyệt.</strong>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <span class="article-cat">
            <?= htmlspecialchars($article['category_name'] ?? 'Tin tức') ?>
          </span>

          <h1 class="article-title">
            <?= htmlspecialchars($article['title']) ?>
          </h1>

          <?php if (!empty($article['image_url'])): ?>
            <figure class="article-image-wrap">
              <img
                src="<?= htmlspecialchars($article['image_url']) ?>"
                alt="<?= htmlspecialchars($article['title']) ?>"
                class="article-image"
                onerror="this.closest('figure').classList.add('is-broken')"
              >
              <figcaption>
                Không tải được ảnh. Hãy dùng link trực tiếp tới file ảnh hoặc tải ảnh lên từ máy.
              </figcaption>
            </figure>
          <?php endif; ?>

          <div class="article-meta">
            <div class="meta-item">
              <i class="bi bi-person-fill"></i>
              <span>
                Tác giả:
                <strong><?= htmlspecialchars($article['source'] ?? 'Tác giả Thoáng.vn') ?></strong>
              </span>
            </div>

            <div class="meta-item border-start ps-3">
              <i class="bi bi-calendar3"></i>
              <span><?= date('d/m/Y H:i', strtotime($published_time)) ?></span>
            </div>

            <div class="meta-item border-start ps-3">
              <i class="bi bi-eye-fill"></i>
              <span><?= number_format((int)$article['view_count']) ?> lượt xem</span>
            </div>
          </div>

          <div class="article-summary">
            <?= nl2br(htmlspecialchars($article['summary'])) ?>
          </div>

          <?php if (!empty($tags)): ?>
            <div class="mb-4">
              <?php foreach ($tags as $tag): ?>
                <span class="card-tag"><?= htmlspecialchars($tag) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="article-content">
            <?= nl2br($article['content']) ?>
          </div>

          <div class="border-top mt-5">
            <a href="<?= htmlspecialchars($back_url) ?>" class="btn-back">
              <i class="bi bi-arrow-left"></i> Quay lại trang trước
            </a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>