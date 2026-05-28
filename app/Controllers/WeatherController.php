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

    public function getWeatherByCity(string $city): void
    {
        $data = $this->weatherModel->findByCity($city);
        if ($data && isset($data['current'])) {
            $this->sendJsonResponse([
                'success' => true,
                'city' => $data['location']['name'],
                'temp' => round($data['current']['temp_c']),
                'description' => ucfirst($data['current']['condition']['text']),
                'icon' => 'https:' . $data['current']['condition']['icon']
            ]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Không tìm thấy dữ liệu thời tiết.'], 404);
        }
    }

    public function getWeatherByCoords(float $lat, float $lon): void
    {
        $data = $this->weatherModel->findByCoords($lat, $lon);
        if ($data && isset($data['current'])) {
            $this->sendJsonResponse([
                'success' => true,
                'city' => $data['location']['name'],
                'temp' => round($data['current']['temp_c']),
                'description' => ucfirst($data['current']['condition']['text']),
                'icon' => 'https:' . $data['current']['condition']['icon']
            ]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Không thể lấy thời tiết cho vị trí của bạn.'], 404);
        }
    }
}