<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use PDOException;

class AccountController extends Controller
{
    private UserModel $users;

    public function __construct(UserModel $users)
    {
        $this->users = $users;
    }

    public function handle(int $userId): array
    {
        $this->users->ensureAvatarColumn();

        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$error, $message] = $this->handlePost($userId);
        }

        $user = $this->users->findById($userId);
        if (!$user) {
            $this->redirect('logout.php');
        }

        return [
            'user' => $user,
            'avatars' => UserModel::defaultAvatars(),
            'error' => $error,
            'message' => $message,
        ];
    }

    private function handlePost(int $userId): array
    {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'account') {
                return $this->updateAccount($userId);
            }

            if ($action === 'password') {
                return $this->updatePassword($userId);
            }
        } catch (PDOException $e) {
            return ['Có lỗi cơ sở dữ liệu. Vui lòng thử lại.', ''];
        }

        return ['Yêu cầu không hợp lệ.', ''];
    }

    private function updateAccount(int $userId): array
    {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $avatar = trim($_POST['avatar'] ?? '');
        $avatars = UserModel::defaultAvatars();

        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            return ['Tên đăng nhập chỉ gồm chữ cái, số, dấu gạch dưới (3-30 ký tự).', ''];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['Địa chỉ email không hợp lệ.', ''];
        }

        if (!in_array($avatar, $avatars, true)) {
            return ['Avatar không hợp lệ.', ''];
        }

        if ($this->users->usernameOrEmailTakenByOther($userId, $username, $email)) {
            return ['Tên đăng nhập hoặc email đã được tài khoản khác sử dụng.', ''];
        }

        $this->users->updateAccount($userId, $username, $email, $avatar);

        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['avatar'] = $avatar;

        return ['', 'Đã cập nhật thông tin tài khoản.'];
    }

    private function updatePassword(int $userId): array
    {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $user = $this->users->findById($userId);

        if (!$user || !password_verify($current, $user['password'])) {
            return ['Mật khẩu hiện tại không đúng.', ''];
        }

        if (strlen($new) < 6) {
            return ['Mật khẩu mới phải có ít nhất 6 ký tự.', ''];
        }

        if ($new !== $confirm) {
            return ['Xác nhận mật khẩu mới không khớp.', ''];
        }

        $this->users->updatePassword($userId, password_hash($new, PASSWORD_BCRYPT));

        return ['', 'Đã đổi mật khẩu thành công.'];
    }
}
