<?php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

// Lấy ID bài viết từ URL (Ví dụ: chitiet.php?id=5)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Nếu ID không hợp lệ, lập tức đẩy về trang chủ
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    // 1. TỰ ĐỘNG TĂNG LƯỢT XEM (VIEW COUNT) KHI CÓ NGƯỜI ĐỌC
    $updateView = $pdo->prepare("UPDATE articles SET view_count = view_count + 1 WHERE id = ?");
    $updateView->execute([$id]);

    // 2. TRUY VẤN LẤY CHI TIẾT BÀI VIẾT KÈM TÊN DANH MỤC
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.id = ? AND a.status = 'published'
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    // Nếu không tìm thấy bài viết hoặc bài viết chưa được xuất bản
    if (!$article) {
        header('Location: index.php');
        exit;
    }
    
} catch (PDOException $e) {
    die("Lỗi kết nối hoặc xử lý dữ liệu hệ thống: " . $e->getMessage());
}

// Thiết lập tiêu đề trang động theo tên bài viết
$page_title = htmlspecialchars($article['title']) . ' — Thoáng.vn';
?>
<link rel="stylesheet" href="stylesheets/style.css">
<?php
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
              <span><?= date('d/m/Y H:i', strtotime($article['created_at'])) ?></span>
            </div>
            <div class="meta-item border-start ps-3">
              <i class="bi bi-eye-fill"></i>
              <span><?= number_format($article['view_count']) ?> lượt xem</span>
            </div>
          </div>
          
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