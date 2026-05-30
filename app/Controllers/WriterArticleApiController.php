<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\ArticleModel;
use App\Models\CategoryModel;
use PDOException;

class WriterArticleApiController extends Controller
{
    private ArticleModel $articles;
    private CategoryModel $categories;

    public function __construct(ArticleModel $articles, CategoryModel $categories)
    {
        $this->articles = $articles;
        $this->categories = $categories;
    }

    public function save(): void
    {
        if (!Auth::check() || Auth::role() !== 'writer') {
            $this->json(['success' => false, 'message' => 'Bạn không có quyền dùng chức năng viết bài.'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Phương thức gửi dữ liệu không hợp lệ.'], 405);
        }

        $inputData = Request::json();

        if ($inputData === []) {
            $this->json(['success' => false, 'message' => 'Định dạng dữ liệu không hợp lệ.'], 400);
        }

        $title = trim($inputData['title'] ?? '');
        $categoryId = (int)($inputData['category_id'] ?? 0);
        $summary = trim($inputData['summary'] ?? '');
        $content = trim($inputData['content'] ?? '');
        $tags = trim($inputData['tags'] ?? '');
        $imageUrl = trim($inputData['image_url'] ?? '');
        $articleId = (int)($inputData['article_id'] ?? 0);

        if ($title === '') {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập tiêu đề bài viết.'], 400);
        }

        if ($categoryId <= 0) {
            $this->json(['success' => false, 'message' => 'Vui lòng chọn danh mục hợp lệ.'], 400);
        }

        if ($summary === '') {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập đoạn tóm tắt bài viết.'], 400);
        }

        if ($content === '') {
            $this->json(['success' => false, 'message' => 'Vui lòng nhập nội dung chi tiết bài viết.'], 400);
        }

        if ($imageUrl !== '' && !preg_match('#^uploads/articles/[A-Za-z0-9_.-]+$#', $imageUrl)) {
            $this->json(['success' => false, 'message' => 'Đường dẫn hình ảnh không hợp lệ.'], 400);
        }

        $authorName = trim($_SESSION['full_name'] ?? '') ?: trim($_SESSION['username'] ?? 'Tác giả');

        try {
            if (!$this->categories->isSelectableForArticle($categoryId)) {
                $this->json([
                    'success' => false,
                    'message' => 'Danh mục này không thể gắn bài viết. Vui lòng chọn danh mục con đang hiển thị.',
                ], 400);
            }

            if ($articleId > 0 && !$this->articles->findByAuthor($articleId, Auth::id())) {
                $this->json(['success' => false, 'message' => 'Không tìm thấy bài viết hoặc bạn không có quyền chỉnh sửa.'], 404);
            }

            $payload = [
                ':category_id' => $categoryId,
                ':author_id' => Auth::id(),
                ':title' => $title,
                ':summary' => $summary,
                ':content' => $content,
                ':author_name' => $authorName,
                ':tags' => $tags !== '' ? $tags : null,
                ':image_url' => $imageUrl !== '' ? $imageUrl : null,
            ];

            if ($articleId > 0) {
                $payload[':article_id'] = $articleId;
            }

            $this->articles->saveDraftForReview($payload);

            $this->json([
                'success' => true,
                'message' => $articleId > 0
                    ? 'Bài viết đã được cập nhật và chuyển về trạng thái chờ Admin duyệt.'
                    : 'Bài viết đã được gửi và đang chờ Admin duyệt.',
            ]);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()], 500);
        }
    }

    public function uploadImage(): void
    {
        if (!Auth::check() || Auth::role() !== 'writer') {
            $this->json(['success' => false, 'message' => 'Bạn không có quyền tải ảnh.'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) {
            $this->json(['success' => false, 'message' => 'Vui lòng chọn hình ảnh cần tải lên.'], 400);
        }

        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'Không thể tải ảnh lên.'], 400);
        }

        if ($file['size'] > 3 * 1024 * 1024) {
            $this->json(['success' => false, 'message' => 'Ảnh không được vượt quá 3MB.'], 400);
        }

        $info = @getimagesize($file['tmp_name']);
        $allowed = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            IMAGETYPE_GIF => 'gif',
        ];

        if (!$info || !isset($allowed[$info[2]])) {
            $this->json(['success' => false, 'message' => 'File tải lên phải là ảnh JPG, PNG, WEBP hoặc GIF.'], 400);
        }

        $uploadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'articles';

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
            $this->json(['success' => false, 'message' => 'Không thể tạo thư mục lưu ảnh.'], 500);
        }

        $filename = 'article_' . Auth::id() . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$info[2]];
        $target = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            $this->json(['success' => false, 'message' => 'Không thể lưu ảnh lên máy chủ.'], 500);
        }

        $this->json(['success' => true, 'url' => 'uploads/articles/' . $filename]);
    }
}
