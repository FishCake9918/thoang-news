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
        if (($data['provider'] ?? '') === 'open-meteo') {
            return '';
        }

        if (!empty($data['weather'][0]['icon'])) {
            return 'https://openweathermap.org/img/wn/' . rawurlencode($data['weather'][0]['icon']) . '@2x.png';
        }

        $icon = (string)($data['current']['condition']['icon'] ?? '');

        if ($icon === '') {
            return '';
        }

        if (str_starts_with($icon, '//')) {
            return 'https:' . $icon;
        }

        if (str_starts_with($icon, 'http://')) {
            return 'https://' . substr($icon, 7);
        }

        if (str_starts_with($icon, 'https://')) {
            return $icon;
        }

        return 'https://cdn.weatherapi.com' . $icon;
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
        if ($data && isset($data['main'])) {
            $this->sendJsonResponse([
                'success' => true,
                'city' => $data['name'] ?? preg_replace('/,\s*VN$/i', '', $city),
                'temp' => round((float)$data['main']['temp']),
                'description' => ucfirst($data['weather'][0]['description'] ?? ''),
                'icon' => $this->iconUrl($data)
            ]);
        } elseif ($data && (($data['provider'] ?? '') === 'open-meteo')) {
            $this->sendJsonResponse([
                'success' => true,
                'city' => $data['name'] ?: preg_replace('/,\s*VN$/i', '', $city),
                'temp' => round((float)$data['current']['temperature_2m']),
                'description' => $this->openMeteoDescription((int)($data['current']['weather_code'] ?? -1)),
                'icon' => ''
            ]);
        } elseif ($data && isset($data['current'])) {
            $this->sendJsonResponse([
                'success' => true,
                'city' => $data['location']['name'] ?? preg_replace('/,\s*VN$/i', '', $city),
                'temp' => round($data['current']['temp_c']),
                'description' => ucfirst($data['current']['condition']['text']),
                'icon' => $this->iconUrl($data)
            ]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Không tìm thấy dữ liệu thời tiết.'], 404);
        }
    }

    public function getWeatherByCoords(float $lat, float $lon): void
    {
        $data = $this->weatherModel->findByCoords($lat, $lon);
        if ($data && isset($data['main'])) {
            $this->sendJsonResponse([
                'success' => true,
                'city' => $data['name'] ?? '',
                'temp' => round((float)$data['main']['temp']),
                'description' => ucfirst($data['weather'][0]['description'] ?? ''),
                'icon' => $this->iconUrl($data)
            ]);
        } elseif ($data && (($data['provider'] ?? '') === 'open-meteo')) {
            $this->sendJsonResponse([
                'success' => true,
                'city' => $data['name'] ?: 'Vị trí hiện tại',
                'temp' => round((float)$data['current']['temperature_2m']),
                'description' => $this->openMeteoDescription((int)($data['current']['weather_code'] ?? -1)),
                'icon' => ''
            ]);
        } elseif ($data && isset($data['current'])) {
            $this->sendJsonResponse([
                'success' => true,
                'city' => $data['location']['name'],
                'temp' => round($data['current']['temp_c']),
                'description' => ucfirst($data['current']['condition']['text']),
                'icon' => $this->iconUrl($data)
            ]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Không thể lấy thời tiết cho vị trí của bạn.'], 404);
        }
    }
}
