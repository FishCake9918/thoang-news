<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="flex-grow-1 d-flex align-items-center justify-content-center py-5 px-3">
    <div class="auth-card text-center" style="max-width: 500px;">
        <div class="auth-title mb-3">Xác thực tài khoản</div>
        <div class="alert <?= htmlspecialchars($status_class) ?> py-3 px-4" style="font-size:14px; border-radius:4px;">
            <?= htmlspecialchars($message) ?>
        </div>
        <div class="mt-4">
            <a href="<?= route('login') ?>" class="btn-login text-decoration-none d-inline-block">Đăng nhập ngay</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
