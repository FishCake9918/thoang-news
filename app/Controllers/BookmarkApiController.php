<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\BookmarkModel;
use Exception;
use PDOException;

class BookmarkApiController extends Controller
{
    private BookmarkModel $bookmarks;

    public function __construct(BookmarkModel $bookmarks)
    {
        $this->bookmarks = $bookmarks;
    }

    public function toggle(): void
    {
        $articleId = isset($_POST['news_id']) ? (int)$_POST['news_id'] : 0;

        if ($articleId === 0) {
            $input = Request::json();
            $articleId = isset($input['news_id'])
                ? (int)$input['news_id']
                : (int)($input['article_id'] ?? $input['id'] ?? 0);
        }

        if ($articleId <= 0) {
            $this->json(['success' => false, 'message' => 'ID bài viết không hợp lệ.'], 400);
        }

        if (!Auth::check()) {
            $this->json([
                'status' => 'auth_required',
                'success' => false,
                'message' => 'Vui lòng đăng nhập hoặc đăng ký để lưu bài viết.',
            ], 401);
        }

        try {
            $action = $this->bookmarks->toggle($articleId, Auth::id(), session_id());

            if ($action === 'not_found') {
                $this->json([
                    'status' => 'error',
                    'success' => false,
                    'message' => 'Bài viết không tồn tại hoặc chưa được xuất bản.',
                ], 404);
            }

            $this->json(['status' => 'success', 'success' => true, 'action' => $action]);
        } catch (Exception $e) {
            $this->json(['status' => 'error', 'success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function savedAction(): void
    {
        $input = Request::json();
        $action = $input['action'] ?? '';

        if (!Auth::check()) {
            $this->json([
                'success' => false,
                'auth_required' => true,
                'message' => 'Vui lòng đăng nhập hoặc đăng ký để quản lý bài viết đã lưu.',
            ], 401);
        }

        try {
            if ($action === 'remove') {
                $articleId = (int)($input['article_id'] ?? 0);

                if ($articleId <= 0) {
                    $this->json(['success' => false, 'message' => 'ID bài viết không hợp lệ'], 400);
                }

                $this->bookmarks->remove($articleId, Auth::id());
                $this->json(['success' => true]);
            }

            if ($action === 'remove_all') {
                $this->bookmarks->removeAllForUser(Auth::id());
                $this->json(['success' => true]);
            }

            $this->json(['success' => false, 'message' => 'Action không hợp lệ'], 400);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Lỗi SQL: ' . $e->getMessage()], 500);
        }
    }
}
