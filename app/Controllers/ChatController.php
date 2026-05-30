<?php

namespace App\Controllers;

use App\Models\ChatModel;
use App\Models\ArticleModel;

class ChatController
{
    private ChatModel $chatModel;
    private ?ArticleModel $articleModel;

    public function __construct(ChatModel $chatModel, ?ArticleModel $articleModel = null)
    {
        $this->chatModel = $chatModel;
        $this->articleModel = $articleModel;
    }

    public function handleChatRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');
        $articleId = (int)($input['article_id'] ?? 0);

        if ($message === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tin nhắn không được để trống.']);
            return;
        }

        $prompt = $message;

        // Nếu người dùng đang xem một bài báo cụ thể, gửi nội dung bài báo cho AI làm ngữ cảnh
        if ($articleId > 0 && $this->articleModel) {
            $article = $this->articleModel->find($articleId);
            if ($article) {
                $context = "Tiêu đề: " . $article['title'] . "\n";
                $context .= "Tóm tắt: " . $article['summary'] . "\n";
                $context .= "Nội dung: " . strip_tags($article['content'] ?? '') . "\n";
                
                $prompt = "Dưới đây là thông tin bài báo người dùng đang đọc:\n---\n" . $context . "---\n\nDựa vào bài báo trên, hãy trả lời câu hỏi sau của người dùng (Nếu câu hỏi không liên quan đến bài báo, hãy trả lời bình thường như một trợ lý AI): " . $message;
            }
        }

        try {
            $reply = $this->chatModel->sendMessage($prompt);
            echo json_encode(['success' => true, 'reply' => $reply]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}