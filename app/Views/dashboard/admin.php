<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="page-body">
  <div class="container-fluid px-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
      <div>
        <h2 class="mb-1" style="font-family:'Playfair Display',serif;font-weight:700;color:var(--navy);">
          <?= ($admin_view ?? 'overview') === 'categories' ? 'Quản lý danh mục' : 'Bảng quản trị hệ thống' ?>
        </h2>
        <div class="text-muted" style="font-size:13px;">
          <?= ($admin_view ?? 'overview') === 'categories'
              ? 'Thêm, chỉnh sửa, ẩn/hiện và sắp xếp các danh mục hiển thị trên website.'
              : 'Quản lý thành viên, duyệt bài writer, theo dõi phản hồi và thống kê hệ thống.' ?>
        </div>
      </div>

      <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-house-door me-1"></i> Về trang chủ
      </a>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if (($admin_view ?? 'overview') === 'categories'): ?>
    <div class="card shadow-sm border-0 p-4 bg-white mb-4" id="category-manager">
      <span class="section-label">Quản lý danh mục</span>

      <form method="POST" action="dashboard.php?view=categories#category-manager" class="row g-3 align-items-end mb-4">
        <input type="hidden" name="action" value="save_category">
        <input type="hidden" name="category_id" value="0">
        <div class="col-lg-3 col-md-6">
          <label class="form-label">Tên danh mục</label>
          <input type="text" name="name" class="form-control form-control-sm" placeholder="VD: Chính trị" required>
        </div>
        <div class="col-lg-2 col-md-6">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" class="form-control form-control-sm" placeholder="chinh-tri">
        </div>
        <div class="col-lg-3 col-md-6">
          <label class="form-label">Danh mục cha</label>
          <select name="parent_id" class="form-select form-select-sm">
            <option value="0">Không có</option>
            <?php foreach ($categories as $cat): ?>
              <?php if (empty($cat['parent_id'])): ?>
                <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-lg-1 col-md-3">
          <label class="form-label">Thứ tự</label>
          <input type="number" name="sort_order" class="form-control form-control-sm" min="1" placeholder="Tự động">
        </div>
        <div class="col-lg-1 col-md-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="is_active" id="newCatActive" checked>
            <label class="form-check-label" for="newCatActive">Hiện</label>
          </div>
        </div>
        <div class="col-lg-2 col-md-6">
          <button type="submit" class="btn btn-sm w-100" style="background:var(--navy);color:#fff;font-weight:700;">
            <i class="bi bi-plus-lg me-1"></i>Thêm
          </button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover align-middle" style="font-size:13px;">
          <thead class="table-light">
            <tr>
              <th>Tên</th>
              <th>Slug</th>
              <th>Danh mục cha</th>
              <th class="text-center">Bài viết</th>
              <th class="text-center">Hiện</th>
              <th class="text-center">Thứ tự</th>
              <th class="text-end">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($categories)): ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-4">Chưa có danh mục.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($categories as $cat): ?>
                <tr>
                  <td style="min-width:180px;">
                    <form method="POST" action="dashboard.php?view=categories#category-manager" id="cat-form-<?= (int)$cat['id'] ?>">
                      <input type="hidden" name="action" value="save_category">
                      <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
                      <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($cat['name']) ?>" required>
                    </form>
                  </td>
                  <td style="min-width:150px;">
                    <input form="cat-form-<?= (int)$cat['id'] ?>" type="text" name="slug" class="form-control form-control-sm" value="<?= htmlspecialchars($cat['slug']) ?>" required>
                  </td>
                  <td style="min-width:170px;">
                    <select form="cat-form-<?= (int)$cat['id'] ?>" name="parent_id" class="form-select form-select-sm">
                      <option value="0">Không có</option>
                      <?php foreach ($categories as $parent): ?>
                        <?php if (empty($parent['parent_id']) && (int)$parent['id'] !== (int)$cat['id']): ?>
                          <option value="<?= (int)$parent['id'] ?>" <?= (int)($cat['parent_id'] ?? 0) === (int)$parent['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($parent['name']) ?>
                          </option>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td class="text-center"><?= number_format((int)$cat['article_count']) ?></td>
                  <td class="text-center">
                    <input form="cat-form-<?= (int)$cat['id'] ?>" type="checkbox" name="is_active" <?= (int)$cat['is_active'] === 1 ? 'checked' : '' ?>>
                  </td>
                  <td style="width:90px;">
                    <input form="cat-form-<?= (int)$cat['id'] ?>" type="number" name="sort_order" class="form-control form-control-sm" value="<?= (int)$cat['sort_order'] ?>">
                  </td>
                  <td class="text-end" style="min-width:120px;">
                    <button form="cat-form-<?= (int)$cat['id'] ?>" type="submit" class="btn btn-sm btn-outline-primary" title="Lưu">
                      <i class="bi bi-save"></i>
                    </button>
                    <form method="POST" action="dashboard.php?view=categories#category-manager" class="d-inline" onsubmit="return confirm('Xóa danh mục này? Chỉ xóa được khi chưa có bài viết và danh mục con.');">
                      <input type="hidden" name="action" value="delete_category">
                      <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php else: ?>

    <!-- THỐNG KÊ TỔNG QUAN -->
    <div class="row mb-4">
      <div class="col-md-3 col-sm-6 mb-3">
        <div class="card shadow-sm border-0 p-3 bg-white text-center h-100 d-flex justify-content-center">
          <div class="text-muted mb-2" style="font-size:12px;font-weight:600;text-transform:uppercase;">
            <i class="bi bi-file-earmark-text me-1"></i>Bài đã duyệt
          </div>
          <h2 class="mb-0" style="color:var(--navy);font-weight:700;">
            <?= number_format((int)$stats['total_articles']) ?>
          </h2>
          <div class="text-muted mt-1" style="font-size:12px;">
            Chờ duyệt: <?= number_format((int)$stats['pending_articles']) ?>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="card shadow-sm border-0 p-3 bg-white text-center h-100 d-flex justify-content-center">
          <div class="text-muted mb-2" style="font-size:12px;font-weight:600;text-transform:uppercase;">
            <i class="bi bi-eye me-1"></i>Tổng lượt xem
          </div>
          <h2 class="mb-0" style="color:#5cb85c;font-weight:700;">
            <?= number_format((int)$stats['total_views']) ?>
          </h2>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="card shadow-sm border-0 p-3 bg-white text-center h-100 d-flex justify-content-center">
          <div class="text-muted mb-2" style="font-size:12px;font-weight:600;text-transform:uppercase;">
            <i class="bi bi-bookmark-heart me-1"></i>Tổng lượt lưu tin
          </div>
          <h2 class="mb-0" style="color:#f0ad4e;font-weight:700;">
            <?= number_format((int)$stats['total_bookmarks']) ?>
          </h2>
          <div class="text-muted mt-1" style="font-size:12px;">
            Users: <?= number_format((int)$stats['total_users']) ?>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 mb-3">
        <div class="card shadow-sm border-0 p-3 bg-white text-center h-100 d-flex justify-content-center">
          <div class="text-muted mb-2" style="font-size:12px;font-weight:600;text-transform:uppercase;">
            <i class="bi bi-graph-up-arrow me-1"></i>Tỷ lệ tương tác
          </div>
          <h2 class="mb-0" style="color:#d9534f;font-weight:700;">
            <?= htmlspecialchars((string)$engagement_rate) ?>%
          </h2>
          <div class="text-muted mt-1" style="font-size:12px;">
            Không duyệt: <?= number_format((int)$stats['disapproved_articles']) ?>
          </div>
        </div>
      </div>
    </div>

    <!-- BIỂU ĐỒ TRỰC QUAN -->
    <div class="row mb-4">
      <div class="col-md-6 mb-3 mb-md-0">
        <div class="card shadow-sm border-0 p-4 bg-white h-100">
          <span class="section-label">Lượt xem theo danh mục</span>
          <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
            <canvas id="catViewsChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card shadow-sm border-0 p-4 bg-white h-100">
          <span class="section-label">Trạng thái bài viết</span>
          <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
            <canvas id="articleStatusChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- CỘT TRÁI -->
      <div class="col-xl-5 mb-4">

        <!-- QUẢN LÝ THÀNH VIÊN -->
        <div class="card shadow-sm border-0 p-4 bg-white">
          <span class="section-label">Quản lý thành viên</span>

          <div class="table-responsive mt-3">
            <table class="table table-hover align-middle" style="font-size:14px;">
              <thead class="table-light">
                <tr>
                  <th>Tên</th>
                  <th>Vai trò</th>
                  <th class="text-end">Hành động</th>
                </tr>
              </thead>

              <tbody>
                <?php if (empty($users)): ?>
                  <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                      Chưa có người dùng.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($users as $u): ?>
                    <tr>
                      <td>
                        <strong><?= htmlspecialchars($u['username']) ?></strong><br>
                        <span class="text-muted" style="font-size:12px;">
                          <?= htmlspecialchars($u['email']) ?>
                        </span>
                      </td>

                      <td>
                        <?php if ((int)$u['id'] === (int)($_SESSION['user_id'] ?? 0)): ?>
                          <span class="badge bg-primary">Admin (Bạn)</span>
                        <?php else: ?>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">

                            <select name="role" class="form-select form-select-sm admin-control-select" onchange="this.form.submit()">
                              <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                              <option value="writer" <?= $u['role'] === 'writer' ? 'selected' : '' ?>>Writer</option>
                              <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                          </form>
                        <?php endif; ?>
                      </td>

                      <td class="text-end">
                        <?php if ($u['role'] === 'writer'): ?>
                          <a href="writer.php?id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-primary" title="Xem hồ sơ writer">
                            <i class="bi bi-eye"></i>
                          </a>
                        <?php elseif ($u['role'] === 'user'): ?>
                          <a href="dashboard.php?comment_user_id=<?= (int)$u['id'] ?>#user-comments" class="btn btn-sm btn-outline-primary" title="Xem bình luận của user">
                            <i class="bi bi-eye"></i>
                          </a>
                        <?php endif; ?>
                        <?php if ((int)$u['id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                          <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa user này?');">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                              <i class="bi bi-trash"></i>
                            </button>
                          </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php if (!empty($selected_comment_user)): ?>
            <div class="user-comments-panel" id="user-comments">
              <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div>
                  <div class="account-eyebrow">Bình luận của user</div>
                  <h5 class="mb-1">
                    <?= htmlspecialchars($selected_comment_user['full_name'] ?: $selected_comment_user['username']) ?>
                  </h5>
                  <div class="text-muted" style="font-size:12px;">
                    @<?= htmlspecialchars($selected_comment_user['username']) ?> ·
                    <?= htmlspecialchars($selected_comment_user['email']) ?> ·
                    <?= count($selected_user_comments) ?> bình luận
                  </div>
                </div>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-x-lg me-1"></i>Đóng
                </a>
              </div>

              <?php if (empty($selected_user_comments)): ?>
                <div class="text-muted" style="font-size:13px;">User này chưa có bình luận nào.</div>
              <?php else: ?>
                <div class="user-comment-list">
                  <?php foreach ($selected_user_comments as $comment): ?>
                    <article class="user-comment-row">
                      <div class="d-flex justify-content-between gap-3 flex-wrap">
                        <div class="user-comment-article">
                          <a href="article.php?id=<?= (int)$comment['article_id'] ?>&from=dashboard#comments">
                            <?= htmlspecialchars($comment['article_title']) ?>
                          </a>
                          <span><?= htmlspecialchars($comment['category_name'] ?? 'Tin tức') ?></span>
                        </div>
                        <time><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></time>
                      </div>
                      <p>
                        <?= htmlspecialchars(mb_strlen($comment['content']) > 180 ? mb_substr($comment['content'], 0, 180) . '...' : $comment['content']) ?>
                      </p>
                    </article>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- HỘP THƯ GÓP Ý -->
        <div class="card shadow-sm border-0 p-4 bg-white mt-4">
          <span class="section-label">Hộp thư góp ý phản hồi</span>

          <div class="d-flex gap-3 mb-4 flex-wrap mt-3">
            <div class="feedback-stat-box">
              <div class="feedback-stat-num" style="color:#f0ad4e"><?= (int)$fcnt['pending'] ?></div>
              <div class="feedback-stat-label">Chờ xử lý</div>
            </div>

            <div class="feedback-stat-box">
              <div class="feedback-stat-num" style="color:#5bc0de"><?= (int)$fcnt['replied'] ?></div>
              <div class="feedback-stat-label">Đã trả lời</div>
            </div>

            <div class="feedback-stat-box">
              <div class="feedback-stat-num" style="color:#5cb85c"><?= (int)$fcnt['done'] ?></div>
              <div class="feedback-stat-label">Hoàn tất</div>
            </div>
          </div>

          <?php if (empty($feedbacks)): ?>
            <div class="text-center py-4" style="color:var(--muted);font-size:13px;">
              <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px"></i>
              Chưa có góp ý nào.
            </div>
          <?php else: ?>
            <div id="feedback-list">
              <?php foreach ($feedbacks as $fb): ?>
                <?php
                  $fbStatus = $fb['status'] ?? 'pending';
                  $fbLabel = $status_labels[$fbStatus] ?? 'Chờ xử lý';
                ?>

                <div class="fb-item" id="fb-item-<?= (int)$fb['id'] ?>">
                  <div class="fb-header" onclick="toggleFb(<?= (int)$fb['id'] ?>)">
                    <span class="fb-status <?= htmlspecialchars($fbStatus) ?>">
                      <?= htmlspecialchars($fbLabel) ?>
                    </span>

                    <span class="fb-email">
                      <?= htmlspecialchars($fb['sender_email'] ?? '') ?>
                    </span>

                    <span class="fb-subject">
                      <?= htmlspecialchars($fb['subject'] ?? '') ?>
                    </span>

                    <i class="bi bi-chevron-down ms-auto" id="fb-icon-<?= (int)$fb['id'] ?>"></i>
                  </div>

                  <div class="fb-body" id="fb-body-<?= (int)$fb['id'] ?>">
                    <p class="mb-1" style="font-size:11px;color:var(--muted)">
                      <?php if (!empty($fb['sender_name'])): ?>
                        Người dùng: <strong><?= htmlspecialchars($fb['sender_name']) ?></strong> &middot;
                      <?php else: ?>
                        Khách vãng lai &middot;
                      <?php endif; ?>

                      <?= date('d/m/Y H:i', strtotime($fb['created_at'])) ?>
                    </p>

                    <div class="fb-message">
                      <?= nl2br(htmlspecialchars($fb['message'] ?? '')) ?>
                    </div>

                    <?php if (!empty($fb['admin_reply'])): ?>
                      <div class="fb-reply-box">
                        <strong>
                          Phản hồi của Admin
                          <span style="font-weight:400;text-transform:none;font-size:11px">
                            <?= !empty($fb['replied_at']) ? '(' . date('d/m/Y', strtotime($fb['replied_at'])) . ')' : '' ?>
                          </span>
                        </strong>

                        <?= nl2br(htmlspecialchars($fb['admin_reply'])) ?>
                      </div>
                    <?php endif; ?>

                    <div id="reply-area-<?= (int)$fb['id'] ?>" style="<?= $fbStatus === 'done' ? 'display:none' : '' ?>">
                      <textarea class="fb-reply-input" id="reply-text-<?= (int)$fb['id'] ?>" placeholder="Nhập nội dung trả lời..."><?= htmlspecialchars($fb['admin_reply'] ?? '') ?></textarea>
                    </div>

                    <div class="fb-actions">
                      <?php if ($fbStatus !== 'done'): ?>
                        <button class="btn-fb btn-fb-reply" onclick="fbReply(<?= (int)$fb['id'] ?>)">
                          <i class="bi bi-reply me-1"></i>Gửi phản hồi
                        </button>

                        <button class="btn-fb btn-fb-done" onclick="fbMarkDone(<?= (int)$fb['id'] ?>)">
                          <i class="bi bi-check2-circle me-1"></i>Hoàn tất
                        </button>
                      <?php else: ?>
                        <button class="btn-fb btn-fb-pending" onclick="fbMarkPending(<?= (int)$fb['id'] ?>)">
                          <i class="bi bi-arrow-counterclockwise me-1"></i>Mở lại
                        </button>
                      <?php endif; ?>

                      <button class="btn-fb btn-fb-del" onclick="fbDelete(<?= (int)$fb['id'] ?>)">
                        <i class="bi bi-trash me-1"></i>Xoá
                      </button>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        
        <!-- BÌNH LUẬN MỚI NHẤT -->
        <div class="card shadow-sm border-0 p-4 bg-white mt-4">
          <span class="section-label">Bình luận mới nhất</span>
          <div class="list-group list-group-flush mt-2">
            <?php if (empty($recent_comments)): ?>
              <div class="text-center py-4" style="color:var(--muted);font-size:13px;">
                Chưa có bình luận nào.
              </div>
            <?php else: ?>
              <?php foreach ($recent_comments as $c): ?>
                <div class="list-group-item px-0 py-3 border-bottom">
                  <div class="d-flex justify-content-between mb-1">
                    <strong style="font-size:13px; color:var(--navy);">
                      <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($c['username']) ?>
                    </strong>
                    <div class="d-flex align-items-center gap-2">
                      <span style="font-size:11px; color:var(--muted);">
                        <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?>
                      </span>
                      <form method="POST" class="d-inline m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bình luận này?');">
                        <input type="hidden" name="action" value="delete_comment">
                        <input type="hidden" name="comment_id" value="<?= (int)$c['id'] ?>">
                        <button type="submit" class="btn btn-sm text-danger p-0 border-0 bg-transparent" title="Xóa bình luận">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                  <div style="font-size:13px; color:#444; margin-bottom:6px;">
                    "<?= nl2br(htmlspecialchars($c['content'])) ?>"
                  </div>
                  <div style="font-size:12px; color:var(--muted);">
                    Trong bài: <a href="article.php?id=<?= (int)$c['article_id'] ?>&from=dashboard#comments" style="text-decoration:none; color:var(--red);">
                      <?= htmlspecialchars($c['title']) ?>
                    </a>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- CỘT PHẢI -->
      <div class="col-xl-7 mb-4">

        <!-- PHÊ DUYỆT BÀI VIẾT -->
        <div class="card shadow-sm border-0 p-4 bg-white">
          <span class="section-label">Phê duyệt & kiểm soát bài viết</span>

          <div class="table-responsive mt-3">
            <table class="table table-hover align-middle" style="font-size:14px;">
              <thead class="table-light">
                <tr>
                  <th>Bài viết</th>
                  <th>Trạng thái</th>
                  <th class="text-end">Hành động</th>
                </tr>
              </thead>

              <tbody>
                <?php if (empty($articles)): ?>
                  <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                      Chưa có bài viết nào.
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($articles as $a): ?>
                    <?php
                      $authorName = $a['full_name']
                          ?: ($a['username']
                          ?: ($a['source'] ?: 'Không rõ'));

                      $statusText = 'Chờ duyệt';
                      $statusBadge = 'text-bg-warning';

                      if ($a['status'] === 'Approved') {
                          $statusText = 'Đã duyệt';
                          $statusBadge = 'text-bg-success';
                      } elseif ($a['status'] === 'disapproved') {
                          $statusText = 'Không duyệt';
                          $statusBadge = 'text-bg-secondary';
                      }
                    ?>

                    <tr>
                      <td>
                        <strong><?= htmlspecialchars($a['title']) ?></strong><br>
                        <span class="text-muted" style="font-size:12px;">
                          Tác giả: <?= htmlspecialchars($authorName) ?> &middot;
                          DM: <?= htmlspecialchars($a['category_name'] ?? 'Không rõ') ?> &middot;
                          Tạo: <?= date('d/m/Y', strtotime($a['created_at'])) ?>
                          <?php if ($a['status'] === 'Approved' && !empty($a['published_at'])): ?>
                            &middot; Đăng: <?= date('d/m/Y H:i', strtotime($a['published_at'])) ?>
                          <?php endif; ?>
                        </span>
                      </td>

                      <td style="min-width:155px;">
                        <form method="POST" class="d-inline">
                          <input type="hidden" name="action" value="update_article_status">
                          <input type="hidden" name="article_id" value="<?= (int)$a['id'] ?>">

                          <select name="status" class="form-select form-select-sm admin-control-select admin-status-select" onchange="this.form.submit()">
                            <option value="request" <?= $a['status'] === 'request' ? 'selected' : '' ?>>
                              Chờ duyệt
                            </option>

                            <option value="Approved" <?= $a['status'] === 'Approved' ? 'selected' : '' ?>>
                              Đã duyệt
                            </option>

                            <option value="disapproved" <?= $a['status'] === 'disapproved' ? 'selected' : '' ?>>
                              Không duyệt
                            </option>
                          </select>
                        </form>

                        <div class="mt-2">
                          <span class="badge <?= $statusBadge ?>">
                            <?= htmlspecialchars($statusText) ?>
                          </span>
                        </div>
                      </td>

                      <td class="text-end admin-actions-cell">
                        <div class="admin-action-group">
                          <a href="article.php?id=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline-primary" title="Xem bài">
                            <i class="bi bi-eye"></i>
                          </a>

                          <?php if ($a['status'] !== 'Approved'): ?>
                            <form method="POST" class="d-inline">
                              <input type="hidden" name="action" value="update_article_status">
                              <input type="hidden" name="article_id" value="<?= (int)$a['id'] ?>">
                              <input type="hidden" name="status" value="Approved">

                              <button type="submit" class="btn btn-sm btn-success" title="Duyệt bài">
                                <i class="bi bi-check-lg"></i>
                                <span>Duyệt</span>
                              </button>
                            </form>
                          <?php endif; ?>

                          <?php if ($a['status'] !== 'disapproved'): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn không duyệt bài viết này?');">
                              <input type="hidden" name="action" value="update_article_status">
                              <input type="hidden" name="article_id" value="<?= (int)$a['id'] ?>">
                              <input type="hidden" name="status" value="disapproved">

                              <button type="submit" class="btn btn-sm btn-outline-secondary" title="Không duyệt">
                                <i class="bi bi-x-lg"></i>
                                <span>Không duyệt</span>
                              </button>
                            </form>
                          <?php endif; ?>

                          <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');">
                            <input type="hidden" name="action" value="delete_article">
                            <input type="hidden" name="article_id" value="<?= (int)$a['id'] ?>">

                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                              <i class="bi bi-trash"></i>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- THỐNG KÊ CHI TIẾT -->
        <div class="row">
          <div class="col-md-6 mt-4">
            <div class="card shadow-sm border-0 p-4 bg-white h-100">
              <span class="section-label">Hiệu suất theo danh mục</span>

              <div class="table-responsive mt-2">
                <table class="table table-borderless align-middle" style="font-size:13px;">
                  <thead class="table-light" style="border-bottom:2px solid var(--border)">
                    <tr>
                      <th>Chủ đề</th>
                      <th class="text-center">Số bài</th>
                      <th class="text-end">Tỷ lệ view</th>
                    </tr>
                  </thead>

                  <tbody>
                    <?php if (empty($cat_stats)): ?>
                      <tr>
                        <td colspan="3" class="text-center text-muted py-3">
                          Chưa có dữ liệu.
                        </td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($cat_stats as $c): ?>
                        <?php
                          $percent = $stats['total_views'] > 0
                              ? round(((int)$c['total_views'] / (int)$stats['total_views']) * 100, 1)
                              : 0;
                        ?>

                        <tr style="border-bottom:1px solid var(--border)">
                          <td>
                            <strong><?= htmlspecialchars($c['name']) ?></strong>
                          </td>

                          <td class="text-center">
                            <span class="badge bg-secondary">
                              <?= number_format((int)$c['article_count']) ?>
                            </span>
                          </td>

                          <td class="text-end">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                              <span style="font-size:12px;color:var(--muted);">
                                <?= $percent ?>%
                              </span>

                              <div class="progress" style="height:5px;width:45px;">
                                <div class="progress-bar bg-success" style="width:<?= $percent ?>%"></div>
                              </div>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="col-md-6 mt-4">
            <div class="card shadow-sm border-0 p-4 bg-white h-100">
              <span class="section-label">Top 5 bài viết thịnh hành</span>

              <div class="list-group list-group-flush mt-2">
                <?php if (empty($top_articles)): ?>
                  <div class="text-center text-muted py-3" style="font-size:13px;">
                    Chưa có bài viết đã duyệt.
                  </div>
                <?php else: ?>
                  <?php foreach ($top_articles as $idx => $ta): ?>
                    <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                      <div style="font-family:'Playfair Display',serif;font-size:18px;font-weight:800;color:var(--border);min-width:22px;text-align:center;">
                        <?= $idx + 1 ?>
                      </div>

                      <div class="flex-grow-1 text-truncate">
                        <a href="article.php?id=<?= (int)$ta['id'] ?>" target="_blank" style="font-weight:600;font-size:13px;color:var(--navy);text-decoration:none;" title="<?= htmlspecialchars($ta['title']) ?>">
                          <?= htmlspecialchars($ta['title']) ?>
                        </a>
                      </div>

                      <div class="text-end" style="min-width:55px;">
                        <div style="font-weight:700;color:#d9534f;font-size:13px;" title="<?= number_format((int)$ta['view_count']) ?> lượt xem">
                          <?= number_format((int)$ta['view_count']) ?>
                          <i class="bi bi-eye ms-1" style="font-size:10px;color:var(--muted);"></i>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        
        <!-- THỐNG KÊ CHI TIẾT 2 -->
        <div class="row">
          <!-- Hiệu suất tác giả -->
          <div class="col-md-6 mt-4">
            <div class="card shadow-sm border-0 p-4 bg-white h-100">
              <span class="section-label">Hiệu suất tác giả</span>

              <div class="table-responsive mt-2">
                <table class="table table-borderless align-middle" style="font-size:13px;">
                  <thead class="table-light" style="border-bottom:2px solid var(--border)">
                    <tr>
                      <th>Tác giả</th>
                      <th class="text-center" title="Đã duyệt"><i class="bi bi-check-circle text-success"></i></th>
                      <th class="text-center" title="Chờ duyệt"><i class="bi bi-clock text-warning"></i></th>
                      <th class="text-center" title="Không duyệt"><i class="bi bi-x-circle text-secondary"></i></th>
                    </tr>
                  </thead>

                  <tbody>
                    <?php if (empty($writer_stats)): ?>
                      <tr>
                        <td colspan="4" class="text-center text-muted py-3">
                          Chưa có dữ liệu.
                        </td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($writer_stats as $w): ?>
                        <tr style="border-bottom:1px solid var(--border)">
                          <td>
                            <strong><?= htmlspecialchars($w['full_name'] ?: $w['username']) ?></strong>
                          </td>
                          <td class="text-center fw-bold text-success"><?= (int)$w['approved_count'] ?></td>
                          <td class="text-center fw-bold text-warning"><?= (int)$w['pending_count'] ?></td>
                          <td class="text-center fw-bold text-secondary"><?= (int)$w['disapproved_count'] ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Top lưu nhiều -->
          <div class="col-md-6 mt-4">
            <div class="card shadow-sm border-0 p-4 bg-white h-100">
              <span class="section-label">Top 5 bài được lưu</span>

              <div class="list-group list-group-flush mt-2">
                <?php if (empty($top_bookmarked)): ?>
                  <div class="text-center text-muted py-3" style="font-size:13px;">
                    Chưa có bài viết nào được lưu.
                  </div>
                <?php else: ?>
                  <?php foreach ($top_bookmarked as $idx => $tb): ?>
                    <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                      <div style="font-family:'Playfair Display',serif;font-size:18px;font-weight:800;color:var(--border);min-width:22px;text-align:center;">
                        <?= $idx + 1 ?>
                      </div>

                      <div class="flex-grow-1 text-truncate">
                        <a href="article.php?id=<?= (int)$tb['id'] ?>" target="_blank" style="font-weight:600;font-size:13px;color:var(--navy);text-decoration:none;" title="<?= htmlspecialchars($tb['title']) ?>">
                          <?= htmlspecialchars($tb['title']) ?>
                        </a>
                      </div>

                      <div class="text-end" style="min-width:55px;">
                        <div style="font-weight:700;color:#f0ad4e;font-size:13px;" title="<?= number_format((int)$tb['saves_count']) ?> lượt lưu">
                          <?= number_format((int)$tb['saves_count']) ?>
                          <i class="bi bi-bookmark-heart ms-1" style="font-size:10px;color:var(--muted);"></i>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <?php endif; ?>

  </div>
</div>

<div class="toast-msg" id="toastMsg"></div>

<script>
function toggleFb(id) {
  var body = document.getElementById('fb-body-' + id);
  var icon = document.getElementById('fb-icon-' + id);

  if (!body || !icon) return;

  var open = body.style.display !== 'none' && body.style.display !== '';

  if (open) {
    body.style.display = 'none';
    icon.className = 'bi bi-chevron-down ms-auto';
  } else {
    body.style.display = 'block';
    icon.className = 'bi bi-chevron-up ms-auto';
  }
}

function showToast(msg, ok) {
  var t = document.getElementById('toastMsg');

  if (!t) {
    alert(msg);
    return;
  }

  t.textContent = msg;
  t.style.display = 'block';
  t.style.borderLeftColor = ok ? '#5cb85c' : '#dc3545';

  clearTimeout(t._tid);
  t._tid = setTimeout(function () {
    t.style.display = 'none';
  }, 3200);
}

function fbAction(id, action, extra) {
  var fd = new FormData();

  fd.append('action', action);
  fd.append('feedback_id', id);

  if (extra) {
    fd.append('reply', extra);
  }

  fetch('api/feedback_action.php', {
    method: 'POST',
    body: fd
  })
  .then(function (r) {
    return r.json();
  })
  .then(function (res) {
    if (res.success) {
      showToast('OK: ' + res.message, true);
      setTimeout(function () {
        location.reload();
      }, 700);
    } else {
      showToast('✕ ' + (res.message || 'Không thể xử lý yêu cầu.'), false);
    }
  })
  .catch(function () {
    showToast('✕ Lỗi kết nối.', false);
  });
}

function fbReply(id) {
  var txtEl = document.getElementById('reply-text-' + id);
  var txt = txtEl ? txtEl.value.trim() : '';

  if (!txt) {
    showToast('Vui lòng nhập nội dung phản hồi.', false);
    return;
  }

  fbAction(id, 'reply', txt);
}

function fbMarkDone(id) {
  fbAction(id, 'mark_done');
}

function fbMarkPending(id) {
  fbAction(id, 'mark_pending');
}

function fbDelete(id) {
  if (!confirm('Xoá góp ý này?')) return;

  fbAction(id, 'delete');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
Chart.register(ChartDataLabels);
const adminChartTextColor = document.documentElement.getAttribute('data-theme') === 'dark' ? '#c6d1e2' : '#666';

// Biểu đồ lượt xem theo danh mục
const catNames = <?= json_encode(array_column($cat_stats, 'name')) ?>;
const catViews = <?= json_encode(array_column($cat_stats, 'total_views')) ?>;

if (document.getElementById('catViewsChart')) {
  new Chart(document.getElementById('catViewsChart'), {
    type: 'doughnut',
    data: {
      labels: catNames,
      datasets: [{
        data: catViews,
        backgroundColor: ['#534AB7', '#155724', '#721c24', '#856404', '#0c5460', '#383d41', '#374151', '#e83e8c', '#fd7e14', '#20c997'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'right', labels: { color: adminChartTextColor, font: { size: 11, family: "'Be Vietnam Pro', sans-serif" } } },
        datalabels: {
          color: '#fff',
          font: { weight: 'bold', size: 11 },
          formatter: (value, ctx) => {
            let sum = 0;
            let dataArr = ctx.chart.data.datasets[0].data;
            dataArr.map(data => { sum += Number(data); });
            if (sum === 0 || value == 0) return '';
            return (value * 100 / sum).toFixed(1) + "%";
          }
        }
      },
      cutout: '65%'
    }
  });
}

// Biểu đồ trạng thái bài viết
const statusLabels = ['Đã duyệt', 'Chờ duyệt', 'Không duyệt'];
const statusData = [
  <?= (int)$stats['total_articles'] ?>,
  <?= (int)$stats['pending_articles'] ?>,
  <?= (int)$stats['disapproved_articles'] ?>
];

if (document.getElementById('articleStatusChart')) {
  new Chart(document.getElementById('articleStatusChart'), {
    type: 'pie',
    data: {
      labels: statusLabels,
      datasets: [{
        data: statusData,
        backgroundColor: ['#198754', '#ffc107', '#6c757d'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'right', labels: { color: adminChartTextColor, font: { size: 11, family: "'Be Vietnam Pro', sans-serif" } } },
        datalabels: {
          color: '#fff',
          font: { weight: 'bold', size: 11 },
          formatter: (value, ctx) => {
            let sum = 0;
            let dataArr = ctx.chart.data.datasets[0].data;
            dataArr.map(data => { sum += Number(data); });
            if (sum === 0 || value == 0) return '';
            return (value * 100 / sum).toFixed(1) + "%";
          }
        }
      }
    }
  });
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
