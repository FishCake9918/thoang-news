<?php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';
$page_title = 'Đã lưu — Thoáng.vn';
$active_nav = 'saved';

// Lấy user_id từ session
$user_id = $_SESSION['user_id'] ?? 0;

// Lấy danh sách nhãn của user
$tags = [];
if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM saved_tags WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy danh sách bộ sưu tập của user
$collections = [];
if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM collections WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy bài đã lưu (có filter)
$filter_cat = $_GET['cat'] ?? 'all';
$search_kw  = trim($_GET['q'] ?? '');
$filter_tag = $_GET['tag'] ?? '';
$filter_col = $_GET['col'] ?? '';
$sort_by    = $_GET['sort'] ?? 'newest';

$where = ["sa.user_id = ?"];
$params = [$user_id];

if ($filter_cat !== 'all' && $filter_cat !== '') {
    $where[] = "sa.category = ?";
    $params[] = $filter_cat;
}
if ($search_kw !== '') {
    $where[] = "(sa.title LIKE ? OR sa.summary LIKE ?)";
    $params[] = "%$search_kw%";
    $params[] = "%$search_kw%";
}
if ($filter_tag !== '') {
    $where[] = "EXISTS (SELECT 1 FROM article_tags at2 WHERE at2.article_id = sa.id AND at2.tag_id = ?)";
    $params[] = $filter_tag;
}
if ($filter_col !== '') {
    $where[] = "EXISTS (SELECT 1 FROM collection_articles ca2 WHERE ca2.article_id = sa.id AND ca2.collection_id = ?)";
    $params[] = $filter_col;
}

$order = $sort_by === 'oldest' ? 'ASC' : 'DESC';
$where_sql = implode(' AND ', $where);

$sql = "SELECT sa.*, 
        GROUP_CONCAT(DISTINCT CONCAT(st.id,':',st.name,':',st.color) SEPARATOR '|') as tags
        FROM saved_articles sa
        LEFT JOIN article_tags at2 ON sa.id = at2.article_id
        LEFT JOIN saved_tags st ON at2.tag_id = st.id
        WHERE $where_sql
        GROUP BY sa.id
        ORDER BY sa.saved_at $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$saved_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($saved_articles);

include 'partials/header.php';
?>

<style>
  .secondary-nav { background: var(--white); border-bottom: 1px solid var(--border); overflow-x: auto; white-space: nowrap; }
  .secondary-nav::-webkit-scrollbar { display: none; }
  .secondary-nav a { display: inline-block; font-size: 12px; color: var(--text); text-decoration: none; padding: 7px 13px; border-right: 1px solid var(--border); transition: background .12s; }
  .secondary-nav a:hover { background: var(--bg); }
  .secondary-nav a.active { color: var(--red); font-weight: 600; }

  .page-body { padding: 28px 0 60px; }
  .section-label { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--red); border-top: 3px solid var(--red); padding-top: 6px; margin-bottom: 16px; display: block; }
  .card-cat { display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; padding: 2px 10px; border-radius: 2px; }
  .source-dot { width: 22px; height: 22px; border-radius: 5px; background: var(--bg); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 8px; font-weight: 700; color: var(--muted); flex-shrink: 0; }
  .source-name { font-size: 12px; font-weight: 600; color: var(--text); }
  .source-time { font-size: 11px; color: var(--muted); }

  /* SAVED CARD */
  .saved-card { background: var(--white); border: 1px solid var(--border); border-radius: 12px; padding: 18px; margin-bottom: 16px; transition: box-shadow 0.2s, transform 0.2s; }
  .saved-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,.05); transform: translateY(-2px); }
  .saved-headline { font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; line-height: 1.3; margin: 10px 0; }
  .saved-headline a { color: var(--text); text-decoration: none; }
  .saved-headline a:hover { color: var(--navy); }
  .saved-summary { font-size: 13px; color: #555; line-height: 1.6; margin-bottom: 12px; }
  .btn-remove-saved { background: none; border: none; color: #1d9e75; font-size: 16px; cursor: pointer; transition: color 0.15s; }
  .btn-remove-saved:hover { color: var(--red); }

  /* TÌM KIẾM & LỌC */
  .filter-bar { background: var(--white); border: 1px solid var(--border); border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; }
  .filter-bar input[type=text] { border: 1px solid var(--border); border-radius: 6px; padding: 6px 12px; font-size: 13px; width: 100%; }
  .filter-bar input[type=text]:focus { outline: none; border-color: var(--navy); }
  .filter-bar select { border: 1px solid var(--border); border-radius: 6px; padding: 6px 10px; font-size: 13px; background: var(--white); cursor: pointer; }
  .filter-bar select:focus { outline: none; border-color: var(--navy); }
  .btn-filter { background: var(--navy); color: #fff; border: none; border-radius: 6px; padding: 6px 16px; font-size: 13px; cursor: pointer; }
  .btn-filter:hover { background: #0f1a33; }
  .btn-reset { background: none; color: var(--muted); border: 1px solid var(--border); border-radius: 6px; padding: 6px 12px; font-size: 13px; cursor: pointer; text-decoration: none; display: inline-block; }

  /* GHI CHÚ */
  .note-area { background: #fffbea; border: 1px dashed #e0c84a; border-radius: 8px; padding: 10px 12px; margin-top: 10px; font-size: 13px; }
  .note-area textarea { width: 100%; border: none; background: transparent; font-size: 13px; resize: none; min-height: 50px; color: #555; }
  .note-area textarea:focus { outline: none; }
  .btn-save-note { background: #f5c518; color: #1a2744; border: none; border-radius: 5px; padding: 4px 12px; font-size: 12px; font-weight: 600; cursor: pointer; }
  .btn-save-note:hover { background: #e0b200; }
  .note-toggle { font-size: 12px; color: var(--muted); cursor: pointer; text-decoration: none; }
  .note-toggle:hover { color: var(--navy); }

  /* NHÃN */
  .tag-badge { display: inline-block; font-size: 11px; padding: 2px 8px; border-radius: 20px; margin-right: 4px; margin-top: 4px; font-weight: 600; color: #fff; }
  .tags-row { margin-top: 8px; }
  .btn-add-tag { font-size: 11px; color: var(--muted); cursor: pointer; border: 1px dashed var(--border); background: none; border-radius: 20px; padding: 2px 8px; }
  .btn-add-tag:hover { border-color: var(--navy); color: var(--navy); }

  /* BỘ SƯU TẬP SIDEBAR */
  .sidebar { border-left: 1px solid var(--border); padding-left: 24px; }
  .sidebar-block { margin-bottom: 28px; }
  .sidebar-heading { font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--navy); border-top: 3px solid var(--navy); padding-top: 6px; margin-bottom: 14px; }
  .collection-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-top: 1px solid var(--border); font-size: 13px; }
  .collection-item:first-child { border-top: none; padding-top: 0; }
  .collection-item a { color: var(--text); text-decoration: none; font-weight: 600; }
  .collection-item a:hover { color: var(--navy); }
  .collection-count { font-size: 11px; color: var(--muted); background: var(--bg); padding: 1px 7px; border-radius: 10px; }
  .btn-new-collection { width: 100%; background: none; border: 1px dashed var(--border); border-radius: 8px; padding: 8px; font-size: 12px; color: var(--muted); cursor: pointer; margin-top: 8px; }
  .btn-new-collection:hover { border-color: var(--navy); color: var(--navy); }

  /* TAG SIDEBAR */
  .tag-filter-item { display: inline-block; margin: 3px; }
  .tag-filter-item a { display: inline-block; font-size: 12px; padding: 3px 10px; border-radius: 20px; color: #fff; font-weight: 600; text-decoration: none; opacity: 0.85; }
  .tag-filter-item a:hover { opacity: 1; }
  .tag-filter-item a.active-tag { outline: 2px solid var(--navy); opacity: 1; }

  /* MODAL */
  .modal-sm-custom { max-width: 400px; }

  /* EMPTY STATE */
  .empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
  .empty-state i { font-size: 3rem; margin-bottom: 16px; display: block; }

  @media (max-width: 768px) {
    .sidebar { border-left: none; padding-left: 0; border-top: 1px solid var(--border); padding-top: 24px; margin-top: 8px; }
  }
</style>

<!-- SECONDARY NAV -->
<div class="secondary-nav">
  <div class="container p-0">
    <a href="saved.php<?= $filter_cat === 'all' ? '?cat=all' : '' ?>" class="<?= $filter_cat === 'all' ? 'active' : '' ?>">Tất cả</a>
    <?php
    $cats = ['tech'=>'Công nghệ','biz'=>'Kinh tế','world'=>'Thế giới','sport'=>'Thể thao','life'=>'Đời sống','edu'=>'Giáo dục'];
    foreach ($cats as $k => $v):
    ?>
    <a href="saved.php?cat=<?= $k ?>" class="<?= $filter_cat === $k ? 'active' : '' ?>"><?= $v ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="page-body">
  <div class="container">
    <div class="row">

      <!-- CỘT CHÍNH -->
      <div class="col-lg-7 col-xl-8 mb-4">

        <!-- TÍNH NĂNG 1: TÌM KIẾM & LỌC & SẮP XẾP -->
        <div class="filter-bar">
          <form method="GET" action="saved.php">
            <input type="hidden" name="cat" value="<?= htmlspecialchars($filter_cat) ?>">
            <div class="d-flex gap-2 flex-wrap align-items-center">
              <input type="text" name="q" placeholder="🔍 Tìm kiếm bài đã lưu..." value="<?= htmlspecialchars($search_kw) ?>" style="flex:1; min-width:180px;">
              <select name="sort">
                <option value="newest" <?= $sort_by === 'newest' ? 'selected' : '' ?>>Mới lưu nhất</option>
                <option value="oldest" <?= $sort_by === 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
              </select>
              <button type="submit" class="btn-filter"><i class="bi bi-search me-1"></i>Lọc</button>
              <?php if ($search_kw || $sort_by !== 'newest' || $filter_tag || $filter_col): ?>
              <a href="saved.php?cat=<?= $filter_cat ?>" class="btn-reset">✕ Xóa lọc</a>
              <?php endif; ?>
            </div>
          </form>
        </div>

        <!-- HEADER DANH SÁCH -->
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="section-label mb-0">Tin tức đã lưu (<?= $total ?>)</span>
          <?php if ($total > 0): ?>
          <a href="#" class="text-muted" style="font-size:12px; text-decoration:none;" onclick="deleteAll(); return false;">
            <i class="bi bi-trash3 me-1"></i>Xóa tất cả
          </a>
          <?php endif; ?>
        </div>

        <!-- DANH SÁCH BÀI LƯU -->
        <div class="saved-list" id="savedList">
          <?php if (empty($saved_articles)): ?>
          <div class="empty-state">
            <i class="bi bi-bookmark-x"></i>
            <p>Chưa có bài nào được lưu<?= $search_kw ? ' phù hợp với "'.htmlspecialchars($search_kw).'"' : '' ?>.</p>
            <a href="index.php" style="font-size:13px; color:var(--navy);">Khám phá tin tức →</a>
          </div>
          <?php else: ?>
          <?php foreach ($saved_articles as $art): ?>
          <?php
            // Màu danh mục
            $cat_styles = [
              'tech'  => 'background:#EEEDFE;color:#534AB7;',
              'biz'   => 'background:#eaf6ec;color:#1d9e75;',
              'world' => 'background:#e8f0fe;color:#1a73e8;',
              'sport' => 'background:#fdecea;color:#c41230;',
              'life'  => 'background:#fff3e0;color:#e65100;',
              'edu'   => 'background:#f3e5f5;color:#7b1fa2;',
            ];
            $cat_style = $cat_styles[$art['category']] ?? 'background:#eee;color:#555;';
            $cat_labels = ['tech'=>'Công nghệ','biz'=>'Kinh tế','world'=>'Thế giới','sport'=>'Thể thao','life'=>'Đời sống','edu'=>'Giáo dục'];
            $cat_label = $cat_labels[$art['category']] ?? $art['category'];

            // Parse tags
            $art_tags = [];
            if ($art['tags']) {
              foreach (explode('|', $art['tags']) as $t) {
                $parts = explode(':', $t);
                if (count($parts) === 3) $art_tags[] = ['id'=>$parts[0],'name'=>$parts[1],'color'=>$parts[2]];
              }
            }
          ?>
          <div class="saved-card" id="card-<?= $art['id'] ?>">
            <div class="d-flex justify-content-between align-items-start">
              <span class="card-cat" style="<?= $cat_style ?>"><?= $cat_label ?></span>
              <div class="d-flex gap-2 align-items-center">
                <!-- Thêm vào bộ sưu tập -->
                <button class="btn-remove-saved" title="Thêm vào bộ sưu tập" onclick="openCollectionModal(<?= $art['id'] ?>)" style="color:#1a73e8; font-size:14px;">
                  <i class="bi bi-folder-plus"></i>
                </button>
                <!-- Xóa bài lưu -->
                <button class="btn-remove-saved" title="Bỏ lưu" onclick="removeArticle(<?= $art['id'] ?>)">
                  <i class="bi bi-bookmark-fill"></i>
                </button>
              </div>
            </div>

            <h3 class="saved-headline">
              <a href="<?= htmlspecialchars($art['article_url'] ?: 'article.php') ?>"><?= htmlspecialchars($art['title']) ?></a>
            </h3>
            <p class="saved-summary"><?= htmlspecialchars($art['summary']) ?></p>

            <div class="d-flex align-items-center gap-2">
              <div class="source-dot"><?= strtoupper(substr($art['source_name'] ?? 'N', 0, 2)) ?></div>
              <div class="source-name"><?= htmlspecialchars($art['source_name'] ?? '') ?></div>
              <div class="source-time ms-2 border-start ps-2">
                <?php
                  $diff = time() - strtotime($art['saved_at']);
                  if ($diff < 3600) echo 'Đã lưu ' . floor($diff/60) . ' phút trước';
                  elseif ($diff < 86400) echo 'Đã lưu ' . floor($diff/3600) . ' giờ trước';
                  else echo 'Đã lưu ' . floor($diff/86400) . ' ngày trước';
                ?>
              </div>
            </div>

            <!-- TÍNH NĂNG 2: NHÃN -->
            <div class="tags-row">
              <?php foreach ($art_tags as $tag): ?>
              <span class="tag-badge" style="background:<?= htmlspecialchars($tag['color']) ?>">
                <?= htmlspecialchars($tag['name']) ?>
                <span style="cursor:pointer; margin-left:4px;" onclick="removeTag(<?= $art['id'] ?>, <?= $tag['id'] ?>)">×</span>
              </span>
              <?php endforeach; ?>
              <button class="btn-add-tag" onclick="openTagModal(<?= $art['id'] ?>)">
                <i class="bi bi-tag me-1"></i>+ Nhãn
              </button>
            </div>

            <!-- TÍNH NĂNG 3: GHI CHÚ -->
            <div class="mt-2">
              <a class="note-toggle" onclick="toggleNote(<?= $art['id'] ?>)">
                <i class="bi bi-pencil-square me-1"></i>
                <?= $art['note'] ? 'Xem/sửa ghi chú' : '+ Thêm ghi chú' ?>
              </a>
              <div class="note-area mt-2" id="note-<?= $art['id'] ?>" style="display:<?= $art['note'] ? 'block' : 'none' ?>">
                <textarea id="note-text-<?= $art['id'] ?>" placeholder="Ghi chú của bạn về bài này..."><?= htmlspecialchars($art['note'] ?? '') ?></textarea>
                <div class="d-flex justify-content-end">
                  <button class="btn-save-note" onclick="saveNote(<?= $art['id'] ?>)">
                    <i class="bi bi-check2 me-1"></i>Lưu ghi chú
                  </button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- SIDEBAR -->
      <div class="col-lg-5 col-xl-4">
        <div class="sidebar">

          <!-- TÍNH NĂNG 2: LỌC THEO NHÃN -->
          <div class="sidebar-block">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="sidebar-heading mb-0">Nhãn của bạn</div>
              <button class="btn-add-tag" onclick="openNewTagModal()"><i class="bi bi-plus"></i> Tạo nhãn</button>
            </div>
            <?php if (empty($tags)): ?>
              <p style="font-size:12px; color:var(--muted);">Chưa có nhãn nào. Tạo nhãn để phân loại bài lưu!</p>
            <?php else: ?>
              <div>
                <?php foreach ($tags as $tag): ?>
                <div class="tag-filter-item">
                  <a href="saved.php?tag=<?= $tag['id'] ?>&cat=<?= $filter_cat ?>"
                     style="background:<?= htmlspecialchars($tag['color']) ?>"
                     class="<?= $filter_tag == $tag['id'] ? 'active-tag' : '' ?>">
                    <?= htmlspecialchars($tag['name']) ?>
                  </a>
                </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- TÍNH NĂNG 4: BỘ SƯU TẬP -->
          <div class="sidebar-block">
            <div class="sidebar-heading">Bộ sưu tập</div>
            <?php if (empty($collections)): ?>
              <p style="font-size:12px; color:var(--muted);">Chưa có bộ sưu tập nào.</p>
            <?php else: ?>
              <?php foreach ($collections as $col): ?>
              <?php
                $cnt_stmt = $pdo->prepare("SELECT COUNT(*) FROM collection_articles WHERE collection_id = ?");
                $cnt_stmt->execute([$col['id']]);
                $col_count = $cnt_stmt->fetchColumn();
              ?>
              <div class="collection-item">
                <a href="saved.php?col=<?= $col['id'] ?>&cat=<?= $filter_cat ?>" class="<?= $filter_col == $col['id'] ? 'fw-bold' : '' ?>">
                  <i class="bi bi-folder me-1"></i><?= htmlspecialchars($col['name']) ?>
                </a>
                <div class="d-flex align-items-center gap-2">
                  <span class="collection-count"><?= $col_count ?></span>
                  <span style="cursor:pointer; color:var(--muted); font-size:12px;" onclick="deleteCollection(<?= $col['id'] ?>)">×</span>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
            <button class="btn-new-collection" onclick="openNewCollectionModal()">
              <i class="bi bi-folder-plus me-1"></i>Tạo bộ sưu tập mới
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: GẮN NHÃN CHO BÀI -->
<div class="modal fade" id="tagModal" tabindex="-1">
  <div class="modal-dialog modal-sm-custom modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Gắn nhãn cho bài viết</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="tag-article-id">
        <?php if (empty($tags)): ?>
          <p class="text-muted" style="font-size:13px;">Bạn chưa có nhãn nào. Hãy tạo nhãn trước!</p>
        <?php else: ?>
          <div id="tag-list-modal">
            <?php foreach ($tags as $tag): ?>
            <div class="form-check mb-2">
              <input class="form-check-input tag-check" type="checkbox" value="<?= $tag['id'] ?>" id="tchk-<?= $tag['id'] ?>">
              <label class="form-check-label" for="tchk-<?= $tag['id'] ?>">
                <span class="tag-badge" style="background:<?= htmlspecialchars($tag['color']) ?>"><?= htmlspecialchars($tag['name']) ?></span>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
        <button type="button" class="btn btn-sm btn-dark" onclick="applyTags()">Áp dụng</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: TẠO NHÃN MỚI -->
<div class="modal fade" id="newTagModal" tabindex="-1">
  <div class="modal-dialog modal-sm-custom modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Tạo nhãn mới</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label style="font-size:13px; font-weight:600;">Tên nhãn</label>
          <input type="text" id="new-tag-name" class="form-control form-control-sm mt-1" placeholder="VD: Quan trọng, Đọc sau...">
        </div>
        <div class="mb-2">
          <label style="font-size:13px; font-weight:600;">Màu nhãn</label>
          <div class="d-flex gap-2 flex-wrap mt-1">
            <?php
            $preset_colors = ['#534AB7','#1d9e75','#c41230','#e65100','#1a73e8','#7b1fa2','#1a2744','#e0b200'];
            foreach ($preset_colors as $c):
            ?>
            <span class="color-dot" style="background:<?= $c ?>; width:24px; height:24px; border-radius:50%; cursor:pointer; display:inline-block; border:2px solid transparent;"
              onclick="selectColor('<?= $c ?>', this)"></span>
            <?php endforeach; ?>
          </div>
          <input type="hidden" id="new-tag-color" value="#534AB7">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-sm btn-dark" onclick="createTag()">Tạo nhãn</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: THÊM VÀO BỘ SƯU TẬP -->
<div class="modal fade" id="collectionModal" tabindex="-1">
  <div class="modal-dialog modal-sm-custom modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Thêm vào bộ sưu tập</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="col-article-id">
        <?php if (empty($collections)): ?>
          <p class="text-muted" style="font-size:13px;">Chưa có bộ sưu tập. Hãy tạo bộ sưu tập trước!</p>
        <?php else: ?>
          <?php foreach ($collections as $col): ?>
          <div class="form-check mb-2">
            <input class="form-check-input col-check" type="checkbox" value="<?= $col['id'] ?>" id="cchk-<?= $col['id'] ?>">
            <label class="form-check-label" for="cchk-<?= $col['id'] ?>">
              <i class="bi bi-folder me-1"></i><?= htmlspecialchars($col['name']) ?>
            </label>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
        <button type="button" class="btn btn-sm btn-dark" onclick="applyCollection()">Thêm vào</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: TẠO BỘ SƯU TẬP MỚI -->
<div class="modal fade" id="newCollectionModal" tabindex="-1">
  <div class="modal-dialog modal-sm-custom modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Tạo bộ sưu tập mới</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="new-col-name" class="form-control form-control-sm" placeholder="VD: Tin quan trọng, Đọc cuối tuần...">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-sm btn-dark" onclick="createCollection()">Tạo</button>
      </div>
    </div>
  </div>
</div>

<script src="scripts/script.js"></script>
<script>
// ===== TÍNH NĂNG 1: XÓA BÀI LƯU =====
function removeArticle(id) {
  if (!confirm('Bỏ lưu bài viết này?')) return;
  fetch('api/saved_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'remove', article_id: id})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.getElementById('card-' + id).remove();
      showToast('Đã bỏ lưu bài viết!');
    }
  });
}

function deleteAll() {
  if (!confirm('Xóa tất cả bài đã lưu?')) return;
  fetch('api/saved_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'remove_all'})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
  });
}

// ===== TÍNH NĂNG 3: GHI CHÚ =====
function toggleNote(id) {
  const el = document.getElementById('note-' + id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function saveNote(id) {
  const note = document.getElementById('note-text-' + id).value;
  fetch('api/saved_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'save_note', article_id: id, note: note})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) showToast('Đã lưu ghi chú!');
  });
}

// ===== TÍNH NĂNG 2: NHÃN =====
let currentTagArticleId = null;

function openTagModal(articleId) {
  currentTagArticleId = articleId;
  document.getElementById('tag-article-id').value = articleId;
  new bootstrap.Modal(document.getElementById('tagModal')).show();
}

function applyTags() {
  const checked = [...document.querySelectorAll('.tag-check:checked')].map(c => c.value);
  fetch('api/saved_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'add_tags', article_id: currentTagArticleId, tag_ids: checked})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) { bootstrap.Modal.getInstance(document.getElementById('tagModal')).hide(); location.reload(); }
  });
}

function removeTag(articleId, tagId) {
  fetch('api/saved_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'remove_tag', article_id: articleId, tag_id: tagId})
  })
  .then(r => r.json())
  .then(data => { if (data.success) location.reload(); });
}

function openNewTagModal() {
  new bootstrap.Modal(document.getElementById('newTagModal')).show();
}

function selectColor(color, el) {
  document.getElementById('new-tag-color').value = color;
  document.querySelectorAll('.color-dot').forEach(d => d.style.border = '2px solid transparent');
  el.style.border = '2px solid #333';
}

function createTag() {
  const name = document.getElementById('new-tag-name').value.trim();
  const color = document.getElementById('new-tag-color').value;
  if (!name) return alert('Vui lòng nhập tên nhãn!');
  fetch('api/saved_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'create_tag', name: name, color: color})
  })
  .then(r => r.json())
  .then(data => { if (data.success) location.reload(); });
}

// ===== TÍNH NĂNG 4: BỘ SƯU TẬP =====
let currentColArticleId = null;

function openCollectionModal(articleId) {
  currentColArticleId = articleId;
  new bootstrap.Modal(document.getElementById('collectionModal')).show();
}

function applyCollection() {
  const checked = [...document.querySelectorAll('.col-check:checked')].map(c => c.value);
  if (checked.length === 0) return alert('Chọn ít nhất 1 bộ sưu tập!');
  fetch('api/saved_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'add_to_collection', article_id: currentColArticleId, collection_ids: checked})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) { bootstrap.Modal.getInstance(document.getElementById('collectionModal')).hide(); showToast('Đã thêm vào bộ sưu tập!'); }
  });
}

function openNewCollectionModal() {
  new bootstrap.Modal(document.getElementById('newCollectionModal')).show();
}

function createCollection() {
  const name = document.getElementById('new-col-name').value.trim();
  if (!name) return alert('Vui lòng nhập tên bộ sưu tập!');
  fetch('api/saved_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'create_collection', name: name})
  })
  .then(r => r.json())
  .then(data => { if (data.success) location.reload(); });
}

function deleteCollection(id) {
  if (!confirm('Xóa bộ sưu tập này?')) return;
  fetch('api/saved_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'delete_collection', collection_id: id})
  })
  .then(r => r.json())
  .then(data => { if (data.success) location.reload(); });
}

// TOAST THÔNG BÁO
function showToast(msg) {
  let t = document.createElement('div');
  t.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1a2744;color:#fff;padding:10px 20px;border-radius:8px;font-size:13px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.2);';
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2500);
}
</script>

<?php include 'partials/footer.php'; ?>
