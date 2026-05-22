<?php
// ============================================================
// dashboard.php — Bảng điều khiển tối cao của Admin (Khung xương)
// ============================================================
session_start();
require_once 'config/db.php';
require_once 'config/session.php';

// KIỂM TRA PHÂN QUYỀN: Nếu chưa đăng nhập hoặc không phải admin, đá về trang login
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$page_title = 'Bảng quản trị Admin — Thoáng.vn';
include 'partials/header.php';
?>

<div class="page-body">
  <div class="container-fluid px-4">
    <h2 class="mb-4" style="font-family: 'Playfair Display', serif; font-weight: 700; color: var(--navy);">Bảng quản trị hệ thống (Admin)</h2>
    
    <div class="row">
      
      <div class="col-xl-5 mb-4">
        <div class="card shadow-sm border-0 p-4 bg-white">
          <span class="section-label">Quản lý Thành viên</span>
          <p class="text-muted" style="font-size: 13px;">[Nghĩa viết code liệt kê user, sửa role, xóa user tại đây]</p>
          
          </div>

        <div class="card shadow-sm border-0 p-4 bg-white mt-4">
          <span class="section-label">Hộp thư góp ý phản hồi</span>
          <p class="text-muted" style="font-size: 13px;">[Nghĩa viết code hiển thị danh sách phản hồi, nút duyệt xử lý xong/xóa tại đây]</p>
          
          </div>
      </div>

      <div class="col-xl-7 mb-4">
        <div class="card shadow-sm border-0 p-4 bg-white">
          <span class="section-label">Phê duyệt & Kiểm soát bài viết</span>
          <p class="text-muted" style="font-size: 13px;">[Nghĩa viết code hiển thị danh sách bài viết trạng thái 'request', nút Duyệt (approved) / Từ chối (disapproved) tại đây]</p>
          
          </div>
      </div>

    </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>