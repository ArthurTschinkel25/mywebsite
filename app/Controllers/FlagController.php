<?php
namespace app\Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use app\Models\Flag;

class FlagController
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../../app/Views');
        $this->twig = new Environment($loader);
    }

    public function handleRequest()
    {
        $jsonPath = __DIR__ . '/../../paises.json';
        $difficulty = $_GET['dificuldade'] ?? null; // Mude para 'dificuldade' para combinar com seu parâmetro GET

        if ($difficulty) {
            $flags = Flag::getFlagsByDifficulty($jsonPath, $difficulty);
            shuffle($flags);
            $gameFlags = array_slice($flags, 0, 4);
            $correctFlag = $gameFlags[array_rand($gameFlags)];

            echo $this->twig->render('bandeiras/tableBandeiras.html', [
                'flags' => $gameFlags,
                'correctFlag' => $correctFlag,
                'base_url' => str_replace('/index.php', '', $_SERVER['PHP_SELF']),
                'showGame' => true,
                'selectedDifficulty' => $difficulty
            ]);
        } else {
            echo $this->twig->render('bandeiras/tableBandeiras.html', [
                'showGame' => false
            ]);
        }
    }
}