<?php

namespace App\Models;

class WeatherModel
{
    private string $apiKey;
    private const OPENMETEO_BASE_URL = 'https://api.open-meteo.com/v1/forecast';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    private function fetchWeatherData(string $url): ?array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $json = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code !== 200 || $json === false) {
                return null;
            }

            return json_decode($json, true);
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
            ],
        ]);
        $json = @file_get_contents($url, false, $context);

        if ($json === false) {
            return null;
        }

        return json_decode($json, true);
    }

    public function findByCity(string $city): ?array
    {
        if (preg_match('/ho\\s*chi\\s*minh|hcm|sai\\s*gon/i', $city)) {
            return $this->findByOpenMeteoCoords(10.8231, 106.6297, 'Ho Chi Minh City');
        }

        return $this->findByOpenMeteoCoords(10.8231, 106.6297, 'Ho Chi Minh City');
    }

    public function findByCoords(float $lat, float $lon): ?array
    {
        return $this->findByOpenMeteoCoords($lat, $lon);
    }

    private function findByOpenMeteoCoords(float $lat, float $lon, string $city = ''): ?array
    {
        $url = sprintf(
            '%s?latitude=%f&longitude=%f&current=temperature_2m,weather_code&timezone=auto',
            self::OPENMETEO_BASE_URL,
            $lat,
            $lon
        );
        $data = $this->fetchWeatherData($url);

        if ($data && isset($data['current']['temperature_2m'])) {
            $data['provider'] = 'open-meteo';
            $data['name'] = $city;
            return $data;
        }

        return null;
    }
}
