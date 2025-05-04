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

        // Inicializa as variáveis de sessão se não existirem
        if (!isset($_SESSION['quantidadeDeJogos'])) {
            $_SESSION['quantidadeDeJogos'] = 0;
        }
        if (!isset($_SESSION['quantidadeCorretas'])) {
            $_SESSION['quantidadeCorretas'] = 0;
        }
        if (!isset($_SESSION['rodadaAtual'])) {
            $_SESSION['rodadaAtual'] = 0;
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
            // Reseta os contadores após exibir a mensagem
            $_SESSION['quantidadeDeJogos'] = 0;
            $_SESSION['quantidadeCorretas'] = 0;
            return;
        }

        // Se não há rodada, exibe tela neutra
        echo $this->twig->render('bandeiras/tableBandeiras.html', [
            'showGame' => false,
            'exibirFimDeJogo' => $exibirFimDejogo
        ]);
    }

    public function salvarPontuacao(bool $acertou)
    {
        session_start();
        if (!isset($_SESSION['quantidadeCorretas'])) {
            $_SESSION['quantidadeCorretas'] = 0;
        }

        if ($acertou) {
            $_SESSION['quantidadeCorretas']++;
        }
    }

    private function iniciarNovoJogo(string $difficulty, string $jsonPath)
    {
        $_SESSION['quantidadeDeJogos'] = 0;
        $_SESSION['selectedDifficulty'] = $difficulty;
        $_SESSION['quantidadeCorretas'] = 0;

        $this->sortearNovaRodada($jsonPath, $difficulty);

        header("Location: /bandeiras");
        exit;
    }

    private function jogarRodada(string $jsonPath)
    {
        $_SESSION['rodadaAtual']++;

        // Renderiza o jogo atual antes de incrementar o contador
        echo $this->twig->render('bandeiras/tableBandeiras.html', [
            'flags' => $_SESSION['flags'],
            'correctFlag' => $_SESSION['correctFlag'],
            'selectedDifficulty' => $_SESSION['selectedDifficulty'],
            'showGame' => true,
            'exibirFimDeJogo' => false,
            'rodadaAtual' => $_SESSION['rodadaAtual']
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

    private function renderizarFimDeJogo(bool $exibirFimDeJogo, int $totalCorretas)
    {
        echo $this->twig->render('bandeiras/tableBandeiras.html', [
            'showGame' => false,
            'totalCorretas' => $totalCorretas,
            'exibirFimDeJogo' => $exibirFimDeJogo,
            'mensagem' => 'Você acertou ' . $totalCorretas . ' de 3 bandeiras! Atualize a página para reiniciar.',
        ]);
    }

    private function dadosDaRodadaEstaoDisponiveis(): bool
    {
        return isset($_SESSION['flags'], $_SESSION['correctFlag'], $_SESSION['selectedDifficulty']);
    }

    private function limparDadosDeRodada()
    {
        unset($_SESSION['flags'], $_SESSION['correctFlag'], $_SESSION['selectedDifficulty'], $_SESSION['rodadaAtual']);
    }
}