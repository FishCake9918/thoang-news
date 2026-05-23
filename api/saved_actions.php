<?php
session_start();
require_once '../config/db.php';
require_once '../config/session.php';

header('Content-Type: application/json; charset=utf-8');

$session_id = session_id();
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

try {
    switch ($action) {
        case 'remove':
            $article_id = (int)($input['article_id'] ?? 0);
            if ($article_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID bai viet khong hop le']);
                break;
            }

            if ($user_id) {
                $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE article_id = ? AND user_id = ?");
                $stmt->execute([$article_id, $user_id]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE article_id = ? AND session_id = ? AND user_id IS NULL");
                $stmt->execute([$article_id, $session_id]);
            }
            echo json_encode(['success' => true]);
            break;

        case 'remove_all':
            if ($user_id) {
                $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ?");
                $stmt->execute([$user_id]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE session_id = ? AND user_id IS NULL");
                $stmt->execute([$session_id]);
            }
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action khong hop le']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Loi SQL: ' . $e->getMessage()]);
}
