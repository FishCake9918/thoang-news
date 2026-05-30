<?php

namespace App\Controllers;

use App\Models\ArticleModel;
use App\Models\UserModel;
use PDOException;

class WriterProfileController
{
    private UserModel $users;
    private ArticleModel $articles;

    public function __construct(UserModel $users, ArticleModel $articles)
    {
        $this->users = $users;
        $this->articles = $articles;
    }

    public function show(int $writerId, bool $isAdmin): array
    {
        try {
            $this->users->ensureAvatarColumn();
            $writer = $this->users->findWriterProfile($writerId);

            if (!$writer) {
                return [
                    'writer' => null,
                    'articles' => [],
                    'error' => 'Không tìm thấy tài khoản writer.',
                ];
            }

            return [
                'writer' => $writer,
                'articles' => $this->articles->byWriterForProfile($writerId, $isAdmin),
                'error' => '',
            ];
        } catch (PDOException $e) {
            return [
                'writer' => null,
                'articles' => [],
                'error' => 'Có lỗi xảy ra khi tải hồ sơ writer.',
            ];
        }
    }
}
