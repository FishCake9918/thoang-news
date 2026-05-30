<?php

namespace App\Models;

class WeatherModel
{
    private string $apiKey;
    private const OPENWEATHER_BASE_URL = 'https://api.openweathermap.org/data/2.5/weather';
    private const WEATHERAPI_BASE_URL = 'https://api.weatherapi.com/v1/current.json';
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
        $openWeatherUrl = sprintf(
            '%s?appid=%s&q=%s&units=metric&lang=vi',
            self::OPENWEATHER_BASE_URL,
            $this->apiKey,
            urlencode($city)
        );
        $data = $this->fetchWeatherData($openWeatherUrl);

        if ($data && isset($data['main'])) {
            return $data;
        }

        $weatherApiUrl = sprintf(
            '%s?key=%s&q=%s&lang=vi',
            self::WEATHERAPI_BASE_URL,
            $this->apiKey,
            urlencode($city)
        );
        $data = $this->fetchWeatherData($weatherApiUrl);

        if ($data && isset($data['current'])) {
            return $data;
        }

        if (preg_match('/ho\\s*chi\\s*minh|hcm|sai\\s*gon/i', $city)) {
            return $this->findByOpenMeteoCoords(10.8231, 106.6297, 'Ho Chi Minh City');
        }

        return null;
    }

    public function findByCoords(float $lat, float $lon): ?array
    {
        $openWeatherUrl = sprintf(
            '%s?appid=%s&lat=%f&lon=%f&units=metric&lang=vi',
            self::OPENWEATHER_BASE_URL,
            $this->apiKey,
            $lat,
            $lon
        );
        $data = $this->fetchWeatherData($openWeatherUrl);

        if ($data && isset($data['main'])) {
            return $data;
        }

        $weatherApiUrl = sprintf(
            '%s?key=%s&q=%f,%f&lang=vi',
            self::WEATHERAPI_BASE_URL,
            $this->apiKey,
            $lat,
            $lon
        );
        $data = $this->fetchWeatherData($weatherApiUrl);

        if ($data && isset($data['current'])) {
            return $data;
        }

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
