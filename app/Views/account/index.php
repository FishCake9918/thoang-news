<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="page-body">
  <div class="container">
    <div class="account-layout">
      <section class="account-panel account-summary">
        <img class="account-avatar-xl" src="<?= htmlspecialchars($user['avatar'] ?: 'images/avatars/avatar-01.svg') ?>" alt="Avatar">
        <div>
          <div class="account-eyebrow"><?= htmlspecialchars(ucfirst($user['role'])) ?></div>
          <h1 class="account-title"><?= htmlspecialchars($user['username']) ?></h1>
          <p class="account-muted"><?= htmlspecialchars($user['email']) ?></p>
        </div>
      </section>

      <div class="account-content">
        <?php if ($error !== ''): ?>
          <div class="account-alert account-alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($message !== ''): ?>
          <div class="account-alert account-alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <section class="account-panel">
          <div class="account-section-head">
            <div>
              <div class="account-eyebrow">Thông tin</div>
              <h2>Đổi username, email và avatar</h2>
            </div>
          </div>

          <form method="POST" class="account-form">
            <input type="hidden" name="action" value="account">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" id="email" name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" required>
              </div>
            </div>

            <div class="avatar-grid" role="radiogroup" aria-label="Chọn avatar">
              <?php foreach ($avatars as $avatar): ?>
                <label class="avatar-option">
                  <input type="radio" name="avatar" value="<?= htmlspecialchars($avatar) ?>" <?= ($user['avatar'] ?: 'images/avatars/avatar-01.svg') === $avatar ? 'checked' : '' ?>>
                  <span><img src="<?= htmlspecialchars($avatar) ?>" alt=""></span>
                </label>
              <?php endforeach; ?>
            </div>

            <button class="btn-submit" type="submit">Lưu thông tin</button>
          </form>
        </section>

        <section class="account-panel">
          <div class="account-section-head">
            <div>
              <div class="account-eyebrow">Bảo mật</div>
              <h2>Đổi mật khẩu</h2>
            </div>
          </div>

          <form method="POST" class="account-form">
            <input type="hidden" name="action" value="password">

            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label" for="current_password">Mật khẩu hiện tại</label>
                <input class="form-control" id="current_password" name="current_password" type="password" required>
              </div>
              <div class="col-md-4">
                <label class="form-label" for="new_password">Mật khẩu mới</label>
                <input class="form-control" id="new_password" name="new_password" type="password" minlength="6" required>
              </div>
              <div class="col-md-4">
                <label class="form-label" for="confirm_password">Xác nhận mật khẩu</label>
                <input class="form-control" id="confirm_password" name="confirm_password" type="password" minlength="6" required>
              </div>
            </div>

            <button class="btn-submit" type="submit">Đổi mật khẩu</button>
          </form>
        </section>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
