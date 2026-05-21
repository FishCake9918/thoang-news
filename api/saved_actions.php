<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$session_id = session_id();
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'remove':
            $article_id = (int)($input['article_id'] ?? 0);
            if ($article_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID bai viet khong hop le']);
                break;
            }

            $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE article_id = ? AND session_id = ?");
            $stmt->execute([$article_id, $session_id]);
            echo json_encode(['success' => true]);
            break;

        case 'remove_all':
            $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE session_id = ?");
            $stmt->execute([$session_id]);
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action khong hop le']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Loi SQL: ' . $e->getMessage()]);
}
