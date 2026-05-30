<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\UserModel;
use PDOException;

class UserPreferenceApiController extends Controller
{
    private UserModel $users;

    public function __construct(UserModel $users)
    {
        $this->users = $users;
    }

    public function save(): void
    {
        if (!Auth::check()) {
            $this->json(['success' => false, 'message' => 'Bạn cần đăng nhập để lưu tùy chọn.'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
        }

        $data = Request::json();
        $theme = $data['theme'] ?? ($_SESSION['theme_preference'] ?? 'light');
        $fontSize = (int)($data['article_font_size'] ?? ($_SESSION['article_font_size'] ?? 16));

        $theme = $theme === 'dark' ? 'dark' : 'light';
        $fontSize = max(14, min(22, $fontSize));

        try {
            $this->users->updatePreferences(Auth::id(), $theme, $fontSize);

            $_SESSION['theme_preference'] = $theme;
            $_SESSION['article_font_size'] = $fontSize;

            $this->json([
                'success' => true,
                'theme' => $theme,
                'article_font_size' => $fontSize,
            ]);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Không thể lưu tùy chọn giao diện.'], 500);
        }
    }
}
