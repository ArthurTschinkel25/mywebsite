<?php
namespace app\Controllers;

use app\Models\Weather;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class WeatherController
{
    private $weatherModel;
    private $twig;

    public function __construct()
    {
        $this->weatherModel = new Weather('2c209f3900374efd902221115252604');
        $loader = new FilesystemLoader(__DIR__ . '/../../app/Views');
        $this->twig = new Environment($loader);
    }

    public function handleRequest()
    {
        $city = $_GET['city'] ?? 'São Paulo'; // Valor padrão

        try {
            $weatherData = $this->weatherModel->getWeatherData($city);

            echo $this->twig->render('clima/tableWeather.html', [
                'city' => $city,
                'weather' => $weatherData,
                'hasWeatherData' => isset($weatherData['current'])
            ]);

        } catch (\Exception $e) {
            echo $this->twig->render('clima/tableWeather.html', [
                'error' => 'Não foi possível obter os dados meteorológicos.'
            ]);
        }
    }
}