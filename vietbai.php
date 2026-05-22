<?php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'writer') {
    header('Location: index.php');
    exit;
}

$author_id = (int)$_SESSION['user_id'];
$article_id = (int)($_GET['id'] ?? 0);
$article = [
    'id' => 0,
    'title' => '',
    'category_id' => '',
    'summary' => '',
    'content' => '',
    'tags' => '',
    'image_url' => '',
];

if ($article_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND author_id = ? LIMIT 1");
        $stmt->execute([$article_id, $author_id]);
        $found = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$found) {
            header('Location: dashboard_writer.php');
            exit;
        }
        $article = array_merge($article, $found);
    } catch (PDOException $e) {
        header('Location: dashboard_writer.php');
        exit;
    }
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO categories (name, slug, color_bg, color_text)
        SELECT 'Khác', 'other', '#E5E7EB', '#374151'
        WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'other')
    ");
    $stmt->execute();
} catch (PDOException $e) {}

$page_title = ($article_id > 0 ? 'Chỉnh sửa bài viết' : 'Viết bài mới') . ' - Thoáng.vn';
include 'partials/header.php';
?>

<div class="page-body">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9 col-xl-8">
        <div class="cms-card">
          <div class="cms-title"><?= $article_id > 0 ? 'Chỉnh sửa bài viết' : 'Viết bài mới' ?></div>
          <div class="cms-sub">Bài viết sau khi nộp sẽ được chuyển đến Admin để phê duyệt. Tên tác giả được lấy từ tài khoản đăng nhập.</div>

          <div id="alertContainer"></div>

          <form id="articleForm" onsubmit="handleFormSubmit(event)">
            <input type="hidden" id="article_id" value="<?= (int)$article['id'] ?>">

            <div class="mb-4">
              <label class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
              <input type="text" id="title" class="form-control" placeholder="Nhập tiêu đề cốt lõi của tin tức..." value="<?= htmlspecialchars($article['title']) ?>" required />
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Chủ đề / Danh mục <span class="text-danger">*</span></label>
                <select id="category_id" class="form-select" required>
                  <option value="">-- Chọn danh mục --</option>
                  <?php
                  try {
                      $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY id ASC");
                      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                          $selected = ((int)$article['category_id'] === (int)$row['id']) ? 'selected' : '';
                          echo '<option value="' . (int)$row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
                      }
                  } catch (PDOException $e) {
                      echo '<option value="">Lỗi tải danh mục</option>';
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Tags bài viết</label>
                <input type="text" id="tags" class="form-control" placeholder="VD: Công nghệ, AI, UEH" value="<?= htmlspecialchars($article['tags'] ?? '') ?>" />
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Hình ảnh bài viết</label>
              <input type="file" id="image_file" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" />
              <div class="text-muted mt-2" style="font-size:12px;">Chọn ảnh từ máy tính. Hỗ trợ JPG, PNG, WEBP, GIF và tối đa 3MB.</div>
              <div id="imagePreviewWrap" class="mt-3" style="<?= empty($article['image_url']) ? 'display:none;' : '' ?>">
                <img id="imagePreview" src="<?= htmlspecialchars($article['image_url'] ?? '') ?>" alt="Ảnh bài viết" style="width:100%;max-height:280px;object-fit:cover;border:1px solid var(--border);border-radius:6px;">
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Đoạn tóm tắt nhanh (Sapo) <span class="text-danger">*</span></label>
              <textarea id="summary" class="form-control" rows="3" placeholder="Viết khoảng 2-3 câu ngắn gọn chứa toàn bộ bản chất sự việc..." required><?= htmlspecialchars($article['summary']) ?></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label">Nội dung chi tiết bài viết <span class="text-danger">*</span></label>
              <textarea id="content" class="form-control" rows="10" placeholder="Nhập nội dung bài viết đầy đủ tại đây..." required><?= htmlspecialchars($article['content']) ?></textarea>
            </div>

            <div class="d-flex gap-3 justify-content-end pt-3 border-top flex-wrap">
              <a href="dashboard_writer.php" class="btn-cancel">Hủy bỏ</a>
              <button type="submit" class="btn-submit">
                <i class="bi bi-send-fill me-2"></i><?= $article_id > 0 ? 'Cập nhật & gửi duyệt' : 'Nộp bài chờ duyệt' ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('image_file').addEventListener('change', function() {
  const file = this.files && this.files[0];
  if (!file) return;
  const image = document.getElementById('imagePreview');
  document.getElementById('imagePreviewWrap').style.display = 'block';
  image.src = URL.createObjectURL(file);
});

function showAlert(type, message) {
  const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
  document.getElementById('alertContainer').innerHTML = `
    <div class="alert alert-${type} py-2 px-3 mb-4 d-flex align-items-center" style="font-size:13.5px; border-radius:4px;">
      <i class="bi ${icon} me-2"></i> ${message}
    </div>
  `;
}

async function uploadSelectedImage() {
  const input = document.getElementById('image_file');
  const file = input.files && input.files[0];
  if (!file) return null;

  const formData = new FormData();
  formData.append('image', file);
  const response = await fetch('api/upload_article_image.php', {
    method: 'POST',
    body: formData
  });
  return response.json();
}

async function handleFormSubmit(e) {
  e.preventDefault();

  const uploaded = await uploadSelectedImage();
  if (uploaded && !uploaded.success) {
    showAlert('danger', uploaded.message || 'Không thể tải ảnh lên.');
    return;
  }

  const payload = {
    article_id: parseInt(document.getElementById('article_id').value || '0', 10),
    title: document.getElementById('title').value.trim(),
    category_id: parseInt(document.getElementById('category_id').value, 10),
    tags: document.getElementById('tags').value.trim(),
    image_url: uploaded && uploaded.url ? uploaded.url : '<?= htmlspecialchars($article['image_url'] ?? '', ENT_QUOTES) ?>',
    summary: document.getElementById('summary').value.trim(),
    content: document.getElementById('content').value.trim()
  };

  fetch('api/add_article.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json; charset=utf-8'},
    body: JSON.stringify(payload)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showAlert('success', data.message);
      setTimeout(() => window.location.href = 'dashboard_writer.php', 800);
    } else {
      showAlert('danger', data.message || 'Không thể lưu bài viết.');
    }
  })
  .catch(() => {
    showAlert('danger', 'Không thể kết nối đến máy chủ. Vui lòng thử lại.');
  });
}
</script>

<?php include 'partials/footer.php'; ?>
