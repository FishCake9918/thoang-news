<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use PDOException;

class AuthController extends Controller
{
    private UserModel $users;

    public function __construct(UserModel $users)
    {
        $this->users = $users;
    }

    public function login(): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $login = trim($_POST['login_id'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($login === '' || $password === '') {
            return 'Vui lòng điền đầy đủ thông tin.';
        }

        try {
            $user = $this->users->findByLogin($login);

            if (!$user || !password_verify($password, $user['password'])) {
                return 'Tên đăng nhập hoặc mật khẩu không đúng.';
            }

            if (isset($user['is_verified']) && (int)$user['is_verified'] === 0) {
                return 'Tài khoản của bạn chưa kích hoạt. Vui lòng kiểm tra email để xác thực trước khi đăng nhập.';
            }

            $oldSessionId = session_id();
            session_regenerate_id(true);
            $newSessionId = session_id();

            $this->users->mergeGuestBookmarks((int)$user['id'], $oldSessionId, $newSessionId);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['avatar'] = $user['avatar'] ?? 'images/avatars/avatar-01.svg';
            $_SESSION['role'] = $user['role'];

            $redirect = 'index.php';
            if ($user['role'] === 'admin') {
                $redirect = 'dashboard.php';
            } elseif ($user['role'] === 'writer') {
                $redirect = 'dashboard_writer.php';
            }

            $this->redirect($_GET['redirect'] ?? $redirect);
        } catch (PDOException $e) {
            return 'Có lỗi xảy ra. Vui lòng thử lại.';
        }
    }

    public function register(callable $sendMail): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['', ''];
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if ($fullName === '' || $username === '' || $email === '' || $password === '') {
            return ['Vui lòng điền đầy đủ tất cả các trường.', ''];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['Địa chỉ email không hợp lệ.', ''];
        }

        if (strlen($password) < 6) {
            return ['Mật khẩu phải có ít nhất 6 ký tự.', ''];
        }

        if ($password !== $confirm) {
            return ['Xác nhận mật khẩu không khớp.', ''];
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            return ['Tên đăng nhập chỉ gồm chữ cái, số, dấu gạch dưới (3-30 ký tự).', ''];
        }

        try {
            if ($this->users->usernameOrEmailExists($username, $email)) {
                return ['Tên đăng nhập hoặc email đã được sử dụng.', ''];
            }

            $token = bin2hex(random_bytes(32));
            $hash = password_hash($password, PASSWORD_BCRYPT);

            if ($this->users->createUser($username, $email, $hash, $fullName, $token)) {
                $verifyLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify.php?token=" . $token;
                $subject = "Kích hoạt tài khoản thành viên của bạn - Thoáng.vn";
                $body = "<p>Xin chào <strong>" . htmlspecialchars($fullName) . "</strong>,</p>"
                    . "<p>Vui lòng nhấn vào liên kết sau để kích hoạt tài khoản:</p>"
                    . "<p><a href='" . htmlspecialchars($verifyLink) . "'>Kích hoạt tài khoản</a></p>";

                $sendMail($email, $subject, $body);
                return ['', 'Đăng ký thành công! Một email xác thực đã được gửi. Vui lòng kiểm tra hộp thư để kích hoạt tài khoản.'];
            }
        } catch (PDOException $e) {
            return ['Có lỗi xảy ra. Vui lòng thử lại.', ''];
        }

        return ['Có lỗi xảy ra. Vui lòng thử lại.', ''];
    }
}
