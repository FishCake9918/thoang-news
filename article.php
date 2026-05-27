<?php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$comment_error = '';
$comments = [];
$nextArticle = null;

function is_ajax_request(): bool {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function comment_display_name(array $comment): string {
    return trim($comment['full_name'] ?? '') ?: ($comment['username'] ?? 'Người dùng');
}

function render_comment_item(array $comment): string {
    $commentName = comment_display_name($comment);
    $commentInitial = function_exists('mb_substr')
        ? mb_substr($commentName, 0, 1, 'UTF-8')
        : substr($commentName, 0, 1);

    $canDeleteComment = isLoggedIn() && (
        ($_SESSION['role'] ?? '') === 'admin' ||
        (int)($comment['user_id'] ?? 0) === (int)($_SESSION['user_id'] ?? 0)
    );

    ob_start();
    ?>
    <article class="comment-item" id="comment-<?= (int)$comment['id'] ?>">
      <div class="comment-avatar"><?= htmlspecialchars(strtoupper($commentInitial)) ?></div>
      <div class="comment-main">
        <div class="comment-meta">
          <strong><?= htmlspecialchars($commentName) ?></strong>
          <span><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>

          <?php if ($canDeleteComment): ?>
            <form method="POST" class="delete-comment-form" style="display:inline;">
              <input type="hidden" name="action" value="delete_comment">
              <input type="hidden" name="comment_id" value="<?= (int)$comment['id'] ?>">
              <button type="submit" class="btn-delete-comment">Xoá</button>
            </form>
          <?php endif; ?>
        </div>
        <div class="comment-content">
          <?= nl2br(htmlspecialchars($comment['content'])) ?>
        </div>
      </div>
    </article>
    <?php
    return trim(ob_get_clean());
}

function send_json(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($id <= 0) {
    if (is_ajax_request()) {
        send_json(['success' => false, 'message' => 'Bài viết không hợp lệ.'], 400);
    }
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT a.*, c.name AS category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        if (is_ajax_request()) {
            send_json(['success' => false, 'message' => 'Không tìm thấy bài viết.'], 404);
        }
        header('Location: index.php');
        exit;
    }

    $canPreview = isLoggedIn() && (
        ($_SESSION['role'] ?? '') === 'admin' ||
        (
            ($_SESSION['role'] ?? '') === 'writer' &&
            (int)($article['author_id'] ?? 0) === (int)($_SESSION['user_id'] ?? 0)
        )
    );

    if ($article['status'] !== 'Approved' && !$canPreview) {
        if (is_ajax_request()) {
            send_json(['success' => false, 'message' => 'Bạn không có quyền xem bài viết này.'], 403);
        }
        header('Location: index.php');
        exit;
    }

    if ($article['status'] === 'Approved') {
        // Xử lý logic thêm/xoá bình luận
        require_once 'controllers/ArticleController.php';

        $updateView = $pdo->prepare("
            UPDATE articles
            SET view_count = view_count + 1
            WHERE id = ?
        ");
        $updateView->execute([$id]);

        $article['view_count'] = (int)$article['view_count'] + 1;

        $commentsStmt = $pdo->prepare("
            SELECT c.*, u.username, u.full_name
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            WHERE c.article_id = ?
            ORDER BY c.created_at DESC, c.id DESC
        ");
        $commentsStmt->execute([$id]);
        $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

        $nextStmt = $pdo->prepare("
            SELECT id
            FROM articles
            WHERE status = 'Approved' AND id > ?
            ORDER BY id ASC
            LIMIT 1
        ");
        $nextStmt->execute([$id]);
        $nextArticle = $nextStmt->fetch(PDO::FETCH_ASSOC);

        if (!$nextArticle) {
            $firstStmt = $pdo->prepare("
                SELECT id
                FROM articles
                WHERE status = 'Approved' AND id <> ?
                ORDER BY id ASC
                LIMIT 1
            ");
            $firstStmt->execute([$id]);
            $nextArticle = $firstStmt->fetch(PDO::FETCH_ASSOC);
        }
    }

} catch (PDOException $e) {
    if (is_ajax_request()) {
        send_json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
    }
    die("Lỗi kết nối hoặc xử lý dữ liệu hệ thống: " . $e->getMessage());
}

$published_time = !empty($article['published_at']) ? $article['published_at'] : $article['created_at'];
$page_title = htmlspecialchars($article['title']) . ' — Thoáng.vn';

$back_url = 'index.php';
if (($_GET['from'] ?? '') === 'dashboard') {
    $back_url = 'dashboard.php';
}

include 'partials/header.php';
?>

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
              <span><?= date('d/m/Y H:i', strtotime($published_time)) ?></span>
            </div>

            <div class="meta-item border-start ps-3">
              <i class="bi bi-eye-fill"></i>
              <span><?= number_format($article['view_count']) ?> lượt xem</span>
            </div>
          </div>

          <?php if ($article['status'] === 'Approved'): ?>
            <div class="article-reading-wrap">
              <div class="article-reading-bar">
                <div id="articleReadingProgress"></div>
              </div>

              <div class="article-reading-text">
                Tự động chuyển bài sau <span id="readingCountdown">30</span> giây
              </div>
            </div>

            <?php if ($nextArticle): ?>
              <a id="nextArticleAuto" href="article.php?id=<?= (int)$nextArticle['id'] ?>" style="display:none;"></a>
            <?php endif; ?>
          <?php endif; ?>

          <?php if (!empty($article['image_url'])): ?>
            <figure class="article-image-wrap">
              <img
                class="article-image"
                src="<?= htmlspecialchars($article['image_url']) ?>"
                alt="<?= htmlspecialchars($article['title']) ?>"
                loading="lazy"
                onerror="this.closest('.article-image-wrap').classList.add('is-broken')"
              >
              <figcaption>Không thể hiển thị hình ảnh bài viết.</figcaption>
            </figure>
          <?php endif; ?>

          <div class="article-summary">
            <?= nl2br(htmlspecialchars($article['summary'])) ?>
          </div>

          <div class="article-content">
            <?= nl2br($article['content']) ?>
          </div>

          <?php if ($article['status'] === 'Approved'): ?>
            <section class="article-comments" id="comments">
              <div class="comments-head">
                <div>
                  <div class="comments-eyebrow">Bình luận</div>
                  <h2><span id="comment-count"><?= count($comments) ?></span> bình luận</h2>
                </div>
              </div>

              <?php if (isLoggedIn()): ?>
                <form method="POST" class="comment-form" id="comment-form">
                  <input type="hidden" name="action" value="add_comment">
                  <div class="comment-error" id="comment-error" style="<?= $comment_error !== '' ? '' : 'display:none;' ?>">
                    <?= htmlspecialchars($comment_error) ?>
                  </div>

                  <textarea name="comment_content" rows="4" maxlength="1000" placeholder="Viết bình luận của bạn..." required><?= htmlspecialchars($_POST['comment_content'] ?? '') ?></textarea>

                  <div class="comment-form-actions">
                    <span>Đăng với tên <?= htmlspecialchars(($_SESSION['full_name'] ?? '') ?: ($_SESSION['username'] ?? 'người dùng')) ?></span>
                    <button type="submit" id="comment-submit-btn">Gửi bình luận</button>
                  </div>
                </form>
              <?php else: ?>
                <div class="comment-login-note">
                  Vui lòng <a href="login.php">đăng nhập</a> để bình luận về bài viết này.
                </div>
              <?php endif; ?>

              <div class="comment-list" id="comment-list">
                <?php if (empty($comments)): ?>
                  <div class="comment-empty" id="comment-empty">Chưa có bình luận nào. Hãy là người đầu tiên chia sẻ ý kiến.</div>
                <?php else: ?>
                  <?php foreach ($comments as $comment): ?>
                    <?= render_comment_item($comment) ?>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </section>
          <?php endif; ?>

          <div class="border-top mt-5">
            <a href="<?= htmlspecialchars($back_url) ?>" class="btn-back">
              <i class="bi bi-arrow-left"></i> Quay lại trang trước
            </a>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const commentForm = document.getElementById('comment-form');
  const commentList = document.getElementById('comment-list');
  const commentCount = document.getElementById('comment-count');
  const commentError = document.getElementById('comment-error');
  const submitBtn = document.getElementById('comment-submit-btn');

  function showError(message) {
    if (!commentError) return;
    commentError.textContent = message;
    commentError.style.display = 'block';
  }

  function hideError() {
    if (!commentError) return;
    commentError.textContent = '';
    commentError.style.display = 'none';
  }

  function updateEmptyState() {
    if (!commentList) return;
    const hasComment = commentList.querySelector('.comment-item');
    let emptyBox = document.getElementById('comment-empty');

    if (hasComment && emptyBox) {
      emptyBox.remove();
    }

    if (!hasComment && !emptyBox) {
      commentList.innerHTML = '<div class="comment-empty" id="comment-empty">Chưa có bình luận nào. Hãy là người đầu tiên chia sẻ ý kiến.</div>';
    }
  }

  if (commentForm) {
    commentForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      hideError();

      const formData = new FormData(commentForm);
      const textarea = commentForm.querySelector('textarea[name="comment_content"]');
      const content = textarea ? textarea.value.trim() : '';

      if (!content) {
        showError('Vui lòng nhập nội dung bình luận.');
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang gửi...';
      }

      try {
        const response = await fetch(window.location.href, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const data = await response.json();

        if (!data.success) {
          showError(data.message || 'Không thể gửi bình luận.');
          return;
        }

        const emptyBox = document.getElementById('comment-empty');
        if (emptyBox) emptyBox.remove();

        if (data.html && commentList) {
          commentList.insertAdjacentHTML('afterbegin', data.html);
        }

        if (commentCount && typeof data.count !== 'undefined') {
          commentCount.textContent = data.count;
        }

        commentForm.reset();
        updateEmptyState();
      } catch (error) {
        showError('Có lỗi xảy ra, vui lòng thử lại.');
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Gửi bình luận';
        }
      }
    });
  }

  if (commentList) {
    commentList.addEventListener('submit', async function (e) {
      const deleteForm = e.target.closest('.delete-comment-form');
      if (!deleteForm) return;

      e.preventDefault();

      if (!confirm('Bạn có chắc muốn xoá bình luận này không?')) {
        return;
      }

      const formData = new FormData(deleteForm);
      const commentItem = deleteForm.closest('.comment-item');
      const deleteBtn = deleteForm.querySelector('button[type="submit"]');

      if (deleteBtn) {
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Đang xoá...';
      }

      try {
        const response = await fetch(window.location.href, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const data = await response.json();

        if (!data.success) {
          alert(data.message || 'Không thể xoá bình luận.');
          return;
        }

        if (commentItem) {
          commentItem.remove();
        }

        if (commentCount && typeof data.count !== 'undefined') {
          commentCount.textContent = data.count;
        }

        updateEmptyState();
      } catch (error) {
        alert('Có lỗi xảy ra, vui lòng thử lại.');
      } finally {
        if (deleteBtn) {
          deleteBtn.disabled = false;
          deleteBtn.textContent = 'Xoá';
        }
      }
    });
  }
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const progress = document.getElementById("articleReadingProgress");
  if (!progress) return;

  const countdownText = document.getElementById("readingCountdown");
  const nextArticle = document.getElementById("nextArticleAuto");
  const articleCard = document.querySelector(".article-card");

  const duration = 30;
  const startTime = Date.now();

  progress.style.width = "0%";

  const timer = setInterval(function () {
    const elapsed = (Date.now() - startTime) / 1000;
    const percent = Math.min((elapsed / duration) * 100, 100);

    progress.style.width = percent + "%";

    if (countdownText) {
      countdownText.textContent = Math.max(Math.ceil(duration - elapsed), 0);
    }

    if (elapsed >= duration) {
      clearInterval(timer);

      if (countdownText) {
        countdownText.textContent = "Đang chuyển bài...";
      }

      if (articleCard) {
        articleCard.classList.add("auto-next-out");
      }

      setTimeout(function () {
        if (nextArticle) {
          window.location.href = nextArticle.href;
        } else {
          window.location.href = "index.php";
        }
      }, 650);
    }
  }, 50);
});
</script>
<?php include 'partials/footer.php'; ?>
