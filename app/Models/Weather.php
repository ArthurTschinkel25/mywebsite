<?php
namespace app\Models;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Weather
{
    private $apiKey;
    private $client;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client();
    }

    public function getWeatherData($city)
    {
        try {
            $response = $this->client->get('http://api.weatherapi.com/v1/forecast.json', [
                'query' => [
                    'key' => $this->apiKey,
                    'q' => $city,
                    'days' => 4,
                    'lang' => 'pt'
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            return ['error' => 'Não foi possível obter os dados meteorológicos.'];
        }
    }
}