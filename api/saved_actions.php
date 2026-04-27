<?php
// api/saved_actions.php — Xử lý tất cả thao tác với bài đã lưu
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {

    // ========== TÍNH NĂNG 1: XÓA BÀI LƯU ==========
    case 'remove':
        $id = (int)($input['article_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM saved_articles WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['success' => true]);
        break;

    case 'remove_all':
        $stmt = $pdo->prepare("DELETE FROM saved_articles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true]);
        break;

    // ========== TÍNH NĂNG 3: GHI CHÚ ==========
    case 'save_note':
        $id   = (int)($input['article_id'] ?? 0);
        $note = trim($input['note'] ?? '');
        $stmt = $pdo->prepare("UPDATE saved_articles SET note = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$note, $id, $user_id]);
        echo json_encode(['success' => true]);
        break;

    // ========== TÍNH NĂNG 2: NHÃN ==========
    case 'create_tag':
        $name  = trim($input['name'] ?? '');
        $color = $input['color'] ?? '#1a2744';
        if (!$name) { echo json_encode(['success' => false]); break; }
        $stmt = $pdo->prepare("INSERT INTO saved_tags (user_id, name, color) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $name, $color]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'add_tags':
        $article_id = (int)($input['article_id'] ?? 0);
        $tag_ids    = $input['tag_ids'] ?? [];
        // Xóa tag cũ trước
        $pdo->prepare("DELETE FROM article_tags WHERE article_id = ?")->execute([$article_id]);
        // Thêm tag mới
        $stmt = $pdo->prepare("INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (?, ?)");
        foreach ($tag_ids as $tag_id) {
            $stmt->execute([$article_id, (int)$tag_id]);
        }
        echo json_encode(['success' => true]);
        break;

    case 'remove_tag':
        $article_id = (int)($input['article_id'] ?? 0);
        $tag_id     = (int)($input['tag_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM article_tags WHERE article_id = ? AND tag_id = ?");
        $stmt->execute([$article_id, $tag_id]);
        echo json_encode(['success' => true]);
        break;

    // ========== TÍNH NĂNG 4: BỘ SƯU TẬP ==========
    case 'create_collection':
        $name = trim($input['name'] ?? '');
        if (!$name) { echo json_encode(['success' => false]); break; }
        $stmt = $pdo->prepare("INSERT INTO collections (user_id, name) VALUES (?, ?)");
        $stmt->execute([$user_id, $name]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    case 'add_to_collection':
        $article_id     = (int)($input['article_id'] ?? 0);
        $collection_ids = $input['collection_ids'] ?? [];
        $stmt = $pdo->prepare("INSERT IGNORE INTO collection_articles (collection_id, article_id) VALUES (?, ?)");
        foreach ($collection_ids as $col_id) {
            $stmt->execute([(int)$col_id, $article_id]);
        }
        echo json_encode(['success' => true]);
        break;

    case 'delete_collection':
        $col_id = (int)($input['collection_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM collections WHERE id = ? AND user_id = ?");
        $stmt->execute([$col_id, $user_id]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}
