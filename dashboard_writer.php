<?php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

if (!isLoggedIn() || ($_SESSION['role'] !== 'writer' && $_SESSION['role'] !== 'admin')) {
    header('Location: login.php');
    exit;
}

$author_id = (int)$_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $article_id = (int)($_POST['article_id'] ?? 0);

    try {
        if ($action === 'delete_article' && $article_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ? AND author_id = ?");
            $stmt->execute([$article_id, $author_id]);
        }

        header('Location: dashboard_writer.php');
        exit;
    } catch (PDOException $e) {
        $error = 'Lỗi xử lý: ' . $e->getMessage();
    }
}

$status_labels = [
    'request' => 'Chờ duyệt',
    'Approved' => 'Đã duyệt',
    'disapproved' => 'Không được duyệt',
];

$articles_by_status = [
    'request' => [],
    'Approved' => [],
    'disapproved' => [],
];

function shortText(string $text, int $limit = 120): string {
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $limit, '...', 'UTF-8');
    }

    return strlen($text) > $limit ? substr($text, 0, $limit - 3) . '...' : $text;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.title,
            a.summary,
            a.status,
            a.view_count,
            a.created_at,
            a.updated_at,
            a.published_at,
            a.tags,
            c.name AS category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.author_id = ?
        ORDER BY FIELD(a.status, 'request', 'Approved', 'disapproved'), a.updated_at DESC
    ");

    $stmt->execute([$author_id]);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $article) {
        if ($article['status'] === 'Approved') {
            $key = 'Approved';
        } elseif ($article['status'] === 'disapproved') {
            $key = 'disapproved';
        } else {
            $key = 'request';
        }

        $articles_by_status[$key][] = $article;
    }
} catch (PDOException $e) {
    $error = 'Không thể tải danh sách bài viết: ' . $e->getMessage();
}

$page_title = 'Không gian viết bài - Thoáng.vn';
include 'partials/header.php';
?>

<div class="page-body">
  <div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
      <div>
        <h3 class="mb-1" style="font-family: 'Playfair Display', serif; font-weight: 700; color: var(--navy);">
          Quản lý bài viết tác giả
        </h3>
        <div class="text-muted" style="font-size:13px;">
          Theo dõi trạng thái duyệt, lượt xem và chỉnh sửa bài của bạn.
        </div>
      </div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-3" id="writerArticleTabs" role="tablist">

      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-pane" type="button" role="tab">
          Chờ duyệt
          <span class="badge text-bg-warning ms-1">
            <?= count($articles_by_status['request']) ?>
          </span>
        </button>
      </li>

      <li class="nav-item" role="presentation">
        <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-pane" type="button" role="tab">
          Đã duyệt
          <span class="badge text-bg-success ms-1">
            <?= count($articles_by_status['Approved']) ?>
          </span>
        </button>
      </li>

      <li class="nav-item" role="presentation">
        <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected-pane" type="button" role="tab">
          Không được duyệt
          <span class="badge text-bg-secondary ms-1">
            <?= count($articles_by_status['disapproved']) ?>
          </span>
        </button>
      </li>

    </ul>

    <div class="tab-content">
      <?php
      $panes = [
          'pending' => [
              'status' => 'request',
              'label' => 'Danh sách bài viết chờ duyệt',
              'active' => true
          ],
          'approved' => [
              'status' => 'Approved',
              'label' => 'Danh sách bài viết đã được duyệt',
              'active' => false
          ],
          'rejected' => [
              'status' => 'disapproved',
              'label' => 'Danh sách bài viết không được duyệt',
              'active' => false
          ],
      ];

      foreach ($panes as $pane_id => $pane):
          $items = $articles_by_status[$pane['status']];
      ?>

      <div class="tab-pane fade <?= $pane['active'] ? 'show active' : '' ?>" id="<?= $pane_id ?>-pane" role="tabpanel" tabindex="0">
        <div class="card shadow-sm border-0 p-4 bg-white">

          <span class="section-label">
            <?= htmlspecialchars($pane['label']) ?>
          </span>

          <?php if (empty($items)): ?>
            <div class="empty-state py-4">
              <i class="bi bi-file-earmark-text"></i>
              Chưa có bài viết nào trong nhóm này.
            </div>
          <?php else: ?>

            <div class="table-responsive mt-3">
              <table class="table table-hover align-middle" style="font-size:14px;">
                <thead class="table-light">
                  <tr>
                    <th>Bài viết</th>
                    <th>Danh mục</th>
                    <th>Trạng thái</th>
                    <th class="text-center">Lượt xem</th>
                    <th>Thời gian</th>
                    <th class="text-end">Hành động</th>
                  </tr>
                </thead>

                <tbody>
                <?php foreach ($items as $article): ?>
                  <tr>
                    <td style="min-width:300px;">
                      <strong>
                        <?= htmlspecialchars($article['title']) ?>
                      </strong>

                      <div class="text-muted mt-1" style="font-size:12px;line-height:1.5;">
                        <?= htmlspecialchars(shortText($article['summary'] ?? '')) ?>
                      </div>

                      <?php if (!empty($article['tags'])): ?>
                        <div class="mt-2">
                          <?php foreach (array_filter(array_map('trim', explode(',', $article['tags']))) as $tag): ?>
                            <span class="card-tag">
                              <?= htmlspecialchars($tag) ?>
                            </span>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </td>

                    <td>
                      <?= htmlspecialchars($article['category_name'] ?? 'Chưa phân loại') ?>
                    </td>

                    <td>
                      <?php if ($article['status'] === 'request'): ?>
                        <span class="badge text-bg-warning">Chờ duyệt</span>
                      <?php elseif ($article['status'] === 'Approved'): ?>
                        <span class="badge text-bg-success">Đã duyệt</span>
                      <?php elseif ($article['status'] === 'disapproved'): ?>
                        <span class="badge text-bg-secondary">Không được duyệt</span>
                      <?php endif; ?>
                    </td>

                    <td class="text-center">
                      <span class="badge bg-light text-dark border">
                        <i class="bi bi-eye me-1"></i>
                        <?= number_format((int)$article['view_count']) ?>
                      </span>
                    </td>

                    <td style="font-size:12px;color:var(--muted);min-width:160px;">
                      Tạo: <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?><br>

                      <?php if ($article['status'] === 'Approved' && !empty($article['published_at'])): ?>
                        Đăng: <?= date('d/m/Y H:i', strtotime($article['published_at'])) ?>
                      <?php else: ?>
                        Cập nhật: <?= date('d/m/Y H:i', strtotime($article['updated_at'])) ?>
                      <?php endif; ?>
                    </td>

                    <td class="text-end" style="min-width:130px;">
                      <a href="article.php?id=<?= (int)$article['id'] ?>" class="btn btn-sm btn-outline-primary" title="Xem bài">
                        <i class="bi bi-eye"></i>
                      </a>

                      <a href="vietbai.php?id=<?= (int)$article['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Chỉnh sửa">
                        <i class="bi bi-pencil-square"></i>
                      </a>

                      <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');">
                        <input type="hidden" name="action" value="delete_article">
                        <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">

                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          <?php endif; ?>

        </div>
      </div>

      <?php endforeach; ?>
    </div>

    <div class="d-flex justify-content-center mt-5">
      <a href="vietbai.php" class="btn" style="background: var(--navy); color: #fff; font-weight: 700; padding: 10px 24px;">
        <i class="bi bi-pencil-square me-1"></i> Viết bài
      </a>
    </div>

  </div>
</div>

<?php include 'partials/footer.php'; ?>