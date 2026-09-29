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
$faseAtual = 1; // Este arquivo representa a fase 1

// Atualiza a fase no banco de dados, se necessário
$novaFase = 2; // Próxima fase no fluxo
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
    <title>Ovo do Espelho Mágico - Segmentação</title>
    <style>
        /* Fonte e Estilo Geral */
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
            background: rgba(18, 47, 148, 1);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #333;
        }

        #game-container {
            background-color: #fff;
            padding: 20px 40px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 90%;
            max-width: 650px;
        }

        h1 {
            color: rgb(73, 72, 87);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 2.2em;
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

        .hidden {
            display: none !important;
        }

        /* --- Estilo do Espelho/Visual da Palavra --- */
        #word-area {
            margin-bottom: 25px;
        }

        #image-display img {
            max-width: 100%;
            height: auto;
            max-height: 150px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }

        #word-slots-container {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .syllable-slot,
        .syllable-fixed {
            width: 90px;
            height: 60px;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.8em;
            font-weight: bold;
            color: #333;
        }

        .syllable-slot {
            border: 3px dashed rgba(18, 47, 148, 1);
        }

        .syllable-fixed {
            pointer-events: none;
        }

        .syllable-slot.filled {
            background-color: rgb(0, 44, 140);
            color: white;
            border-style: solid;
        }

        /* --- Opções de Sílaba (Arrastáveis) --- */
        #syllable-options {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 25px;
        }

        .syllable-option {
            width: 90px;
            height: 60px;
            background-color: #002c8c;
            color: white;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.8em;
            font-weight: bold;
            cursor: grab;
            transition: transform 0.2s, opacity 0.2s;
            user-select: none;
        }

        .syllable-option.used {
            background-color: #ccc;
            color: #666;
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* --- Feedback e Botões --- */
        #feedback {
            font-size: 1.3em;
            font-weight: bold;
            min-height: 30px;
            margin: 20px 0 10px;
        }

        .success {
            color: #28a745;
        }

        .error {
            color: #dc3545;
        }

        button {
            background-color: rgba(18, 47, 148, 1);
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1em;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #audio-button {
            margin-bottom: 15px;
        }

        #next-word-button.hidden {
            display: none;
        }

        /* --- ESTILOS TELA FINAL UNIFICADA (COPIADOS DOS SEUS OUTROS JOGOS) --- */
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
        <h1>Complete a palavra</h1>

        <div id="word-counter" style="font-weight: bold; margin-bottom: 15px; color: var(--magic-purple);"></div>

        <div id="word-area">
            <div id="image-display"></div>
            <br>
            <button id="audio-button">🔊 Ouvir Palavra</button>
            <div id="word-slots-container"></div>
        </div>

        <div id="syllable-options"></div>

        <div id="feedback"></div>

        <button id="next-word-button" class="hidden">Próxima Palavra!</button>
    </div>

    <div id="end-game-screen" class="hidden">
        <div id="end-game-box">
            <h2>Parabéns!</h2>
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
        const words = [{
                word: "CASA",
                syllables: ["CA", "SA"],
                missingIndex: 0,
                imagePath: "imagens/casa.png"
            },
            {
                word: "BOLA",
                syllables: ["BO", "LA"],
                missingIndex: 1,
                imagePath: "imagens/bola.png"
            },
            {
                word: "FOCA",
                syllables: ["FO", "CA"],
                missingIndex: 0,
                imagePath: "imagens/foca.png"
            },
            {
                word: "LUVA",
                syllables: ["LU", "VA"],
                missingIndex: 1,
                imagePath: "imagens/luva.webp"
            },
            {
                word: "MACACO",
                syllables: ["MA", "CA", "CO"],
                missingIndex: 1,
                imagePath: "imagens/macaco.webp"
            },
            {
                word: "SAPATO",
                syllables: ["SA", "PA", "TO"],
                missingIndex: 2,
                imagePath: "imagens/sapato.webp"
            },
            {
                word: "JANELA",
                syllables: ["JA", "NE", "LA"],
                missingIndex: 0,
                imagePath: "imagens/janela.webp"
            },
            {
                word: "ESCOLA",
                syllables: ["ES", "CO", "LA"],
                missingIndex: 1,
                imagePath: "imagens/escola.webp"
            }
        ];

        const distractorSyllables = ["PE", "XI", "DO", "NA", "RI", "JU", "NE", "SO", "VI", "PO"];

        let currentWordIndex = 0;
        let draggedSyllable = null;

        // Elementos do DOM
        const imageDisplay = document.getElementById('image-display');
        const wordSlotsContainer = document.getElementById('word-slots-container');
        const syllableOptionsContainer = document.getElementById('syllable-options');
        const feedbackMessage = document.getElementById('feedback');
        const nextWordButton = document.getElementById('next-word-button');
        const audioButton = document.getElementById('audio-button');
        const wordCounter = document.getElementById('word-counter');
        const gameContainer = document.getElementById('game-container'); // Adicionado
        const endGameScreen = document.getElementById('end-game-screen'); // Adicionado

        function shuffle(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        function updateWordCounter() {
            const totalWords = words.length;
            const currentNumber = currentWordIndex + 1;
            wordCounter.textContent = `Palavra ${currentNumber} de ${totalWords}`;
        }

        function speakWord(text) {
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'pt-BR';
                window.speechSynthesis.speak(utterance);
            } else {
                console.warn('Seu navegador não suporta síntese de voz.');
            }
        }

        function loadWord() {
            feedbackMessage.textContent = '';
            feedbackMessage.className = '';
            nextWordButton.classList.add('hidden');
            wordSlotsContainer.innerHTML = '';
            syllableOptionsContainer.innerHTML = '';
            imageDisplay.innerHTML = '';
            updateWordCounter();

            const current = words[currentWordIndex];
            const correctSyllable = current.syllables[current.missingIndex];

            // 1. CARREGA A IMAGEM NO DISPLAY
            const imgElement = document.createElement('img');
            // Nota: O caminho da imagem deve ser ajustado para ser relativo a este arquivo PHP
            imgElement.src = `../fases/${current.imagePath}`;
            imgElement.alt = `Imagem de ${current.word}`;
            imageDisplay.appendChild(imgElement);

            // 2. Monta os slots da palavra
            current.syllables.forEach((syllable, index) => {
                let slot;
                if (index === current.missingIndex) {
                    slot = document.createElement('div');
                    slot.classList.add('syllable-slot');
                    slot.dataset.expectedSyllable = correctSyllable;
                    addDropEvents(slot);
                } else {
                    slot = document.createElement('div');
                    slot.classList.add('syllable-fixed');
                    slot.textContent = syllable;
                }
                wordSlotsContainer.appendChild(slot);
            });

            // 3. Monta as opções de sílabas (incluindo distratores)
            const options = [correctSyllable];
            const distractorsToAdd = 3;

            while (options.length < 1 + distractorsToAdd) {
                let randomSyllable = distractorSyllables[Math.floor(Math.random() * distractorSyllables.length)];
                if (!options.includes(randomSyllable) && !current.syllables.includes(randomSyllable)) {
                    options.push(randomSyllable);
                }
            }

            shuffle(options).forEach(syllable => {
                const option = document.createElement('div');
                option.classList.add('syllable-option');
                option.textContent = syllable;
                option.draggable = true;
                syllableOptionsContainer.appendChild(option);
                addDragEvents(option);
            });

            audioButton.onclick = () => speakWord(current.word);
        }

        function addDragEvents(element) {
            element.addEventListener('dragstart', (e) => {
                if (element.classList.contains('used')) {
                    e.preventDefault();
                    return;
                }
                draggedSyllable = element;
                setTimeout(() => element.classList.add('dragging'), 0);
            });

            element.addEventListener('dragend', () => {
                if (draggedSyllable) {
                    draggedSyllable.classList.remove('dragging');
                }
            });
        }

        function addDropEvents(element) {
            element.addEventListener('dragover', (e) => {
                e.preventDefault();
            });
            element.addEventListener('dragleave', (e) => {});

            element.addEventListener('drop', (e) => {
                e.preventDefault();

                if (element.textContent !== '' || !draggedSyllable) return;

                const droppedSyllableText = draggedSyllable.textContent;
                const expectedSyllableText = element.dataset.expectedSyllable;

                if (droppedSyllableText === expectedSyllableText) {
                    element.textContent = droppedSyllableText;
                    element.classList.add('filled');
                    draggedSyllable.classList.add('used');
                    draggedSyllable.draggable = false;

                    showFeedback('Excelente! Você completou a palavra!', 'success');
                    speakWord(words[currentWordIndex].word);
                    nextWordButton.classList.remove('hidden');

                } else {
                    showFeedback('Ops! Não foi essa a sílaba. Tente outra!', 'error');
                }
                draggedSyllable = null;
            });
        }

        function showFeedback(message, type) {
            feedbackMessage.textContent = message;
            feedbackMessage.className = '';
            feedbackMessage.classList.add(type);
        }

        // --- Lógica de Avanço e Fim de Jogo Modificada ---
        nextWordButton.addEventListener('click', () => {
            currentWordIndex++;
            if (currentWordIndex < words.length) {
                loadWord();
            } else {
                // FIM DE JOGO: Mostra a tela final unificada
                gameContainer.classList.add('hidden');
                endGameScreen.classList.remove('hidden');

                // --- ADICIONE O CÓDIGO DO ÁUDIO AQUI ---
                try {
                    var audio = document.getElementById('somDeVitoria');
                    audio.currentTime = 0; // Reinicia o áudio
                    audio.play();
                } catch (e) {
                    console.error("Erro ao tocar o áudio:", e);
                }
                // --- FIM DO CÓDIGO DO ÁUDIO ---

                // O feedback original 'Parabéns! O Espelho Mágico te deu uma recompensa! (Fim da fase)'
                // foi substituído pela tela final visualmente mais agradável.
            }
        });

        // --- INICIA O JOGO ---
        shuffle(words);
        loadWord();
    </script>
</body>

</html>