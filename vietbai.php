<?php
// ============================================================
// vietbai.php — Giao diện tạo bài viết mới (Admin/Writer Only)
// ============================================================
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

// Kiểm tra quyền truy cập nghiêm ngặt (Chỉ Admin hoặc Writer mới được vào)
if (!isLoggedIn() || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'writer')) {
    header('Location: index.php');
    exit;
}

$page_title = 'Viết bài mới — Thoáng.vn';
include 'partials/header.php';
?>

<style>
  .page-body { padding: 40px 0 80px; background: var(--bg); }
  .cms-card { background: var(--white); border: 1px solid var(--border); border-top: 4px solid var(--navy) !important; border-radius: 8px; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,.05); }
  .cms-title { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 700; color: var(--navy); margin-bottom: 6px; }
  .cms-sub { font-size: 13px; color: var(--muted); margin-bottom: 30px; }
  .form-label { font-size: 11px; font-weight: 700; color: #444; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 6px; display: block; }
  .form-control, .form-select { border-radius: 4px; border: 1px solid var(--border); font-size: 13.5px; padding: 10px 12px; width: 100%; }
  .form-control:focus, .form-select:focus { border-color: var(--navy); box-shadow: 0 0 0 2px rgba(26,39,68,.12); outline: none; }
  .btn-submit { background: var(--navy); color: #fff; border: none; padding: 12px 30px; font-size: 13px; font-weight: 700; letter-spacing: .04em; border-radius: 4px; cursor: pointer; transition: opacity .15s; }
  .btn-submit:hover { opacity: .9; }
  .btn-cancel { background: transparent; color: var(--muted); border: 1px solid var(--border); padding: 12px 30px; font-size: 13px; font-weight: 600; border-radius: 4px; text-decoration: none; text-align: center; display: inline-block; transition: background .15s; }
  .btn-cancel:hover { background: var(--bg); color: var(--text); }
</style>

<div class="page-body">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9 col-xl-8">
        
        <div class="cms-card">
          <div class="cms-title">Tạo bài viết mới</div>
          <div class="cms-sub">Vui lòng tóm tắt thông tin ngắn gọn, rõ ràng để độc giả có thể nắm bắt nhanh trong 30 giây.</div>

          <div id="alertContainer"></div>

          <form id="articleForm" onsubmit="handleFormSubmit(event)">
            
            <div class="mb-4">
              <label class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
              <input type="text" id="title" class="form-control" placeholder="Nhập tiêu đề cốt lõi của tin tức..." required />
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Chủ đề / Danh mục <span class="text-danger">*</span></label>
                <select id="category_id" class="form-select" required>
                  <option value="">-- Chọn danh mục phù hợp --</option>
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
              <div class="col-md-6">
                <label class="form-label">Nguồn tin gốc</label>
                <input type="text" id="source" class="form-control" placeholder="Ví dụ: Tuổi Trẻ, VNExpress, Reuters..." />
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Đoạn tóm tắt nhanh (Hiển thị ở trang chủ) <span class="text-danger">*</span></label>
              <textarea id="summary" class="form-control" rows="3" placeholder="Viết khoảng 2-3 câu ngắn gọn chứa toàn bộ bản chất sự việc..." required></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label">Nội dung chi tiết bài viết <span class="text-danger">*</span></label>
              <textarea id="content" class="form-control" rows="10" placeholder="Nhập nội dung báo cáo hoặc bài viết đầy đủ tại đây..." required></textarea>
            </div>

            <div class="d-flex gap-3 justify-content-end pt-2 border-top">
              <a href="index.php" class="btn-cancel">Hủy bỏ</a>
              <button type="submit" class="btn-submit">
                <i class="bi bi-send-fill me-2"></i>Xuất bản bài viết
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
  
  // Thu thập và đóng gói dữ liệu đầu vào
  const payload = {
    title: document.getElementById('title').value.trim(),
    category_id: parseInt(document.getElementById('category_id').value),
    source: document.getElementById('source').value.trim(),
    summary: document.getElementById('summary').value.trim(),
    content: document.getElementById('content').value.trim()
  };

  // Gửi dữ liệu ngầm lên máy chủ bằng phương thức AJAX POST
  fetch('api/add_article.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json; charset=utf-8'
    },
    body: JSON.stringify(payload)
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Lỗi phản hồi hệ thống hệ mạng.');
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      // Thông báo thành công và xóa trắng form dữ liệu cũ
      alertContainer.innerHTML = `
        <div class="alert alert-success py-2 px-3 mb-4 d-flex align-items-center" style="font-size:13.5px; border-radius:4px;">
          <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
        </div>
      `;
      document.getElementById('articleForm').reset();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
      // Hiển thị lỗi nghiệp vụ hệ thống từ backend trả về
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