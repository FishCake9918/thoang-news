<?php

namespace App\Models;

class ChatModel
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function sendMessage(string $message): ?string
    {
        // THAY ĐỔI: URL của OpenRouter thay vì generativelanguage.googleapis.com
        $url = "https://openrouter.ai/api/v1/chat/completions";

        $systemPrompt = <<<PROMPT
Bạn là Trợ lý AI thông minh của trang web đọc tin tức Thoáng.vn. Bạn được tạo ra trong dự án WEB TIN TỨC của nhóm 6 tại trường Đại học Kinh tế TP.HCM (UEH).
Khi người dùng hỏi "Bạn là ai?", "Ai tạo ra bạn?", "Bạn có thể làm gì?", bạn PHẢI trả lời bám sát ý sau: "Chào bạn, tôi là Trợ lý AI được tạo ra trong dự án WEB TIN TỨC của nhóm 6 tại UEH, tôi có thể giúp bạn các công việc: Tóm tắt bài viết nhanh chóng, tìm kiếm tin tức theo chuyên mục, gợi ý bài viết hấp dẫn và hướng dẫn bạn cách sử dụng trang web Thoáng.vn."
Quy tắc: Xưng hô là "tôi" và gọi người dùng là "bạn". Thái độ chuyên nghiệp, thân thiện và trả lời ngắn gọn. Chỉ hỗ trợ các vấn đề liên quan đến tin tức và Thoáng.vn. Nếu hỏi ngoài lề, hãy khéo léo từ chối và hướng họ đọc tin tức.
PROMPT;

        // THAY ĐỔI: Cấu trúc payload theo chuẩn OpenAI/OpenRouter
        $data = [
            "model" => "openrouter/owl-alpha", // Bạn có thể đổi sang model khác tuỳ ý
            "messages" => [
                [
                    "role" => "system",
                    "content" => $systemPrompt
                ],
                [
                    "role" => "user",
                    "content" => "Bạn là ai vậy?"
                ],
                [
                    "role" => "assistant",
                    "content" => "Chào bạn, tôi là Trợ lý AI được tạo ra trong dự án WEB TIN TỨC của nhóm 6 tại UEH, tôi có thể giúp bạn các công việc: tóm tắt bài viết nhanh chóng, tìm kiếm tin tức theo chuyên mục, gợi ý bài viết hấp dẫn và hướng dẫn bạn cách sử dụng trang web Thoáng.vn. Bạn cần tôi giúp gì hôm nay?"
                ],
                [
                    "role" => "user",
                    "content" => "Website này có những chuyên mục nào?"
                ],
                [
                    "role" => "assistant",
                    "content" => "Hiện tại Thoáng.vn cung cấp các chuyên mục tin tức như: Công nghệ, Đời sống, Thể thao, Kinh tế, Thế giới và Giáo dục. Bạn đang quan tâm đến chủ đề nào để tôi tìm bài viết giúp bạn nhé!"
                ],
                [
                    "role" => "user",
                    "content" => $message
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        
        // THAY ĐỔI: Thêm Authorization và các Header bắt buộc của OpenRouter
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'HTTP-Referer: http://localhost', // Thay bằng tên miền website thực tế của bạn
            'X-Title: Thoang News' 
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        // Giữ nguyên thiết lập bỏ qua chứng chỉ SSL khi test trên XAMPP
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // THAY ĐỔI: Bóc tách kết quả theo cấu trúc JSON mới
        if ($httpCode === 200 && $response) {
            $responseData = json_decode($response, true);
            return $responseData['choices'][0]['message']['content'] ?? 'AI không phản hồi dữ liệu hợp lệ.';
        }

        $errorMessage = "HTTP $httpCode";
        if ($curlError) {
            $errorMessage .= " - Lỗi kết nối cURL: $curlError";
        }
        if ($response) {
            $errorData = json_decode($response, true);
            $errorMessage .= " - OpenRouter báo lỗi: " . ($errorData['error']['message'] ?? $response);
        }
        
        throw new \Exception($errorMessage);
    }
}