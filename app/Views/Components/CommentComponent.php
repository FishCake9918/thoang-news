<?php

namespace App\Views\Components;

use App\Core\Auth;

class CommentComponent
{
    public static function displayName(array $comment): string
    {
        return trim($comment['full_name'] ?? '') ?: ($comment['username'] ?? 'Người dùng');
    }

    public static function render(array $comment): string
    {
        $commentName = self::displayName($comment);
        $commentInitial = function_exists('mb_substr')
            ? mb_substr($commentName, 0, 1, 'UTF-8')
            : substr($commentName, 0, 1);

        $canDeleteComment = Auth::check() && (
            Auth::isAdmin() ||
            (int)($comment['user_id'] ?? 0) === Auth::id()
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
                  <button type="submit" class="btn-delete-comment">Xóa</button>
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
}
