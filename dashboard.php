<?php
// ============================================================
// dashboard.php — Bảng điều khiển tối cao của Admin
// ============================================================
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

// KIỂM TRA PHÂN QUYỀN: Nếu chưa đăng nhập hoặc không phải admin, đá về trang login
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Xử lý các action (update role, delete user, delete article, update article status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'update_role') {
            $user_id = intval($_POST['user_id'] ?? 0);
            $new_role = $_POST['role'] ?? 'user';
            if ($user_id !== $_SESSION['user_id']) {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$new_role, $user_id]);
            }
        } elseif ($action === 'delete_user') {
            $user_id = intval($_POST['user_id'] ?? 0);
            if ($user_id !== $_SESSION['user_id']) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
            }
          } elseif ($action === 'update_article_status') {
            $article_id = intval($_POST['article_id'] ?? 0);
            $new_status = $_POST['status'] ?? 'request';
        
            $allowed_statuses = ['request', 'published', 'disapproved'];
        
            if ($article_id <= 0 || !in_array($new_status, $allowed_statuses, true)) {
                throw new Exception('Trạng thái bài viết không hợp lệ.');
            }
        
            if ($new_status === 'published') {
                $stmt = $pdo->prepare("
                    UPDATE articles 
                    SET status = 'published',
                        published_at = NOW(),
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$article_id]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE articles 
                    SET status = ?,
                        published_at = NULL,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$new_status, $article_id]);
            }
        } elseif ($action === 'delete_article') {
            $article_id = intval($_POST['article_id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
            $stmt->execute([$article_id]);
        }
        
        // Redirect to prevent form resubmission
        header('Location: dashboard.php');
        exit;
    } catch (PDOException $e) {
        $error = "Lỗi xử lý: " . $e->getMessage();
    }
}

// Lấy danh sách users
try {
    $stmt = $pdo->query("SELECT id, username, email, full_name, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}

// Lấy danh sách bài viết
try {
    // Thay u.username bằng a.source vì bảng articles của bạn lưu nguồn/tác giả ở cột source
    $stmt = $pdo->query("
        SELECT a.id, a.title, a.status, a.created_at, a.source as author_name, c.name as category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        ORDER BY a.created_at DESC
    ");
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Bản dự phòng siêu an toàn: Nếu bảng categories của bạn bị lỗi hoặc chưa có cột name
    try {
        $stmt = $pdo->query("SELECT id, title, status, created_at, source as author_name FROM articles ORDER BY created_at DESC");
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $articles = [];
    }
}

// Load feedbacks
$feedbacks = []; 
$fcnt = ['pending'=>0,'replied'=>0,'done'=>0];
try {
    $st = $pdo->query(
        "SELECT f.*, u.username as sender_name FROM feedback f
         LEFT JOIN users u ON f.user_id = u.id
         ORDER BY FIELD(f.status,'pending','replied','done'), f.created_at DESC"
    );
    $feedbacks = $st->fetchAll(PDO::FETCH_ASSOC);
    foreach ($feedbacks as $fb) $fcnt[$fb['status']]++;
} catch (Exception $e) {}

// Lấy thống kê tổng quan (KPIs)
$stats = [
    'total_views' => 0,
    'total_articles' => 0,
    'total_users' => 0,
    'total_bookmarks' => 0
];
try {
    $stats['total_views'] = $pdo->query("SELECT SUM(view_count) FROM articles WHERE status IN ('published', 'Approved')")->fetchColumn() ?: 0;
    $stats['total_articles'] = $pdo->query("SELECT COUNT(id) FROM articles WHERE status IN ('published', 'Approved')")->fetchColumn() ?: 0;
    $stats['total_users'] = $pdo->query("SELECT COUNT(id) FROM users")->fetchColumn() ?: 0;
    $stats['total_bookmarks'] = $pdo->query("SELECT COUNT(id) FROM bookmarks")->fetchColumn() ?: 0;
} catch (PDOException $e) {}
$engagement_rate = $stats['total_views'] > 0 ? round(($stats['total_bookmarks'] / $stats['total_views']) * 100, 2) : 0;

// Hiệu suất theo danh mục
$cat_stats = [];
try {
    $stmt = $pdo->query("
        SELECT c.name, 
               COUNT(a.id) as article_count, 
               COALESCE(SUM(a.view_count), 0) as total_views
        FROM categories c
        LEFT JOIN articles a ON c.id = a.category_id AND a.status IN ('published', 'Approved')
        GROUP BY c.id
        ORDER BY total_views DESC
    ");
    $cat_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// Top 5 bài viết thịnh hành
$top_articles = [];
try {
    $stmt = $pdo->query("
        SELECT id, title, view_count, created_at
        FROM articles
        WHERE status IN ('published', 'Approved')
        ORDER BY view_count DESC, created_at DESC
        LIMIT 5
    ");
    $top_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

$status_labels = ['pending'=>'Chờ xử lý','replied'=>'Đã trả lời','done'=>'Hoàn tất'];
$status_colors = ['pending'=>'#f0ad4e','replied'=>'#5bc0de','done'=>'#5cb85c'];

$page_title = 'Bảng quản trị Admin — Thoáng.vn';
include 'partials/header.php';
?>

<div class="page-body">
  <div class="container-fluid px-4">
    <h2 class="mb-4" style="font-family: 'Playfair Display', serif; font-weight: 700; color: var(--navy);">Bảng quản trị hệ thống (Admin)</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- THỐNG KÊ TỔNG QUAN (KPIs) -->
    <div class="row mb-4">
      <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
        <div class="card shadow-sm border-0 p-3 bg-white text-center h-100 d-flex justify-content-center">
          <div class="text-muted mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase;"><i class="bi bi-file-earmark-text me-1"></i>Bài viết xuất bản</div>
          <h2 class="mb-0" style="color: var(--navy); font-weight: 700;"><?= number_format($stats['total_articles']) ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
        <div class="card shadow-sm border-0 p-3 bg-white text-center h-100 d-flex justify-content-center">
          <div class="text-muted mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase;"><i class="bi bi-eye me-1"></i>Tổng lượt xem</div>
          <h2 class="mb-0" style="color: #5cb85c; font-weight: 700;"><?= number_format($stats['total_views']) ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
        <div class="card shadow-sm border-0 p-3 bg-white text-center h-100 d-flex justify-content-center">
          <div class="text-muted mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase;"><i class="bi bi-bookmark-heart me-1"></i>Tổng lượt lưu tin</div>
          <h2 class="mb-0" style="color: #f0ad4e; font-weight: 700;"><?= number_format($stats['total_bookmarks']) ?></h2>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
        <div class="card shadow-sm border-0 p-3 bg-white text-center h-100 d-flex justify-content-center" title="Tỷ lệ người dùng lưu tin sau khi xem (Lượt lưu / Lượt xem * 100)">
          <div class="text-muted mb-2" style="font-size: 12px; font-weight: 600; text-transform: uppercase;"><i class="bi bi-graph-up-arrow me-1"></i>Tỷ lệ tương tác</div>
          <h2 class="mb-0" style="color: #d9534f; font-weight: 700;"><?= $engagement_rate ?>%</h2>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-xl-5 mb-4">
        <!-- Quản lý Thành viên -->
        <div class="card shadow-sm border-0 p-4 bg-white">
          <span class="section-label">Quản lý Thành viên</span>
          <div class="table-responsive mt-3">
            <table class="table table-hover align-middle" style="font-size: 14px;">
              <thead class="table-light">
                <tr>
                  <th>Tên</th>
                  <th>Vai trò</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($u['username']) ?></strong><br>
                    <span class="text-muted" style="font-size: 12px;"><?= htmlspecialchars($u['email']) ?></span>
                  </td>
                  <td>
                    <?php if ($u['id'] === $_SESSION['user_id']): ?>
                      <span class="badge bg-primary">Admin (Bạn)</span>
                    <?php else: ?>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <select name="role" class="form-select form-select-sm admin-control-select" onchange="this.form.submit()">
                          <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                          <option value="writer" <?= $u['role'] === 'writer' ? 'selected' : '' ?>>Writer</option>
                          <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                      </form>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                      <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa user này?');">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Hộp thư góp ý phản hồi -->
        <div class="card shadow-sm border-0 p-4 bg-white mt-4">
          <span class="section-label">Hộp thư góp ý phản hồi</span>
          
          <div class="d-flex gap-3 mb-4 flex-wrap mt-3">
            <div class="feedback-stat-box">
              <div class="feedback-stat-num" style="color:#f0ad4e"><?= $fcnt['pending'] ?></div>
              <div class="feedback-stat-label">Chờ xử lý</div>
            </div>
            <div class="feedback-stat-box">
              <div class="feedback-stat-num" style="color:#5bc0de"><?= $fcnt['replied'] ?></div>
              <div class="feedback-stat-label">Đã trả lời</div>
            </div>
            <div class="feedback-stat-box">
              <div class="feedback-stat-num" style="color:#5cb85c"><?= $fcnt['done'] ?></div>
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
              <div class="fb-item" id="fb-item-<?= $fb['id'] ?>">
                <div class="fb-header" onclick="toggleFb(<?= $fb['id'] ?>)">
                  <span class="fb-status <?= $fb['status'] ?>"><?= $status_labels[$fb['status']] ?></span>
                  <span class="fb-email"><?= htmlspecialchars($fb['sender_email'] ?? '') ?></span>
                  <span class="fb-subject"><?= htmlspecialchars($fb['subject']) ?></span>
                  <i class="bi bi-chevron-down ms-auto" id="fb-icon-<?= $fb['id'] ?>"></i>
                </div>
                <div class="fb-body" id="fb-body-<?= $fb['id'] ?>">
                  <p class="mb-1" style="font-size:11px;color:var(--muted)">
                    <?= $fb['sender_name'] ? 'Người dùng: <strong>' . htmlspecialchars($fb['sender_name']) . '</strong> · ' : 'Khách vãng lai · ' ?>
                    <?= date('d/m/Y H:i', strtotime($fb['created_at'])) ?>
                  </p>
                  <div class="fb-message"><?= nl2br(htmlspecialchars($fb['message'])) ?></div>
                  <?php if ($fb['admin_reply']): ?>
                    <div class="fb-reply-box">
                      <strong>Phản hồi của Admin <span style="font-weight:400;text-transform:none;font-size:11px">(<?= $fb['replied_at'] ? date('d/m/Y', strtotime($fb['replied_at'])) : '' ?>)</span></strong>
                      <?= nl2br(htmlspecialchars($fb['admin_reply'])) ?>
                    </div>
                  <?php endif; ?>
                  <div id="reply-area-<?= $fb['id'] ?>" style="<?= $fb['status'] === 'done' ? 'display:none' : '' ?>">
                    <textarea class="fb-reply-input" id="reply-text-<?= $fb['id'] ?>"
                      placeholder="Nhập nội dung trả lời..."><?= htmlspecialchars($fb['admin_reply'] ?? '') ?></textarea>
                  </div>
                  <div class="fb-actions">
                    <?php if ($fb['status'] !== 'done'): ?>
                      <button class="btn-fb btn-fb-reply" onclick="fbReply(<?= $fb['id'] ?>)">
                        <i class="bi bi-reply me-1"></i>Gửi phản hồi
                      </button>
                      <button class="btn-fb btn-fb-done" onclick="fbMarkDone(<?= $fb['id'] ?>)">
                        <i class="bi bi-check2-circle me-1"></i>Hoàn tất
                      </button>
                    <?php else: ?>
                      <button class="btn-fb btn-fb-pending" onclick="fbMarkPending(<?= $fb['id'] ?>)">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Mở lại
                      </button>
                    <?php endif; ?>
                    <button class="btn-fb btn-fb-del" onclick="fbDelete(<?= $fb['id'] ?>)">
                      <i class="bi bi-trash me-1"></i>Xoá
                    </button>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-xl-7 mb-4">
        <div class="card shadow-sm border-0 p-4 bg-white">
          <span class="section-label">Phê duyệt & Kiểm soát bài viết</span>
          
          <div class="table-responsive mt-3">
            <table class="table table-hover align-middle" style="font-size: 14px;">
              <thead class="table-light">
                <tr>
                  <th>Bài viết</th>
                  <th>Trạng thái</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($articles as $a): ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($a['title']) ?></strong><br>
                    <span class="text-muted" style="font-size: 12px;">
                      Tác giả: <?= htmlspecialchars($a['author_name'] ?? 'Không rõ') ?> · 
                      DM: <?= htmlspecialchars($a['category_name'] ?? 'Không rõ') ?> ·
                      <?= date('d/m/Y', strtotime($a['created_at'])) ?>
                    </span>
                  </td>
                  <td>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="action" value="update_article_status">
                      <input type="hidden" name="article_id" value="<?= $a['id'] ?>">
                      <select name="status" class="form-select form-select-sm admin-control-select admin-status-select" onchange="this.form.submit()">
                      <option value="request" <?= $a['status'] === 'request' ? 'selected' : '' ?>>Chờ duyệt</option>
                      <option value="published" <?= ($a['status'] === 'published' || $a['status'] === 'Approved') ? 'selected' : '' ?>>Đã duyệt</option>
                      <option value="disapproved" <?= $a['status'] === 'disapproved' ? 'selected' : '' ?>>Không duyệt</option>
                      </select>
                    </form>
                  </td>
                  <td>
                    <a href="article.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary" title="Xem"><i class="bi bi-eye"></i></a>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');">
                      <input type="hidden" name="action" value="delete_article">
                      <input type="hidden" name="article_id" value="<?= $a['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        </div>

        <!-- CÁC BẢNG THỐNG KÊ CHI TIẾT -->
        <div class="row">
          <div class="col-md-6 mt-4">
            <!-- Báo cáo hiệu suất danh mục -->
            <div class="card shadow-sm border-0 p-4 bg-white h-100">
              <span class="section-label">Hiệu suất theo danh mục</span>
              <div class="table-responsive mt-2">
                <table class="table table-borderless align-middle" style="font-size: 13px;">
                  <thead class="table-light" style="border-bottom: 2px solid var(--border)">
                    <tr>
                      <th>Chủ đề</th>
                      <th class="text-center">Số bài</th>
                      <th class="text-end">Tỷ lệ View</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($cat_stats as $c): ?>
                      <?php $percent = $stats['total_views'] > 0 ? round(($c['total_views'] / $stats['total_views']) * 100, 1) : 0; ?>
                    <tr style="border-bottom: 1px solid var(--border)">
                      <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                      <td class="text-center"><span class="badge bg-secondary"><?= $c['article_count'] ?></span></td>
                      <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                          <span style="font-size: 12px; color: var(--muted);"><?= $percent ?>%</span>
                          <div class="progress" style="height: 5px; width: 40px;">
                            <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="col-md-6 mt-4">
            <!-- Top 5 bài viết thịnh hành -->
            <div class="card shadow-sm border-0 p-4 bg-white h-100">
              <span class="section-label">Top 5 Bài viết thịnh hành</span>
              <div class="list-group list-group-flush mt-2">
                <?php foreach ($top_articles as $idx => $ta): ?>
                <div class="list-group-item px-0 py-2 d-flex align-items-center gap-2" style="border-bottom: 1px dashed var(--border);">
                  <div style="font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 800; color: var(--border); min-width: 20px; text-align: center;"><?= $idx + 1 ?></div>
                  <div class="flex-grow-1 text-truncate">
                    <a href="article.php?id=<?= $ta['id'] ?>" target="_blank" style="font-weight: 600; font-size: 13px; color: var(--navy); text-decoration: none;" title="<?= htmlspecialchars($ta['title']) ?>"><?= htmlspecialchars($ta['title']) ?></a>
                  </div>
                  <div class="text-end" style="min-width: 35px;"><div style="font-weight: 700; color: #d9534f; font-size: 13px;" title="<?= number_format($ta['view_count']) ?> lượt xem"><?= number_format($ta['view_count']) ?> <i class="bi bi-eye ms-1" style="font-size: 10px; color: var(--muted);"></i></div></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<div class="toast-msg" id="toastMsg"></div>

<script>
function toggleFb(id) {
  var body = document.getElementById('fb-body-' + id);
  var icon = document.getElementById('fb-icon-' + id);
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
  t.textContent = msg;
  t.style.display = 'block';
  t.style.borderLeftColor = ok ? '#5cb85c' : '#dc3545';
  clearTimeout(t._tid);
  t._tid = setTimeout(function(){ t.style.display='none'; }, 3200);
}

function fbAction(id, action, extra) {
  var fd = new FormData();
  fd.append('action', action);
  fd.append('feedback_id', id);
  if (extra) fd.append('reply', extra);

  fetch('api/feedback_action.php', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(res) {
      if (res.success) {
        showToast('✓ ' + res.message, true);
        setTimeout(function(){ location.reload(); }, 700);
      } else {
        showToast('✗ ' + res.message, false);
      }
    })
    .catch(function(){ showToast('✗ Lỗi kết nối.', false); });
}

function fbReply(id) {
  var txt = document.getElementById('reply-text-' + id).value.trim();
  if (!txt) { showToast('Vui lòng nhập nội dung phản hồi.', false); return; }
  fbAction(id, 'reply', txt);
}
function fbMarkDone(id)    { fbAction(id, 'mark_done'); }
function fbMarkPending(id) { fbAction(id, 'mark_pending'); }
function fbDelete(id) {
  if (!confirm('Xoá góp ý này?')) return;
  fbAction(id, 'delete');
}
</script>

<?php include 'partials/footer.php'; ?>
