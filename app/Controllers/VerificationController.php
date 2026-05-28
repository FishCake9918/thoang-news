<?php

namespace App\Controllers;

use App\Models\UserModel;
use PDOException;

class VerificationController
{
    private UserModel $users;

    public function __construct(UserModel $users)
    {
        $this->users = $users;
    }

    public function verify(string $token): array
    {
        if ($token === '') {
            return [
                'message' => 'Yêu cầu không hợp lệ. Thiếu mã xác thực kích hoạt.',
                'status_class' => 'alert-danger',
            ];
        }

        try {
            $user = $this->users->findVerificationState($token);

            if (!$user) {
                return [
                    'message' => 'Mã xác thực không hợp lệ hoặc liên kết kích hoạt này đã hết hạn.',
                    'status_class' => 'alert-danger',
                ];
            }

            if ((int)$user['is_verified'] === 1) {
                return [
                    'message' => 'Tài khoản này đã được kích hoạt từ trước.',
                    'status_class' => 'alert-warning',
                ];
            }

            if ($this->users->markVerified((int)$user['id'])) {
                return [
                    'message' => 'Chúc mừng! Tài khoản của bạn đã được xác thực thành công. Bây giờ bạn có thể đăng nhập vào hệ thống.',
                    'status_class' => 'alert-success',
                ];
            }
        } catch (PDOException $e) {
        }

        return [
            'message' => 'Có lỗi trong quá trình xử lý xác thực trên hệ thống.',
            'status_class' => 'alert-danger',
        ];
    }
}
