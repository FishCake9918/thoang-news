<?php
// ============================================================
// dashboard_writer.php — Không gian làm việc của Tác giả (Khung xương)
// ============================================================
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

// KIỂM TRA PHÂN QUYỀN: Chỉ cho phép tài khoản có quyền writer hoặc admin vào trang này
if (!isLoggedIn() || ($_SESSION['role'] !== 'writer' && $_SESSION['role'] !== 'admin')) {
    header('Location: login.php');
    exit;
}

$author_id = $_SESSION['user_id']; // Dùng biến này để lọc bài viết của chính tác giả này

$page_title = 'Không gian viết bài — Thoáng.vn';
include 'partials/header.php';
?>

<div class="page-body">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 style="font-family: 'Playfair Display', serif; font-weight: 700; color: var(--navy);">Quản lý bài viết tác giả</h3>
      
      <a href="vietbai.php" class="btn btn-sm" style="background: var(--navy); color: #fff; font-weight: 600; padding: 8px 16px;">
        <i class="bi bi-pencil-square me-1"></i> Sáng tác bài mới
      </a>
    </div>

    <div class="card shadow-sm border-0 p-4 bg-white">
      <span class="section-label">Danh sách tác phẩm của bạn</span>
      <p class="text-muted" style="font-size: 13.5px;">[Trí viết code dùng câu truy vấn SELECT bài viết WHERE author_id = $author_id; hiển thị Table kèm cột status ('request', 'approved', 'disapproved') tại đây]</p>
      
      </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>