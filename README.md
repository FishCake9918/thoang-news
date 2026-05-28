# Thoáng.vn

Thoáng.vn là ứng dụng web đọc tin tức được xây dựng bằng PHP thuần trên XAMPP/MySQL. Dự án tập trung vào trải nghiệm đọc nhanh, lưu bài viết, quản lý bài viết cho tác giả và quản trị nội dung cho admin.

## Tính năng chính

- Đọc tin theo danh mục và xem chi tiết bài viết.
- Lưu/xóa bài viết đã quan tâm.
- Đăng ký, đăng nhập, xác thực tài khoản và khôi phục mật khẩu.
- Tác giả có thể viết bài, chỉnh sửa bài và gửi duyệt.
- Admin quản lý người dùng, bài viết, bình luận, phản hồi và nội dung trang giới thiệu.
- Một số thao tác dùng AJAX/Webservice và truyền dữ liệu JSON.

## Minh Chứng Yêu Cầu Môn Học

### 1. Bootstrap cho phần thiết kế

Project dùng Bootstrap 5 và Bootstrap Icons trong các view:

- `app/Views/partials/header.php`
- `app/Views/auth/login.php`
- `app/Views/dashboard/admin.php`
- `app/Views/dashboard/writer.php`
- `app/Views/writer/form.php`
- `app/Views/about/index.php`

Các thành phần giao diện dùng class Bootstrap như `container`, `row`, `col-*`, `btn`, `card`, `alert`, `table`, `nav`, `modal`, `form-control`.

### 2. Backend hướng đối tượng

Backend đã được tách thành class:

- `app/Core`: lớp lõi như `Controller`, `Model`, `View`, `Auth`, `Request`, `Response`.
- `app/Models`: lớp truy vấn dữ liệu như `ArticleModel`, `UserModel`, `BookmarkModel`, `FeedbackModel`.
- `app/Controllers`: lớp xử lý request như `AuthController`, `ArticleController`, `AdminController`, `SavedController`.

### 3. OOP + MVC

Luồng xử lý chính:

```text
Entry point ở root hoặc api
-> Controller
-> Model
-> View hoặc JSON Response
```

Ví dụ:

- `login.php` gọi `AuthController`, sau đó render `app/Views/auth/login.php`.
- `saved.php` gọi `SavedController`, sau đó render `app/Views/saved/index.php`.
- `article.php` gọi `ArticlePageController` và `ArticleController`, sau đó render `app/Views/articles/show.php`.
- `dashboard.php` gọi `AdminController` và `AdminDashboardController`, sau đó render `app/Views/dashboard/admin.php`.

Các file `.php` ở thư mục gốc chỉ đóng vai trò entry point để giữ URL chạy trên XAMPP. HTML giao diện nằm trong `app/Views`.

### 4. AJAX/Webservice JSON

Frontend dùng `fetch()` để gọi các endpoint trong thư mục `api/`. Các endpoint này trả JSON thông qua Controller API.

Ví dụ:

- `api/get_news.php` gọi `FeedApiController`.
- `api/toggle_bookmark.php` và `api/saved_actions.php` gọi `BookmarkApiController`.
- `api/add_article.php` và `api/upload_article_image.php` gọi `WriterArticleApiController`.
- `api/contact_submit.php` và `api/feedback_action.php` gọi `FeedbackApiController`.
- `api/about_save.php` gọi `AboutApiController`.

## Cấu Trúc Thư Mục

```text
thoang-news/
├── app/
│   ├── Controllers/
│   ├── Core/
│   ├── Models/
│   └── Views/
├── api/
├── config/
├── images/
├── scripts/
├── stylesheets/
├── uploads/
├── index.php
├── login.php
├── article.php
├── saved.php
└── dashboard.php
```

## Cài Đặt Nhanh

1. Đặt project trong `C:\xampp\htdocs\thoang-news`.
2. Import `database.sql` vào MySQL.
3. Kiểm tra cấu hình database trong `config/db.php`.
4. Chạy XAMPP Apache và MySQL.
5. Mở `http://localhost/thoang-news/index.php`.

## Thành Viên

GROUP 6

| STT | Họ và tên | Link |
| --- | --- | --- |
| 1 | Nguyễn Khánh Hoàng | https://github.com/FishCake9918 |
| 2 | Lê Hoàng Việt | https://github.com/LeHoangViet905 |
| 3 | Nguyễn Kiều Minh Trí | https://github.com/MinhTri1701/Minhtri.git |
| 4 | Hứa Đức Nghĩa | https://github.com/nghia2122005-cmyk |