<?php
session_start();

// Verifica se o ID do aluno está definido na sessão
if (!isset($_SESSION['id_aluno'])) {
    echo "<script>alert('ID do aluno não encontrado na sessão');window.location.href='../index.html';</script>";
    exit;
}

include "../php/conexao.php";

// Obtém o ID do aluno
$id_aluno = $_SESSION['id_aluno'];

// Define qual fase este arquivo representa
$faseAtual = 2; // Este arquivo representa a fase 1

// Atualiza a fase no banco de dados, se necessário
$novaFase = 3; // Próxima fase no fluxo
if (isset($_SESSION['id_aluno'])) {
    $id_aluno = $_SESSION['id_aluno'];
    $con = @new mysqli("localhost", "root", "", "educamente");

    if (!$con->connect_error) {
        if (isset($_SESSION['fase_atual']) && $_SESSION['fase_atual'] < $novaFase) {
            $con->query("UPDATE progresso SET fase_atual = $novaFase WHERE id_aluno = $id_aluno");
            $_SESSION['fase_atual'] = $novaFase;
        }
        $con->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sílaba Mágica</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800;900&display=swap');

        :root {
            --azul-fundo: rgba(18, 47, 148, 1);
            --azul-claro: #0659b2ff;
            --verde-acerto: #28a745;
            --vermelho-erro: #dc3545;
            --amarelo-brilhante: #ffc107;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--azul-fundo);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }

        #game-container {
            background-color: #fff;
            padding: 20px 80px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 1000px;
        }

        h1 {
            color: #002c8c;
            font-size: 2.2em;
            margin-bottom: 10px;
        }

        #a {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            font-size: 28px;
            font-weight: bold;
            text-decoration: none;
        }

        #syllable-focus {
            font-size: 3em;
            font-weight: 900;
            color: #02194cff;
            background-color: #5f97ffac;
            padding: 10px 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: inline-block;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* --- Cartões de Opções: Layout 3x2 responsivo --- */
        #options-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .word-card {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 7px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s, background-color 0.2s;
            text-align: center;
            user-select: none;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 150px;
            /* Adicionado para evitar quebras de altura */
        }

        .word-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .word-card img {
            max-width: 100%;
            height: 80px;
            object-fit: contain;
            margin: 0 auto 10px auto;
            /* Centraliza a imagem */
            border-radius: 5px;
        }

        .word-card p {
            font-size: 1.5em;
            font-weight: 700;
            margin: 0;
            color: #333;
        }

        /* Feedback visual */
        .word-card.correct {
            background-color: var(--verde-acerto);
            border-color: var(--verde-acerto);
            color: white;
            pointer-events: none;
        }

        .word-card.incorrect {
            background-color: var(--vermelho-erro);
            border-color: var(--vermelho-erro);
            animation: shake 0.5s;
        }

        .word-card.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-5px);
            }

            40%,
            80% {
                transform: translateX(5px);
            }
        }

        /* --- FIM DE JOGO E FEEDBACK --- */
        #feedback-area {
            font-size: 1em;
            font-weight: bold;
            min-height: 30px;
            margin: 20px 0;
        }

        .success {
            color: var(--verde-acerto);
        }

        .error {
            color: var(--vermelho-erro);
        }

        #next-button {
            background-color: var(--azul-claro);
            color: white;
            border: none;
            padding: 10px 25px;
            font-size: 1.2em;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: -10px;
        }

        #next-button:hover {
            background-color: #0056b3;
        }

        .hidden {
            display: none !important;
        }

        /* --- TELAS DE FIM DE JOGO (Modal style) --- */
        #end-game-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(18, 47, 148, 0.9);
            z-index: 100;
        }

        #end-game-box {
            background-color: white;
            color: #333;
            border-radius: 15px;
            padding: 40px 50px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        #end-game-box h2 {
            font-size: 2.5em;
            color: #007bff;
            margin: 0;
        }

        #end-game-box p {
            font-size: 1.5em;
            color: #343a40;
            margin-top: 5px;
        }

        #end-game-box img {
            max-width: 180px;
            height: auto;
            margin: 20px 0;
        }

        #end-game-box a {
            background-color: #007bff;
            color: white;
            padding: 12px 35px;
            font-size: 1.2em;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s, transform 0.2s;
        }

        #end-game-box a:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        @keyframes popIn {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }

            80% {
                transform: scale(1.05);
                opacity: 1;
            }

            100% {
                transform: scale(1);
            }
        }

        /* --- RESPONSIVIDADE --- */
        @media (max-width: 850px) {
            #options-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 500px) {
            #options-container {
                grid-template-columns: 1fr;
            }

            #game-container {
                padding: 15px 10px;
            }

            .word-card img {
                height: 60px;
            }

            .word-card p {
                font-size: 1.2em;
            }

            #end-game-box {
                padding: 30px 20px;
                max-width: 90%;
            }
        }
    </style>
    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
        new window.VLibras.Widget("https://vlibras.gov.br/app");
    </script>
    <script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>
</head>

<body>
    <a id="a" href="caminho.php">←</a>
    <div id="game-container">
        <h1>Qual Palavra Começa com a Sílaba:</h1>
        <div id="syllable-focus"></div>
        <div id="word-counter" style="font-weight: bold; margin-bottom: 15px;"></div>

        <div id="options-container">
        </div>
        <div id="feedback-area"></div>
        <button id="next-button" class="hidden">Próximo Desafio</button>

    </div>

    <div id="end-game-screen" class="hidden">
        <div id="end-game-box">
            <h2>Fase Completa!</h2>
            <p>Você completou o Desafio!</p>
            <audio id="somDeVitoria" preload="auto">
                <source src="vitoria.mp3" type="audio/mpeg">
            </audio>
            <img src="../fases/imagens/15.png" alt="Fim de Jogo" />
            <a href="caminho.php">Continuar</a>
        </div>
    </div>


    <script>
        // --- DADOS DO JOGO ---
        const GAME_DATA = [{
                syllable: "BO",
                correctWords: [{
                        word: "BOLA",
                        image: "../fases/imagens/bola.png"
                    },
                    {
                        word: "BOCA",
                        image: "../fases/imagens/boca.png"
                    },
                    {
                        word: "BOLO",
                        image: "../fases/imagens/bolo.png"
                    }
                ],
                distractors: [{
                        word: "DADO",
                        image: "../fases/imagens/dado.png"
                    },
                    {
                        word: "COLA",
                        image: "../fases/imagens/cola.png"
                    },
                    {
                        word: "MALA",
                        image: "../fases/imagens/mala.png"
                    }
                ]
            },
            {
                syllable: "MA",
                correctWords: [{
                        word: "MACACO",
                        image: "../fases/imagens/macaco.webp"
                    },
                    {
                        word: "MAMÃO",
                        image: "../fases/imagens/mamao.png"
                    },
                    {
                        word: "MALA",
                        image: "../fases/imagens/mala.png"
                    }
                ],
                distractors: [{
                        word: "PATO",
                        image: "../fases/imagens/pato.png"
                    },
                    {
                        word: "FACA",
                        image: "../fases/imagens/faca.png"
                    },
                    {
                        word: "RATO",
                        image: "../fases/imagens/rato.png"
                    }
                ]
            },
            {
                syllable: "PE",
                correctWords: [{
                        word: "PETECA",
                        image: "../fases/imagens/peteca.png"
                    },
                    {
                        word: "PERA",
                        image: "../fases/imagens/pera.png"
                    },
                    {
                        word: "PEIXE",
                        image: "../fases/imagens/peixe.png"
                    }
                ],
                distractors: [{
                        word: "CACHORRO",
                        image: "../fases/imagens/cachorro.png"
                    },
                    {
                        word: "GATO",
                        image: "../fases/imagens/gato.png"
                    },
                    {
                        word: "CAIXA",
                        image: "../fases/imagens/caixa.png"
                    }
                ]
            }
        ];

        let currentChallengeIndex = 0;
        let correctGuesses = 0;
        let guessesRemaining = 3;

        // Elementos do DOM
        const syllableFocus = document.getElementById('syllable-focus');
        const optionsContainer = document.getElementById('options-container');
        const feedbackArea = document.getElementById('feedback-area');
        const nextButton = document.getElementById('next-button');
        const wordCounter = document.getElementById('word-counter');
        const gameContainer = document.getElementById('game-container');
        const endGameScreen = document.getElementById('end-game-screen');


        function shuffle(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        function loadChallenge() {
            if (currentChallengeIndex >= GAME_DATA.length) {
                // Ao fim de todos os desafios, chame a tela de fim de jogo
                showEndGame();
                return;
            }
            // ... (Resto da função loadChallenge, não alterada)
            const challenge = GAME_DATA[currentChallengeIndex];
            syllableFocus.textContent = challenge.syllable;
            optionsContainer.innerHTML = '';
            feedbackArea.textContent = '';
            nextButton.classList.add('hidden');
            correctGuesses = 0;
            guessesRemaining = challenge.correctWords.length;
            updateWordCounter();

            const allOptions = shuffle([...challenge.correctWords, ...challenge.distractors]);

            allOptions.forEach(option => {
                const card = document.createElement('div');
                card.classList.add('word-card');
                card.dataset.word = option.word;
                card.dataset.isCorrect = challenge.correctWords.some(cw => cw.word === option.word);

                card.innerHTML = `
                    <img src="${option.image}" alt="${option.word}">
                    <p>${option.word}</p>
                `;

                card.addEventListener('click', () => handleCardClick(card, challenge.syllable));
                optionsContainer.appendChild(card);
            });
        }

        function updateWordCounter() {
            wordCounter.textContent = `Desafio ${currentChallengeIndex + 1} de ${GAME_DATA.length}`;
        }

        // FUNÇÃO DE ÁUDIO REMOVIDA
        function speakWord(text) {
            // A função de fala (Text-to-Speech) foi removida conforme solicitado.
        }

        function handleCardClick(card, correctSyllable) {
            if (card.classList.contains('correct') || card.classList.contains('incorrect') || card.classList.contains('disabled')) return;

            const isCorrect = card.dataset.isCorrect === 'true';

            if (isCorrect) {
                card.classList.add('correct');
                card.classList.add('disabled');
                // speakWord(card.dataset.word); // Chamada de áudio removida
                correctGuesses++;

                feedbackArea.textContent = `Ótimo! ${card.dataset.word} começa com ${correctSyllable}!`;
                feedbackArea.classList.remove('error');
                feedbackArea.classList.add('success');

                if (correctGuesses === guessesRemaining) {
                    feedbackArea.textContent = `Parabéns! Você encontrou todas as palavras com a sílaba ${correctSyllable}!`;
                    nextButton.classList.remove('hidden');
                    disableAllCards();
                }

            } else {
                card.classList.add('incorrect');
                // speakWord("Ops, tente de novo."); // Chamada de áudio removida
                feedbackArea.textContent = `A palavra ${card.dataset.word} não começa com ${correctSyllable}.`;
                feedbackArea.classList.remove('success');
                feedbackArea.classList.add('error');

                setTimeout(() => {
                    card.classList.remove('incorrect');
                }, 1000);
            }
        }

        function disableAllCards() {
            document.querySelectorAll('.word-card').forEach(card => {
                card.classList.add('disabled');
            });
        }

        // Função para mostrar a tela final
        function showEndGame() {
            gameContainer.classList.add('hidden');
            endGameScreen.classList.remove('hidden');

            try {
                var audio = document.getElementById('somDeVitoria');
                audio.currentTime = 0; // Reinicia o áudio
                audio.play();
            } catch (e) {
                console.error("Erro ao tocar o áudio:", e);
            }
        }

        nextButton.addEventListener('click', () => {
            currentChallengeIndex++;
            loadChallenge();
        });

        // INICIA O JOGO
        shuffle(GAME_DATA);
        loadChallenge();
    </script>
</body>

</html>