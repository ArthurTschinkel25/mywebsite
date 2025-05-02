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
        session_start();
        $jsonPath = __DIR__ . '/../../paises.json';
        $difficulty = $_GET['dificuldade'] ?? null;
        $exibirFimDejogo = false;

        // Inicializa a quantidade de jogos se não existir
        if (!isset($_SESSION['quantidadeDeJogos'])) {
            $_SESSION['quantidadeDeJogos'] = 0;
        }
        if (!isset($_SESSION['quantidadeCorretas'])) {
            $_SESSION['quantidadeCorretas'] = 0;
        }

        // Quando o usuário escolhe uma dificuldade
        if ($difficulty) {
            $this->iniciarNovoJogo($difficulty, $jsonPath);
            return;
        }

        // Se existe uma rodada em andamento
        if ($this->dadosDaRodadaEstaoDisponiveis()) {
            $this->jogarRodada($jsonPath);
            return;
        }

        // Verifica se já jogou 3 vezes APÓS processar a rodada atual
        if ($_SESSION['quantidadeDeJogos'] >= 3) {
            $exibirFimDejogo = true;
            $totalCorretas = $_SESSION['quantidadeCorretas'];
            $this->renderizarFimDeJogo($exibirFimDejogo, $totalCorretas);
            // Reseta o contador após exibir a mensagem
            $_SESSION['quantidadeDeJogos'] = 0;
            return;
        }

        // Se não há rodada, exibe tela neutra
        echo $this->twig->render('bandeiras/tableBandeiras.html', [
            'showGame' => false,
            'exibirFimDeJogo' => $exibirFimDejogo
        ]);
    }

    private function atualizarPlacar(bool $acertou)
    {
        if (!isset($_SESSION['quantidadeCorretas'])) {
            $_SESSION['quantidadeCorretas'] = 0;
        }

        if ($acertou) {
            $_SESSION['quantidadeCorretas']++;
        }
    }
    private function iniciarNovoJogo(string $difficulty, string $jsonPath)
    {
        $_SESSION['quantidadeDeJogos'] = 0; // Resetar contador ao iniciar novo jogo
        $_SESSION['selectedDifficulty'] = $difficulty;

        $this->sortearNovaRodada($jsonPath, $difficulty);

        header("Location: /bandeiras");
        exit;
    }

    private function jogarRodada(string $jsonPath)
    {
        // Renderiza o jogo atual antes de incrementar o contador
        echo $this->twig->render('bandeiras/tableBandeiras.html', [
            'flags' => $_SESSION['flags'],
            'correctFlag' => $_SESSION['correctFlag'],
            'selectedDifficulty' => $_SESSION['selectedDifficulty'],
            'showGame' => true,
            'exibirFimDeJogo' => false // Garante que não mostra mensagem de fim de jogo
        ]);

        // Incrementa o contador após renderizar
        $_SESSION['quantidadeDeJogos']++;

        if ($_SESSION['quantidadeDeJogos'] < 3) {
            $this->sortearNovaRodada($jsonPath, $_SESSION['selectedDifficulty']);
        } else {
            $this->limparDadosDeRodada();
        }
    }

    private function sortearNovaRodada(string $jsonPath, string $difficulty)
    {
        $flags = Flag::getFlagsByDifficulty($jsonPath, $difficulty);
        shuffle($flags);
        $gameFlags = array_slice($flags, 0, 4);
        $correctFlag = $gameFlags[array_rand($gameFlags)];

        $_SESSION['flags'] = $gameFlags;
        $_SESSION['correctFlag'] = $correctFlag;
    }

    private function renderizarFimDeJogo(bool $exibirFimDeJogo, string $totalCorretas)
    {
        $_SESSION['totalCorretas'] = $totalCorretas;

        echo $this->twig->render('bandeiras/tableBandeiras.html', [
            'showGame' => false,
            'totalCorretas' => $totalCorretas,
            'exibirFimDeJogo' => $exibirFimDeJogo,
            'mensagem' => 'Você já jogou 3 vezes! Atualize a página para reiniciar.'
        ]);
    }

    private function dadosDaRodadaEstaoDisponiveis(): bool
    {
        return isset($_SESSION['flags'], $_SESSION['correctFlag'], $_SESSION['selectedDifficulty']);
    }

    private function limparDadosDeRodada()
    {
        unset($_SESSION['flags'], $_SESSION['correctFlag'], $_SESSION['selectedDifficulty']);
    }
}