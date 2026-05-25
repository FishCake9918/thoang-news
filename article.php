<?php
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$comment_error = '';
$comments = [];

if ($id <= 0) {
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
        header('Location: index.php');
        exit;
    }

    if ($article['status'] === 'Approved') {
        // Xử lý xoá bình luận
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_comment') {
            if (!isLoggedIn()) {
                header('Location: login.php');
                exit;
            }

            $comment_id = (int)($_POST['comment_id'] ?? 0);

            if ($comment_id > 0) {
                // Admin được xoá mọi bình luận.
                // Người dùng thường chỉ được xoá bình luận của chính mình.
                if (($_SESSION['role'] ?? '') === 'admin') {
                    $deleteStmt = $pdo->prepare("
                        DELETE FROM comments
                        WHERE id = ? AND article_id = ?
                    ");
                    $deleteStmt->execute([$comment_id, $id]);
                } else {
                    $deleteStmt = $pdo->prepare("
                        DELETE FROM comments
                        WHERE id = ? AND article_id = ? AND user_id = ?
                    ");
                    $deleteStmt->execute([
                        $comment_id,
                        $id,
                        (int)($_SESSION['user_id'] ?? 0)
                    ]);
                }
            }

            header('Location: article.php?id=' . $id . '#comments');
            exit;
        }

        // Xử lý thêm bình luận
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_comment') {
            if (!isLoggedIn()) {
                header('Location: login.php');
                exit;
            }

            $comment_content = trim($_POST['comment_content'] ?? '');
            if ($comment_content === '') {
                $comment_error = 'Vui lòng nhập nội dung bình luận.';
            } elseif (strlen($comment_content) > 1000) {
                $comment_error = 'Bình luận không được vượt quá 1000 ký tự.';
            } else {
                $commentStmt = $pdo->prepare("
                    INSERT INTO comments (article_id, user_id, content, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $commentStmt->execute([$id, (int)$_SESSION['user_id'], $comment_content]);

                header('Location: article.php?id=' . $id . '#comments');
                exit;
            }
        }

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
    }

} catch (PDOException $e) {
    die("Lỗi kết nối hoặc xử lý dữ liệu hệ thống: " . $e->getMessage());
}

$published_time = !empty($article['published_at']) ? $article['published_at'] : $article['created_at'];
$page_title = htmlspecialchars($article['title']) . ' — Thoáng.vn';

include 'partials/header.php';
?>

<style>
  .delete-comment-form {
    display: inline-block;
    margin-left: 10px;
  }

  .btn-delete-comment {
    border: none;
    background: transparent;
    color: #dc2626;
    font-size: 13px;
    cursor: pointer;
    padding: 0;
  }

  .btn-delete-comment:hover {
    text-decoration: underline;
  }
</style>

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
                  <h2><?= count($comments) ?> bình luận</h2>
                </div>
              </div>

              <?php if (isLoggedIn()): ?>
                <form method="POST" class="comment-form">
                  <input type="hidden" name="action" value="add_comment">
                  <?php if ($comment_error !== ''): ?>
                    <div class="comment-error"><?= htmlspecialchars($comment_error) ?></div>
                  <?php endif; ?>
                  <textarea name="comment_content" rows="4" maxlength="1000" placeholder="Viết bình luận của bạn..." required><?= htmlspecialchars($_POST['comment_content'] ?? '') ?></textarea>
                  <div class="comment-form-actions">
                    <span>Đăng với tên <?= htmlspecialchars(($_SESSION['full_name'] ?? '') ?: ($_SESSION['username'] ?? 'người dùng')) ?></span>
                    <button type="submit">Gửi bình luận</button>
                  </div>
                </form>
              <?php else: ?>
                <div class="comment-login-note">
                  Vui lòng <a href="login.php">đăng nhập</a> để bình luận về bài viết này.
                </div>
              <?php endif; ?>

              <div class="comment-list">
                <?php if (empty($comments)): ?>
                  <div class="comment-empty">Chưa có bình luận nào. Hãy là người đầu tiên chia sẻ ý kiến.</div>
                <?php else: ?>
                  <?php foreach ($comments as $comment): ?>
                    <?php
                      $commentName = trim($comment['full_name'] ?? '') ?: ($comment['username'] ?? 'Người dùng');
                      $commentInitial = function_exists('mb_substr')
                          ? mb_substr($commentName, 0, 1, 'UTF-8')
                          : substr($commentName, 0, 1);
                    ?>
                    <article class="comment-item">
                      <div class="comment-avatar"><?= htmlspecialchars(strtoupper($commentInitial)) ?></div>
                      <div class="comment-main">
                        <div class="comment-meta">
                          <strong><?= htmlspecialchars($commentName) ?></strong>
                          <span><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>

                          <?php
                            $canDeleteComment = isLoggedIn() && (
                                ($_SESSION['role'] ?? '') === 'admin' ||
                                (int)($comment['user_id'] ?? 0) === (int)($_SESSION['user_id'] ?? 0)
                            );
                          ?>

                          <?php if ($canDeleteComment): ?>
                            <form method="POST" class="delete-comment-form" onsubmit="return confirm('Bạn có chắc muốn xoá bình luận này không?');">
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
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </section>
          <?php endif; ?>
          
          <div class="border-top mt-5">
            <a href="javascript:history.back()" class="btn-back">
              <i class="bi bi-arrow-left"></i> Quay lại trang trước
            </a>
          </div>

        </div></div>
    </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>
