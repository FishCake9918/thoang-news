<?php

namespace App\Models;

class WeatherModel
{
    private string $apiKey;
    private const API_BASE_URL = 'https://api.weatherapi.com/v1/current.json';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    private function fetchWeatherData(string $url): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10-giây timeout
        $json = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || $json === false) {
            return null;
        }

        return json_decode($json, true);
    }

    public function findByCity(string $city): ?array
    {
        $url = sprintf(
            '%s?key=%s&q=%s&lang=vi',
            self::API_BASE_URL,
            $this->apiKey,
            urlencode($city)
        );
        return $this->fetchWeatherData($url);
    }

    public function findByCoords(float $lat, float $lon): ?array
    {
        $url = sprintf(
            '%s?key=%s&q=%f,%f&lang=vi',
            self::API_BASE_URL,
            $this->apiKey,
            $lat,
            $lon
        );
        return $this->fetchWeatherData($url);
    }
}