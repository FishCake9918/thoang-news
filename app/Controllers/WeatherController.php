<?php

namespace App\Controllers;

use App\Models\WeatherModel;

class WeatherController
{
    private WeatherModel $weatherModel;

    public function __construct(WeatherModel $weatherModel)
    {
        $this->weatherModel = $weatherModel;
    }

    private function sendJsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
    }

    private function iconUrl(array $data): string
    {
        return '';
    }

    private function openMeteoDescription(int $code): string
    {
        if ($code === 0) {
            return 'Trời quang';
        }

        if (in_array($code, [1, 2, 3], true)) {
            return 'Có mây';
        }

        if (in_array($code, [45, 48], true)) {
            return 'Sương mù';
        }

        if (($code >= 51 && $code <= 67) || ($code >= 80 && $code <= 82)) {
            return 'Mưa';
        }

        if ($code >= 95) {
            return 'Dông';
        }

        return 'Thời tiết';
    }

    public function getWeatherByCity(string $city): void
    {
        $data = $this->weatherModel->findByCity($city);
        if ($data && (($data['provider'] ?? '') === 'open-meteo')) {
            $this->sendJsonResponse([
                'success' => true,
                'city' => $data['name'] ?: preg_replace('/,\s*VN$/i', '', $city),
                'temp' => round((float)$data['current']['temperature_2m']),
                'description' => $this->openMeteoDescription((int)($data['current']['weather_code'] ?? -1)),
                'icon' => ''
            ]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Không tìm thấy dữ liệu thời tiết.'], 404);
        }
    }

    public function getWeatherByCoords(float $lat, float $lon): void
    {
        $data = $this->weatherModel->findByCoords($lat, $lon);
        if ($data && (($data['provider'] ?? '') === 'open-meteo')) {
            $this->sendJsonResponse([
                'success' => true,
                'city' => 'Vị trí hiện tại',
                'temp' => round((float)$data['current']['temperature_2m']),
                'description' => $this->openMeteoDescription((int)($data['current']['weather_code'] ?? -1)),
                'icon' => ''
            ]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Không thể lấy thời tiết cho vị trí của bạn.'], 404);
        }
    }
}
