<?php
use App\Views\Components\CommentComponent;

include __DIR__ . '/../partials/header.php';
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

            <div class="article-nav-actions">
              <?php if ($prevArticle): ?>
                <a class="article-nav-btn" id="prevArticleLink" href="article.php?id=<?= (int)$prevArticle['id'] ?>">
                  <i class="bi bi-arrow-left"></i> Bài trước
                </a>
              <?php endif; ?>

              <?php if ($nextArticle): ?>
                <a class="article-nav-btn article-nav-btn-primary" id="nextArticleLink" href="article.php?id=<?= (int)$nextArticle['id'] ?>">
                  Bài tiếp <i class="bi bi-arrow-right"></i>
                </a>
              <?php endif; ?>
            </div>
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
                    <?= CommentComponent::render($comment) ?>
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
  const prevArticleLink = document.getElementById("prevArticleLink");
  const nextArticleLink = document.getElementById("nextArticleLink");
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

  document.addEventListener("keydown", function (e) {
    if (e.target && ["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName)) return;

    if (e.key === "ArrowLeft" && prevArticleLink) {
      window.location.href = prevArticleLink.href;
    }

    if (e.key === "ArrowRight" && nextArticleLink) {
      window.location.href = nextArticleLink.href;
    }
  });

  let articleTouchStartX = 0;

  document.addEventListener("touchstart", function (e) {
    articleTouchStartX = e.touches[0].clientX;
  }, { passive: true });

  document.addEventListener("touchend", function (e) {
    const endX = e.changedTouches[0].clientX;
    const diff = endX - articleTouchStartX;

    if (Math.abs(diff) < 90) return;

    if (diff > 0 && prevArticleLink) {
      window.location.href = prevArticleLink.href;
    }

    if (diff < 0 && nextArticleLink) {
      window.location.href = nextArticleLink.href;
    }
  }, { passive: true });
});
<script src="scripts/script.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
