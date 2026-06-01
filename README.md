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

Dự án áp dụng mô hình kiến trúc MVC (Model-View-Controller) thông qua một Front Controller duy nhất ở cổng vào là `index.php`:

Luồng xử lý chính:
```text
Request (URL) 
-> index.php (Front Controller / Routing)
-> Controller tương ứng
-> Model (Truy vấn dữ liệu từ Database)
-> View (Render giao diện HTML trả về client) hoặc API JSON Response
```

Ví dụ:
- URL `/login` gọi `AuthController`, sau đó render `app/Views/auth/login.php`.
- URL `/saved` gọi `SavedController`, sau đó render `app/Views/saved/index.php`.
- URL `/article?id=...` gọi `ArticlePageController` và `ArticleController`, sau đó render `app/Views/articles/show.php`.
- URL `/dashboard` gọi `AdminController` và `AdminDashboardController`, sau đó render `app/Views/dashboard/admin.php`.

### 4. AJAX/Webservice JSON

Frontend sử dụng hàm `fetch()` để gọi các API endpoints trong thư mục `api/`. Các endpoint này trả dữ liệu định dạng JSON để xử lý không cần tải lại trang.

Ví dụ:
- `api/get_news.php` gọi `FeedApiController`.
- `api/toggle_bookmark.php` và `api/saved_actions.php` gọi `BookmarkApiController`.
- `api/add_article.php` và `api/upload_article_image.php` gọi `WriterArticleApiController`.
- `api/contact_submit.php` và `api/feedback_action.php` gọi `FeedbackApiController`.
- `api/about_save.php` gọi `AboutApiController`.

## Cấu Trúc Thư Mục

```text
thoang-news/
├── api/                  # Các file API endpoints trả về JSON (AJAX)
├── app/                  # Thư mục mã nguồn chính áp dụng mô hình MVC
│   ├── Controllers/      # Các Controller xử lý logic nghiệp vụ
│   ├── Core/             # Lớp lõi hệ thống (Router, View, Model, Request, Response, Auth)
│   ├── Models/           # Các Model thực hiện truy vấn cơ sở dữ liệu
│   └── Views/            # Các file giao diện (HTML/CSS/JS bổ trợ)
├── config/               # Cấu hình dự án (db.php, mailer.php, session.php)
├── Diagrams/             # Biểu đồ phân tích và thiết kế hệ thống
├── images/               # Chứa hình ảnh giao diện tĩnh
├── libs/                 # Thư viện ngoài tích hợp (PHPMailer)
├── scripts/              # Các file mã nguồn Javascript
├── stylesheets/          # Các file mã nguồn CSS
├── uploads/              # Nơi chứa các tệp tải lên (hình ảnh bài viết)
├── .gitignore            # Danh sách loại trừ file của Git
├── .htaccess             # File cấu hình máy chủ Apache phục vụ URL thân thiện
├── ARCHITECTURE.md       # Tài liệu mô tả kiến trúc dự án chi tiết
├── index.php             # Front Controller nhận và điều hướng toàn bộ luồng yêu cầu
├── README.md             # Tài liệu giới thiệu và hướng dẫn cài đặt dự án
├── router.php            # File định tuyến bổ trợ chạy PHP Built-in Server
└── thoang_vn.sql         # File backup cơ sở dữ liệu MySQL của dự án
```

## Cài Đặt Nhanh

### Với Apache (XAMPP)

1. Đặt project trong thư mục `C:\xampp\htdocs\thoang-news-main`.
2. Tạo cơ sở dữ liệu mới mang tên `thoang_vn` trong phpMyAdmin và import file [thoang_vn.sql](file:///c:/xampp/htdocs/thoang-news-main/thoang_vn.sql) vào.
3. Kiểm tra cấu hình database trong [config/db.php](file:///c:/xampp/htdocs/thoang-news-main/config/db.php).
4. Chạy XAMPP Apache và MySQL.
5. Mở trình duyệt truy cập: `http://localhost/thoang-news-main/index.php` (hoặc cổng cấu hình tương ứng của bạn).

### Với PHP Built-in Server

1. Mở terminal tại thư mục gốc của dự án.
2. Chạy lệnh: `php -S localhost:3000 router.php`
3. Mở trình duyệt truy cập: `http://localhost:3000`

<img width="2254" height="1241" alt="image" src="https://github.com/user-attachments/assets/8896d2e0-c89e-482e-a913-f1f9c95578d7" />

## Thành Viên

GROUP 6

| STT | Họ và tên | Link |
| --- | --- | --- |
| 1 | Nguyễn Khánh Hoàng | https://github.com/FishCake9918 |
| 2 | Lê Hoàng Việt | https://github.com/LeHoangViet905 |
| 3 | Nguyễn Kiều Minh Trí | https://github.com/MinhTri1701/Minhtri.git |
| 4 | Hứa Đức Nghĩa | https://github.com/nghia2122005-cmyk |
