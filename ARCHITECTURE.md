# Kien Truc Backend OOP + MVC Nhe

Project da tach backend theo huong doi tuong de dap ung yeu cau mon hoc.

## Cau Truc

- `app/Core`: lop nen `Controller` va `Model`.
- `app/Core/Auth.php`, `Request.php`, `Response.php`, `DateHelper.php`, `TextHelper.php`: cac lop tien ich dung chung cho session, AJAX request, JSON response, ngay hien thi va xu ly chuoi.
- `app/Models`: tang lam viec voi CSDL, vi du `ArticleModel`, `CategoryModel`, `BookmarkModel`, `AdminModel`.
- `app/Controllers`: tang xu ly request, vi du `ArticleController`, `AdminController`, `WriterArticleController`, `AuthController`, `PasswordController`, `WriterDashboardController`, `AdminDashboardController`, `AboutController`, `VerificationController`, `WriterFormController`, `FeedApiController`, `BookmarkApiController`, `WriterArticleApiController`, `AboutApiController`, `FeedbackApiController`.
- `app/Views`: tang giao dien. Vi du `app/Views/auth/login.php`, `app/Views/home/index.php`, `app/Views/articles/show.php`, `app/Views/dashboard/admin.php`, `app/Views/dashboard/writer.php`, `app/Views/writer/form.php`, `app/Views/saved/index.php`, `app/Views/search/index.php`, `app/Views/about/index.php`.
- `app/Views/Components`: cac component view tai su dung, hien co `CommentComponent` de render binh luan cho ca trang bai viet va AJAX response.
- Cac file `index.php`, `article.php`, `dashboard.php`, `dashboard_writer.php`, `vietbai.php`, `saved.php`, `search.php`, `about.php`, `login.php`, `register.php`, `forgot_password.php`, `reset_password.php`, `verify.php` chi dong vai tro entry point mong va render qua `App\Core\View`.
- Cac file trong `api/` chi dong vai tro endpoint Webservice/AJAX; logic xu ly nam trong controller API va tra JSON.

## Luu Y Ve File PHP O Thu Muc Goc

Khong move cac file nhu `index.php`, `login.php`, `saved.php`, `article.php` vao `app/Views` vi chung la URL/entry point cua ung dung tren XAMPP. Neu move truc tiep cac file nay, cac link va form hien tai se bi gay.

Cach to chuc dung trong project nay:

- Thu muc goc chi giu entry point mong de khoi dong session, nap config, goi Controller va render View.
- Tat ca giao dien HTML nam trong `app/Views`.
- Controller khong require view truc tiep; Controller tra du lieu hoac JSON, con render duoc thuc hien qua `App\Core\View::render()`.
- `App\Core\View` la noi duy nhat resolve duong dan view: `app/Views/{ten-view}.php`.

## Luong Xu Ly

`View/Entry point -> Controller -> Model -> MySQL`

Sau khi Model truy van MySQL, Controller tra ve ket qua cho View hoac JSON cho frontend.

## Vi Du

- `login.php` va `register.php` goi truc tiep `App\Controllers\AuthController`.
- `forgot_password.php` va `reset_password.php` goi truc tiep `App\Controllers\PasswordController`.
- `verify.php` goi `App\Controllers\VerificationController`.
- `login.php`, `register.php`, `forgot_password.php`, `reset_password.php` render giao dien qua `App\Core\View` va `app/Views/auth/*`.
- `article.php` render giao dien qua `app/Views/articles/show.php`.
- `dashboard_writer.php` lay du lieu thong qua `App\Controllers\WriterDashboardController`.
- `dashboard.php` lay du lieu thong qua `App\Controllers\AdminDashboardController`.
- `dashboard_writer.php` render giao dien qua `app/Views/dashboard/writer.php`.
- `dashboard.php` render giao dien qua `app/Views/dashboard/admin.php`.
- `index.php` lay du lieu qua `App\Controllers\HomeController` va render `app/Views/home/index.php`.
- `saved.php` lay du lieu qua `App\Controllers\SavedController` va render `app/Views/saved/index.php`.
- `search.php` lay du lieu qua `App\Controllers\SearchController` va render `app/Views/search/index.php`.
- `vietbai.php` lay du lieu qua `WriterFormController` va render form viet bai qua `app/Views/writer/form.php`.
- `about.php` lay section qua `AboutController` va render `app/Views/about/index.php`.
- `api/get_news.php` goi `FeedApiController`, tra JSON danh sach bai viet cho AJAX.
- `api/toggle_bookmark.php` va `api/saved_actions.php` goi `BookmarkApiController`, dung `BookmarkModel` de luu/xoa bai viet va tra JSON.
- `api/add_article.php` va `api/upload_article_image.php` goi `WriterArticleApiController`.
- `api/about_save.php` goi `AboutApiController`.
- `api/contact_submit.php` va `api/feedback_action.php` goi `FeedbackApiController`.
- `config/session.php` giu cac ham wrapper cu nhu `isLoggedIn()`, `jsonResponse()`, `viDate()` nhung ben trong da uy quyen cho cac class OOP trong `app/Core`, giup code cu van chay trong khi backend ro rang huong doi tuong hon.

## Mapping MVC

- Model: `app/Models/*Model.php`
- Controller: `app/Controllers/*Controller.php`
- View: `app/Views/*`, bao gom `app/Views/partials` cho header/footer dung chung.
- Webservice/AJAX: `api/*.php`, tra ve JSON

Day la cach to chuc MVC phu hop voi project PHP thuan/XAMPP: moi URL hien tai van giu nguyen, nhung logic xu ly nghiep vu va truy van CSDL da duoc dua vao class Controller/Model.
