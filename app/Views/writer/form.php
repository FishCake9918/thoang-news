<?php include __DIR__ . '/../partials/header.php'; ?>

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
                  <?php if (empty($category_tree ?? [])): ?>
                    <option value="">Không thể tải danh mục</option>
                  <?php else: ?>
                    <?php foreach ($category_tree as $parent): ?>
                      <?php $children = $parent['children'] ?? []; ?>
                      <?php if (empty($children)): ?>
                        <?php $selected = ((int)$article['category_id'] === (int)$parent['id']) ? 'selected' : ''; ?>
                        <option value="<?= (int)$parent['id'] ?>" <?= $selected ?>>
                          <?= htmlspecialchars($parent['name']) ?>
                        </option>
                      <?php else: ?>
                        <optgroup label="<?= htmlspecialchars($parent['name']) ?>">
                          <?php foreach ($children as $child): ?>
                            <?php $selected = ((int)$article['category_id'] === (int)$child['id']) ? 'selected' : ''; ?>
                            <option value="<?= (int)$child['id'] ?>" <?= $selected ?>>
                              <?= htmlspecialchars($child['name']) ?>
                            </option>
                          <?php endforeach; ?>
                        </optgroup>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <div class="text-muted mt-2" style="font-size:12px;">
                  Danh mục có nhóm con sẽ hiển thị theo cụm để chọn đúng chủ đề bài viết.
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Tags bài viết</label>
                <input type="text" id="tags" class="form-control" placeholder="VD: Công nghệ, AI, UEH" value="<?= htmlspecialchars($article['tags'] ?? '') ?>" />
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Ảnh thumbnail bài viết</label>
              <input type="file" id="image_file" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" />
              <div class="text-muted mt-2" style="font-size:12px;">Ảnh này dùng làm thumbnail ở trang chủ và ảnh đại diện đầu bài. Hỗ trợ JPG, PNG, WEBP, GIF và tối đa 3MB.</div>
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
              <div class="content-image-tool mt-3">
                <div class="row g-2 align-items-end">
                  <div class="col-md-5">
                    <label class="form-label mb-1">Ảnh trong nội dung</label>
                    <input type="file" id="content_image_file" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" />
                  </div>
                  <div class="col-md-5">
                    <label class="form-label mb-1">Chú thích ảnh</label>
                    <input type="text" id="content_image_caption" class="form-control" placeholder="Nhập chú thích hiển thị dưới ảnh..." />
                  </div>
                  <div class="col-md-2 d-grid">
                    <button type="button" class="btn btn-outline-secondary" onclick="insertContentImage()">
                      <i class="bi bi-image me-1"></i> Chèn
                    </button>
                  </div>
                </div>
                <div class="text-muted mt-2" style="font-size:12px;">Đặt con trỏ tại đoạn muốn chèn ảnh, chọn ảnh và nhập chú thích.</div>
              </div>
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

  return uploadArticleImage(file);
}

async function uploadArticleImage(file) {
  const formData = new FormData();
  formData.append('image', file);
  const response = await fetch('api/upload_article_image.php', {
    method: 'POST',
    body: formData
  });
  return response.json();
}

function escapeHtml(value) {
  return value.replace(/[&<>"']/g, char => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  }[char]));
}

async function insertContentImage() {
  const input = document.getElementById('content_image_file');
  const captionInput = document.getElementById('content_image_caption');
  const content = document.getElementById('content');
  const file = input.files && input.files[0];

  if (!file) {
    showAlert('danger', 'Vui lòng chọn ảnh cần chèn vào nội dung.');
    return;
  }

  const uploaded = await uploadArticleImage(file);
  if (!uploaded || !uploaded.success) {
    showAlert('danger', uploaded?.message || 'Không thể tải ảnh lên.');
    return;
  }

  const caption = captionInput.value.trim();
  const captionHtml = caption ? `<figcaption>${escapeHtml(caption)}</figcaption>` : '';
  const figureHtml = `\n\n<figure class="article-inline-image"><img src="${uploaded.url}" alt="${escapeHtml(caption || 'Ảnh bài viết')}" loading="lazy">${captionHtml}</figure>\n\n`;
  const start = content.selectionStart;
  const end = content.selectionEnd;

  content.value = content.value.slice(0, start) + figureHtml + content.value.slice(end);
  content.focus();
  content.selectionStart = content.selectionEnd = start + figureHtml.length;
  input.value = '';
  captionInput.value = '';
  showAlert('success', 'Đã chèn ảnh vào nội dung bài viết.');
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

<?php include __DIR__ . '/../partials/footer.php'; ?>
