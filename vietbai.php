<?php
// ============================================================
// vietbai.php — Giao diện tạo bài viết mới (Chỉ dành cho Writer)
// ============================================================
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

// Kiểm tra quyền: Chỉ cho phép Writer
if (!isLoggedIn() || $_SESSION['role'] !== 'writer') {
    header('Location: index.php');
    exit;
}

$page_title = 'Viết bài mới — Thoáng.vn';
include 'partials/header.php';
?>

<div class="page-body">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9 col-xl-8">
        
        <div class="cms-card">
          <div class="cms-title">Sáng tác & Biên tập bài viết</div>
          <div class="cms-sub">Vui lòng tóm tắt thông tin ngắn gọn. Bài viết sau khi nộp sẽ được chuyển đến Admin để phê duyệt.</div>

          <div id="alertContainer"></div>

          <form id="articleForm" onsubmit="handleFormSubmit(event)">
            
            <div class="mb-4">
              <label class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
              <input type="text" id="title" class="form-control" placeholder="Nhập tiêu đề cốt lõi của tin tức..." required />
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <label class="form-label">Chủ đề / Danh mục <span class="text-danger">*</span></label>
                <select id="category_id" class="form-select" required>
                  <option value="">-- Chọn danh mục --</option>
                  <?php
                  try {
                      $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY id ASC");
                      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                          echo '<option value="' . intval($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
                      }
                  } catch (PDOException $e) {
                      echo '<option value="">Lỗi tải danh mục</option>';
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Nguồn tin gốc</label>
                <input type="text" id="source" class="form-control" placeholder="Ví dụ: Tuổi Trẻ, VNExpress..." />
              </div>
              <div class="col-md-4">
                <label class="form-label">Từ khóa (Tags)</label>
                <input type="text" id="tags" class="form-control" placeholder="VD: Công nghệ, AI..." />
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Đoạn tóm tắt nhanh (Sapo) <span class="text-danger">*</span></label>
              <textarea id="summary" class="form-control" rows="3" placeholder="Viết khoảng 2-3 câu ngắn gọn chứa toàn bộ bản chất sự việc..." required></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label">Nội dung chi tiết bài viết <span class="text-danger">*</span></label>
              <textarea id="content" class="form-control" rows="10" placeholder="Nhập nội dung báo cáo hoặc bài viết đầy đủ tại đây..." required></textarea>
            </div>

            <div class="d-flex gap-3 justify-content-end pt-3 border-top">
              <a href="dashboard_writer.php" class="btn-cancel">Hủy bỏ</a>
              <button type="submit" class="btn-submit">
                <i class="bi bi-send-fill me-2"></i>Nộp bài (Chờ duyệt)
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
function handleFormSubmit(e) {
  e.preventDefault();

  const alertContainer = document.getElementById('alertContainer');
  
  const payload = {
    title: document.getElementById('title').value.trim(),
    category_id: parseInt(document.getElementById('category_id').value),
    source: document.getElementById('source').value.trim(),
    tags: document.getElementById('tags').value.trim(),
    summary: document.getElementById('summary').value.trim(),
    content: document.getElementById('content').value.trim()
  };

  fetch('api/add_article.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json; charset=utf-8'
    },
    body: JSON.stringify(payload)
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Lỗi phản hồi hệ thống mạng.');
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      alertContainer.innerHTML = `
        <div class="alert alert-success py-2 px-3 mb-4 d-flex align-items-center" style="font-size:13.5px; border-radius:4px;">
          <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
        </div>
      `;
      document.getElementById('articleForm').reset();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      alertContainer.innerHTML = `
        <div class="alert alert-danger py-2 px-3 mb-4 d-flex align-items-center" style="font-size:13.5px; border-radius:4px;">
          <i class="bi bi-exclamation-triangle-fill me-2"></i> ${data.message}
        </div>
      `;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alertContainer.innerHTML = `
      <div class="alert alert-danger py-2 px-3 mb-4 d-flex align-items-center" style="font-size:13.5px; border-radius:4px;">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Không thể kết nối đến máy chủ. Vui lòng thử lại.
      </div>
    `;
  });
}
</script>

<?php include 'partials/footer.php'; ?>