<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use PDOException;

class PasswordController extends Controller
{
    private UserModel $users;

    public function __construct(UserModel $users)
    {
        $this->users = $users;
    }

    public function forgotPassword(callable $sendMail): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['', ''];
        }

        $email = trim($_POST['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['Vui lòng cung cấp một địa chỉ email hợp lệ.', ''];
        }

        try {
            $user = $this->users->findByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $this->users->setResetToken((int)$user['id'], $token, $expires);

                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                $subject = "Yêu cầu khôi phục mật khẩu - Thoáng.vn";
                $body = "<p>Xin chào <strong>" . htmlspecialchars($user['full_name'] ?? '') . "</strong>,</p>"
                    . "<p>Liên kết khôi phục mật khẩu có hiệu lực trong 15 phút:</p>"
                    . "<p><a href='" . htmlspecialchars($resetLink) . "'>Đặt lại mật khẩu</a></p>";

                $sendMail($email, $subject, $body);
            }

            return ['', 'Nếu email của bạn tồn tại trên hệ thống, một đường dẫn khôi phục mật khẩu đã được gửi đi. Vui lòng kiểm tra hộp thư.'];
        } catch (PDOException $e) {
            return ['Hệ thống đang bận, vui lòng thử lại sau.', ''];
        }
    }

    public function resetPassword(string $token): array
    {
        $userId = $token !== '' ? $this->users->findIdByValidResetToken($token) : 0;

        if ($userId <= 0) {
            return ['Yêu cầu khôi phục mật khẩu không hợp lệ.', ''];
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['', ''];
        }

        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if (strlen($password) < 6) {
            return ['Mật khẩu mới phải có tối thiểu từ 6 ký tự.', ''];
        }

        if ($password !== $confirm) {
            return ['Mật khẩu xác nhận nhập lại không trùng khớp.', ''];
        }

        try {
            if ($this->users->resetPassword($userId, password_hash($password, PASSWORD_BCRYPT))) {
                header('refresh:2;url=login.php');
                return ['', 'Đặt lại mật khẩu thành công! Bạn đang được chuyển hướng về trang đăng nhập...'];
            }
        } catch (PDOException $e) {
            return ['Đổi mật khẩu thất bại. Vui lòng thử lại.', ''];
        }

        return ['Đổi mật khẩu thất bại. Vui lòng thử lại.', ''];
    }
}
