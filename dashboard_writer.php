<?php
session_start();

require_once 'config/db.php';
require_once 'config/session.php';

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'writer') {
    header('Location: login.php');
    exit;
}

$author_id = (int)($_SESSION['user_id'] ?? 0);
$error = '';

function shortText(string $text, int $limit = 120): string
{
    $text = trim(strip_tags($text));

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $limit, '...', 'UTF-8');
    }

    return strlen($text) > $limit
        ? substr($text, 0, $limit - 3) . '...'
        : $text;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $article_id = (int)($_POST['article_id'] ?? 0);

    try {
        if ($action === 'delete_article') {
            if ($article_id <= 0) {
                throw new Exception('Bài viết không hợp lệ.');
            }

            $stmt = $pdo->prepare("
                DELETE FROM articles
                WHERE id = ?
                  AND author_id = ?
            ");
            $stmt->execute([$article_id, $author_id]);

            header('Location: dashboard_writer.php');
            exit;
        }
    } catch (Exception $e) {
        $error = 'Lỗi xử lý: ' . $e->getMessage();
    }
}

$articles_by_status = [
    'request' => [],
    'Approved' => [],
    'disapproved' => [],
];

$stats = [
    'total' => 0,
    'request' => 0,
    'Approved' => 0,
    'disapproved' => 0,
    'total_views' => 0,
];

$cat_views_data = [];

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
            a.source,
            c.name AS category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.author_id = ?
        ORDER BY 
            FIELD(a.status, 'request', 'Approved', 'disapproved'),
            a.updated_at DESC,
            a.created_at DESC
    ");

    $stmt->execute([$author_id]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($articles as $article) {
        if ($article['status'] === 'Approved') {
            $key = 'Approved';
            $cname = $article['category_name'] ?? 'Chưa phân loại';
            if (!isset($cat_views_data[$cname])) {
                $cat_views_data[$cname] = 0;
            }
            $cat_views_data[$cname] += (int)$article['view_count'];
        } elseif ($article['status'] === 'disapproved') {
            $key = 'disapproved';
        } else {
            $key = 'request';
        }

        $articles_by_status[$key][] = $article;

        $stats['total']++;
        $stats[$key]++;
        $stats['total_views'] += (int)$article['view_count'];
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
        <h3 class="mb-1" style="font-family:'Playfair Display',serif;font-weight:700;color:var(--navy);">
          Không gian viết bài
        </h3>
        <div class="text-muted" style="font-size:13px;">
          Theo dõi trạng thái duyệt, chỉnh sửa và quản lý bài viết của bạn.
        </div>
      </div>

      <a href="vietbai.php" class="btn btn-sm" style="background:var(--navy);color:#fff;font-weight:700;">
        <i class="bi bi-pencil-square me-1"></i> Viết bài mới
      </a>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
          <div class="text-muted" style="font-size:13px;">Tổng bài viết</div>
          <h4 class="mb-0"><?= number_format($stats['total']) ?></h4>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
          <div class="text-muted" style="font-size:13px;">Chờ duyệt</div>
          <h4 class="mb-0 text-warning"><?= number_format($stats['request']) ?></h4>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
          <div class="text-muted" style="font-size:13px;">Đã duyệt</div>
          <h4 class="mb-0 text-success"><?= number_format($stats['Approved']) ?></h4>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3">
          <div class="text-muted" style="font-size:13px;">Tổng lượt xem</div>
          <h4 class="mb-0"><?= number_format($stats['total_views']) ?></h4>
        </div>
      </div>
    </div>

    <!-- BIỂU ĐỒ TRỰC QUAN -->
    <div class="row mb-4">
      <div class="col-md-6 mb-3 mb-md-0">
        <div class="card shadow-sm border-0 p-4 bg-white h-100">
          <span class="section-label">Lượt xem theo danh mục (Đã duyệt)</span>
          <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
            <canvas id="writerCatViewsChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card shadow-sm border-0 p-4 bg-white h-100">
          <span class="section-label">Trạng thái bài viết</span>
          <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
            <canvas id="writerStatusChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="writerArticleTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="request-tab" data-bs-toggle="tab" data-bs-target="#request-pane" type="button" role="tab">
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
        <button class="nav-link" id="disapproved-tab" data-bs-toggle="tab" data-bs-target="#disapproved-pane" type="button" role="tab">
          Không được duyệt
          <span class="badge text-bg-secondary ms-1">
            <?= count($articles_by_status['disapproved']) ?>
          </span>
        </button>
      </li>
    </ul>

    <div class="tab-content" id="writerArticleTabsContent">
      <?php
      $panes = [
          'request' => [
              'pane_id' => 'request-pane',
              'label' => 'Danh sách bài viết chờ duyệt',
              'active' => true,
          ],
          'Approved' => [
              'pane_id' => 'approved-pane',
              'label' => 'Danh sách bài viết đã được duyệt',
              'active' => false,
          ],
          'disapproved' => [
              'pane_id' => 'disapproved-pane',
              'label' => 'Danh sách bài viết không được duyệt',
              'active' => false,
          ],
      ];
      ?>

      <?php foreach ($panes as $status_key => $pane): ?>
        <?php $items = $articles_by_status[$status_key]; ?>

        <div class="tab-pane fade <?= $pane['active'] ? 'show active' : '' ?>" id="<?= $pane['pane_id'] ?>" role="tabpanel">
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
                      <?php
                      $statusLabel = 'Chờ duyệt';
                      $statusClass = 'text-bg-warning';

                      if ($article['status'] === 'Approved') {
                          $statusLabel = 'Đã duyệt';
                          $statusClass = 'text-bg-success';
                      } elseif ($article['status'] === 'disapproved') {
                          $statusLabel = 'Không được duyệt';
                          $statusClass = 'text-bg-secondary';
                      }
                      ?>

                      <tr>
                        <td style="min-width:320px;">
                          <strong>
                            <?= htmlspecialchars($article['title']) ?>
                          </strong>

                          <div class="text-muted mt-1" style="font-size:12px;line-height:1.5;">
                            <?= htmlspecialchars(shortText($article['summary'] ?? '', 140)) ?>
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
                          <span class="badge <?= $statusClass ?>">
                            <?= htmlspecialchars($statusLabel) ?>
                          </span>
                        </td>

                        <td class="text-center">
                          <span class="badge bg-light text-dark border">
                            <i class="bi bi-eye me-1"></i>
                            <?= number_format((int)$article['view_count']) ?>
                          </span>
                        </td>

                        <td style="font-size:12px;color:var(--muted);min-width:170px;">
                          Tạo: <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?><br>

                          <?php if ($article['status'] === 'Approved' && !empty($article['published_at'])): ?>
                            Đăng: <?= date('d/m/Y H:i', strtotime($article['published_at'])) ?>
                          <?php else: ?>
                            Cập nhật: <?= date('d/m/Y H:i', strtotime($article['updated_at'])) ?>
                          <?php endif; ?>
                        </td>

                        <td class="text-end admin-actions-cell">
                          <div class="admin-action-group">
                            <a href="article.php?id=<?= (int)$article['id'] ?>" class="btn btn-sm btn-outline-primary" title="Xem bài">
                              <i class="bi bi-eye"></i>
                            </a>

                            <a href="vietbai.php?id=<?= (int)$article['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Chỉnh sửa">
                              <i class="bi bi-pencil-square"></i> <span>Sửa</span>
                            </a>

                            <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');">
                              <input type="hidden" name="action" value="delete_article">
                              <input type="hidden" name="article_id" value="<?= (int)$article['id'] ?>">

                              <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                <i class="bi bi-trash"></i>
                              </button>
                            </form>
                          </div>
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

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
Chart.register(ChartDataLabels);

// Biểu đồ lượt xem theo danh mục
const writerCatNames = <?= json_encode(array_keys($cat_views_data)) ?>;
const writerCatViews = <?= json_encode(array_values($cat_views_data)) ?>;

if (document.getElementById('writerCatViewsChart')) {
  new Chart(document.getElementById('writerCatViewsChart'), {
    type: 'doughnut',
    data: {
      labels: writerCatNames,
      datasets: [{
        data: writerCatViews,
        backgroundColor: ['#534AB7', '#155724', '#721c24', '#856404', '#0c5460', '#383d41', '#374151', '#e83e8c', '#fd7e14', '#20c997'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'right', labels: { font: { size: 11, family: "'Be Vietnam Pro', sans-serif" } } },
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
const writerStatusLabels = ['Đã duyệt', 'Chờ duyệt', 'Không duyệt'];
const writerStatusData = [
  <?= (int)$stats['Approved'] ?>,
  <?= (int)$stats['request'] ?>,
  <?= (int)$stats['disapproved'] ?>
];

if (document.getElementById('writerStatusChart')) {
  new Chart(document.getElementById('writerStatusChart'), {
    type: 'pie',
    data: {
      labels: writerStatusLabels,
      datasets: [{
        data: writerStatusData,
        backgroundColor: ['#198754', '#ffc107', '#6c757d'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'right', labels: { font: { size: 11, family: "'Be Vietnam Pro', sans-serif" } } },
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

<?php include 'partials/footer.php'; ?>
