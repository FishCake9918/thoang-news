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
include 'partials/header.php';
?>

<style>
  /* Giao diện đồng bộ với tông màu Navy, Gold và Red của dự án */
  .article-body { padding: 40px 0 80px; background: var(--bg); }
  .article-card { background: var(--white); border: 1px solid var(--border); border-radius: 16px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,.04); }
  
  /* Badge danh mục động */
  .article-cat { display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; padding: 3px 12px; border-radius: 4px; margin-bottom: 20px; background: #EEEDFE; color: #534AB7; }
  
  .article-title { font-family: 'Playfair Display', serif; font-size: 2.4rem; font-weight: 800; line-height: 1.25; color: var(--text); margin-bottom: 20px; }
  
  /* Meta thông tin bài viết */
  .article-meta { font-size: 13px; color: var(--muted); padding-bottom: 20px; border-bottom: 1px solid var(--border); margin-bottom: 30px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
  .meta-item { display: flex; align-items: center; gap: 6px; }
  .meta-item i { color: var(--navy); font-size: 14px; }
  
  /* Khối tóm tắt bài viết (Summary) */
  .article-summary { font-size: 16px; font-weight: 500; color: #333; line-height: 1.6; margin-bottom: 30px; padding: 18px 22px; background: #fafafa; border-left: 4px solid var(--navy); border-radius: 0 8px 8px 0; }
  
  /* Nội dung chi tiết */
  .article-content { font-size: 15.5px; color: #222; line-height: 1.9; text-align: justify; }
  .article-content p { margin-bottom: 20px; }
  
  /* Nút quay lại điều hướng nhanh */
  .btn-back { background: none; border: 1px solid var(--border); color: var(--text); padding: 8px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.15s; margin-top: 40px; }
  .btn-back:hover { background: var(--bg); border-color: var(--text); color: var(--navy); }
</style>

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