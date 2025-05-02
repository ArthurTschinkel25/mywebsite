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

// Rota para salvar pontuação via AJAX
$router->addRoute('/salvar-pontuacao', function () {
    $controller = new FlagController();

    // Garante que está vindo via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acertou = $_POST['acertou'] === 'true';
        $controller->salvarPontuacao($acertou);
        echo json_encode(['status' => 'ok']);
    } else {
        http_response_code(405); // Método não permitido
        echo json_encode(['error' => 'Método não permitido']);
    }
});

// Executa a rota correspondente
$router->dispatch();
