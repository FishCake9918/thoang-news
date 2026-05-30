<?php

use App\Controllers\WeatherController;
use App\Models\WeatherModel;

// Bắt đầu session để có thể sử dụng cache
session_start();

// Autoload hoặc require các file cần thiết.
// Dựa trên cấu trúc của bạn, có vẻ bạn đang dùng require_once thủ công.
require_once __DIR__ . '/../app/Models/WeatherModel.php';
require_once __DIR__ . '/../app/Controllers/WeatherController.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// !!! QUAN TRỌNG: Bạn BẮT BUỘC phải thay thế 'YOUR_OPENWEATHERMAP_API_KEY' bằng API key của bạn.
// Lấy key miễn phí tại: https://openweathermap.org/appid
$apiKey = '2c8eacdcfb894cbea1b80836262805'; // <--- THAY THẾ KEY CỦA BẠN VÀO ĐÂY

if ($apiKey === 'YOUR_API_KEY_HERE' || $apiKey === '') {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi: Chưa cấu hình API Key cho thời tiết.']);
    exit;
}

$lat = $_GET['lat'] ?? null;
$lon = $_GET['lon'] ?? null;
$city = trim($_GET['city'] ?? 'Ho Chi Minh City,VN'); // Thành phố mặc định
if (strcasecmp($city, 'Ho Chi Minh City') === 0) {
    $city = 'Ho Chi Minh City,VN';
}
$cache_ttl = 600; // Cache trong 10 phút (600 giây)

// Tạo key cache duy nhất dựa trên vị trí
$cache_key = $lat && $lon
    ? 'weather_v6_' . round((float)$lat, 2) . '_' . round((float)$lon, 2)
    : 'weather_v6_' . str_replace([' ', ','], '_', strtolower($city));

// 1. Kiểm tra cache trước khi gọi API
if (isset($_SESSION[$cache_key]) && (time() - $_SESSION[$cache_key]['timestamp']) < $cache_ttl) {
    echo json_encode($_SESSION[$cache_key]['data']);
    exit;
}

try {
    $weatherModel = new WeatherModel($apiKey);
    $weatherController = new WeatherController($weatherModel);

    // 2. Nếu không có cache, gọi API và bắt đầu bộ đệm đầu ra để lấy kết quả
    ob_start();
    if ($lat && $lon) {
        $weatherController->getWeatherByCoords((float)$lat, (float)$lon);
    } else {
        $weatherController->getWeatherByCity($city);
    }
    $response_body = ob_get_clean();
    $response_data = json_decode($response_body, true);
} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => 'Không thể tải thời tiết.']);
    exit;
}

// 3. Nếu gọi API thành công, lưu kết quả vào cache (session)
if (isset($response_data['success']) && $response_data['success'] === true) {
    $_SESSION[$cache_key] = ['timestamp' => time(), 'data' => $response_data];
}

// 4. Trả kết quả về cho client
echo $response_body;
