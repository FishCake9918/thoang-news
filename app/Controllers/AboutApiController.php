<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AboutSectionModel;
use PDOException;

class AboutApiController extends Controller
{
    private AboutSectionModel $sections;

    public function __construct(AboutSectionModel $sections)
    {
        $this->sections = $sections;
    }

    public function save(): void
    {
        if (!Auth::isAdmin()) {
            $this->json(['success' => false, 'message' => 'Không có quyền truy cập.'], 403);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
        }

        $allowedKeys = ['hero', 'stats', 'mission', 'values', 'story', 'features', 'team', 'cta'];
        $sectionKey = trim($_POST['section_key'] ?? '');
        $sectionData = trim($_POST['section_data'] ?? '');

        if (!in_array($sectionKey, $allowedKeys, true)) {
            $this->json(['success' => false, 'message' => 'Section key không hợp lệ.'], 400);
        }

        $decoded = json_decode($sectionData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->json(['success' => false, 'message' => 'Dữ liệu JSON không hợp lệ.'], 400);
        }

        try {
            $this->sections->save($sectionKey, json_encode($decoded, JSON_UNESCAPED_UNICODE), Auth::id());
            $this->json(['success' => true, 'message' => 'Đã lưu thành công!', 'data' => $decoded]);
        } catch (PDOException $e) {
            $this->json(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()], 500);
        }
    }
}
