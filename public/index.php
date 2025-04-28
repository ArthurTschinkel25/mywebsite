<?php
require_once __DIR__ . '/../vendor/autoload.php';

use app\Controllers\WeatherController;
use app\Controllers\FlagController;
use app\Routes\Router;

$router = new Router();

// Rota padrão que redireciona para /clima
$router->addRoute('/', function() {
    header('Location: /clima');
    exit;
});

// Rota do clima
$router->addRoute('/clima', WeatherController::class);

// Rota das bandeiras
$router->addRoute('/bandeiras', FlagController::class);

// Executa a rota correspondente
$router->dispatch();