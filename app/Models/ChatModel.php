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

        // THAY ĐỔI: Cấu trúc payload theo chuẩn OpenAI/OpenRouter
        $data = [
            "model" => "openrouter/owl-alpha", // Bạn có thể đổi sang model khác tuỳ ý
            "messages" => [
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