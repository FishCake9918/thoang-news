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
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $message]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bỏ qua lỗi chứng chỉ SSL trên XAMPP (Localhost)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Bỏ qua kiểm tra tên miền SSL

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $responseData = json_decode($response, true);
            return $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'AI không phản hồi dữ liệu hợp lệ.';
        }

        $errorMessage = "HTTP $httpCode";
        if ($curlError) {
            $errorMessage .= " - Lỗi kết nối cURL: $curlError";
        }
        if ($response) {
            $errorData = json_decode($response, true);
            $errorMessage .= " - Google báo lỗi: " . ($errorData['error']['message'] ?? $response);
        }
        
        throw new \Exception($errorMessage);
    }
}