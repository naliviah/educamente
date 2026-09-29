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

// Define qual fase este arquivo representa no início
$faseAtual = 3;

// --- Lógica de Salvamento (AJAX) ---
// Esta seção lida com as chamadas do JavaScript para salvar o progresso no banco de dados.

// Ação para salvar a conclusão da PARTE 1 (SÍLABAS) e avançar para a fase 4
if (isset($_GET['action']) && $_GET['action'] == 'save_part1') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['id_aluno'])) {
        echo json_encode(['status' => 'error', 'message' => 'Sessão não encontrada.']);
        exit;
    }

    $id_aluno = $_SESSION['id_aluno'];
    $novaFase = 3; // Ao concluir a parte 1, o progresso vai para a fase 4

    // Só atualiza se o progresso atual for menor que a nova fase
    if ($_SESSION['fase_atual'] < $novaFase) {
        $con = new mysqli("localhost", "root", "", "educamente");
        if ($con->connect_error) {
            echo json_encode(['status' => 'error', 'message' => 'Erro de conexão.']);
            exit;
        }

        $stmt = $con->prepare("UPDATE progresso SET fase_atual = ? WHERE id_aluno = ?");
        $stmt->bind_param("ii", $novaFase, $id_aluno);

        if ($stmt->execute()) {
            $_SESSION['fase_atual'] = $novaFase; // Atualiza a sessão
            echo json_encode(['status' => 'success', 'message' => 'Parte 1 concluída!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar.']);
        }
        $stmt->close();
        $con->close();
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Parte 1 já completa.']);
    }
    exit;
}

// Ação para salvar a conclusão da PARTE 2 (COMPLETAR PALAVRA) e avançar para a fase 5
if (isset($_GET['action']) && $_GET['action'] == 'save_part2') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['id_aluno'])) {
        echo json_encode(['status' => 'error', 'message' => 'Sessão não encontrada.']);
        exit;
    }

    $id_aluno = $_SESSION['id_aluno'];
    $novaFase = 4; // Ao concluir a parte 2, o progresso vai para a fase 5

    if ($_SESSION['fase_atual'] < $novaFase) {
        $con = new mysqli("localhost", "root", "", "educamente");
        if ($con->connect_error) {
            echo json_encode(['status' => 'error', 'message' => 'Erro de conexão.']);
            exit;
        }

        $stmt = $con->prepare("UPDATE progresso SET fase_atual = ? WHERE id_aluno = ?");
        $stmt->bind_param("ii", $novaFase, $id_aluno);

        if ($stmt->execute()) {
            $_SESSION['fase_atual'] = $novaFase; // Atualiza a sessão
            echo json_encode(['status' => 'success', 'message' => 'Fase concluída!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar.']);
        }
        $stmt->close();
        $con->close();
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Fase já completa.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fase 3 - Desafios Mágicos</title>

    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
        new window.VLibras.Widget('https://vlibras.gov.br/app');
    </script>

    <style>
        /* --- ESTILOS GERAIS --- */
        body {
            font-family: Arial, sans-serif;
            background-color: #122f94;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }

        .hidden {
            display: none !important;
        }

        /* --- ESTILOS PARTE 1: JOGO DE SÍLABAS --- */
        #game-container-part1 {
            background-color: #fff;
            padding: 20px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 90%;
            max-width: 600px;
        }

        #game-container-part1 h1 {
            color: #224294;
        }

        #image-display {
            font-size: 2.5em;
            font-weight: bold;
            margin: 20px 0;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #word-counter {
            font-size: 1.1em;
            font-weight: bold;
            color: #224294;
            margin-bottom: 10px;
        }

        .slots-container,
        .syllables-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin: 20px 0;
            min-height: 70px;
        }

        .syllable-slot {
            width: 80px;
            height: 60px;
            border: 3px dashed #ccc;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.8em;
            font-weight: bold;
            background-color: #f0f8ff;
        }

        .syllable-option {
            width: 80px;
            height: 60px;
            background-color: #8f84e4;
            border: 2px solid #8994b3;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.8em;
            font-weight: bold;
            color: white;
            cursor: grab;
            user-select: none;
        }

        .dragging {
            opacity: 0.5;
            cursor: grabbing;
        }

        .syllable-slot.filled {
            background-color: #182577;
            border-style: solid;
            color: white;
        }

        .syllable-option.used {
            background-color: #d3d3d3;
            color: #666;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .feedback-message {
            font-size: 1.2em;
            font-weight: bold;
            min-height: 30px;
        }

        .success {
            color: #28a745;
        }

        .error {
            color: #dc3545;
        }

        button {
            background-color: #4756ff;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.2em;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 20px;
        }

        button:hover {
            background-color: #3751e5;
        }

        /* --- ESTILOS PARTE 2: COMPLETAR PALAVRA --- */
        #game-container-part2 {
            background-color: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 90%;
            max-width: 700px;
            color: #333;
        }

        #game-container-part2 h1,
        #game-container-part2 h2 {
            color: #224294;
            margin-top: 0;
            letter-spacing: 4px;
        }

        #game-container-part2 p {
            font-size: 1.2em;
        }

        #game-container-part2 p.sub {
            color: #555;
            margin-top: -10px;
        }

        #challenge-container .container {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        #challenge-container .card {
            background: white;
            color: black;
            border-radius: 16px;
            padding: 30px 20px;
            width: 100px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        #challenge-container .card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        #challenge-container .card p {
            margin: 0;
            font-size: 2.5em;
            font-weight: bold;
        }

        #challenge-image {
            height: 80px;
            margin-bottom: 20px;
        }

        #challenge-image img {
            max-height: 100%;
            max-width: 100%;
        }

        #resultado {
            font-size: 1.5em;
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            min-height: 50px;
        }

        #challenge-container .acerto {
            background-color: #d4edda;
            border: 3px solid #28a745;
        }

        #challenge-container .erro {
            background-color: #f8d7da;
            border: 3px solid #dc3545;
        }

        #congrats-image {
            max-width: 80%;
            height: auto;
            max-height: 250px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <a href="../fases/caminho.php" style="position: absolute; top: 30px; left: 30px; color: white; font-size: 24px; font-weight: bold; text-decoration: none; z-index: 10;">←</a>

    <div id="game-container-part1">
        <h1>Aventura das Sílabas</h1>
        <p>Ajude o Dudu a montar a palavra!</p>
        <div id="word-counter"></div>
        <div id="image-display"></div>
        <div id="word-slots" class="slots-container"></div>
        <hr>
        <div id="syllable-options" class="syllables-container"></div>
        <div id="feedback" class="feedback-message"></div>
        <button id="next-word-button" class="hidden">Próxima Palavra!</button>
    </div>

    <div id="game-container-part2" class="hidden">
        <div id="challenge-container">
            <h1>Complete a Palavra</h1>
            <h2 id="challenge-word"></h2>
            <p class="sub">Clique na letra que falta</p>
            <div id="challenge-image"></div>
            <div class="container" id="challenge-options"></div>
            <div id="resultado"></div>
        </div>
        <div id="final-message-container" class="hidden">
            <img id="congrats-image" src="imagens/15.png" alt="Dragão fofo de parabéns">
            <h1>Parabéns!</h1>
            <p>Você concluiu a Fase 3 com sucesso!</p>
            <audio id="somDeVitoria" preload="auto">
                <source src="vitoria.mp3" type="audio/mpeg">
            </audio>
            <a href="../fases/caminho.php" style="font-size: 1.5em; font-weight: bold; text-decoration: none; color:#4756ff; margin-top: 15px; display: inline-block;">
                Continuar →
            </a>
        </div>
    </div>

    <script>
        // --- GERAL ---
        const gamePart1 = document.getElementById('game-container-part1');
        const gamePart2 = document.getElementById('game-container-part2');

        // --- LÓGICA PARTE 1: JOGO DE SÍLABAS ---
        const words = [{
                word: "BOLA",
                syllables: ["BO", "LA"],
                display: "⚽"
            },
            {
                word: "CASA",
                syllables: ["CA", "SA"],
                display: "🏠"
            },
            {
                word: "GATO",
                syllables: ["GA", "TO"],
                display: "🐈"
            },
            {
                word: "LUA",
                syllables: ["LU", "A"],
                display: "🌙"
            },
            {
                word: "MACACO",
                syllables: ["MA", "CA", "CO"],
                display: "🐒"
            }
        ];
        const distractorSyllables = ["PE", "XI", "DA", "FO", "RI", "JU", "NE", "SO"];
        let currentWordIndex = 0;
        let correctlyPlacedCount = 0;
        let draggedSyllable = null;

        const imageDisplay = document.getElementById('image-display');
        const wordSlotsContainer = document.getElementById('word-slots');
        const syllableOptionsContainer = document.getElementById('syllable-options');
        const feedbackMessage = document.getElementById('feedback');
        const nextWordButton = document.getElementById('next-word-button');
        const wordCounter = document.getElementById('word-counter');

        function shuffle(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        function updateWordCounter() {
            wordCounter.textContent = `Palavra ${currentWordIndex + 1} de ${words.length}`;
        }

        function loadWord() {
            correctlyPlacedCount = 0;
            feedbackMessage.textContent = '';
            feedbackMessage.className = 'feedback-message';
            nextWordButton.classList.add('hidden');
            wordSlotsContainer.innerHTML = '';
            syllableOptionsContainer.innerHTML = '';
            updateWordCounter();

            const currentWord = words[currentWordIndex];
            imageDisplay.textContent = currentWord.display;

            currentWord.syllables.forEach((syllable) => {
                const slot = document.createElement('div');
                slot.classList.add('syllable-slot');
                slot.dataset.expectedSyllable = syllable;
                wordSlotsContainer.appendChild(slot);
                addDropEvents(slot);
            });

            const options = [...currentWord.syllables, ...distractorSyllables.slice(0, 2)];
            shuffle(options).forEach(syllable => {
                const option = document.createElement('div');
                option.classList.add('syllable-option');
                option.textContent = syllable;
                option.draggable = true;
                syllableOptionsContainer.appendChild(option);
                addDragEvents(option);
            });
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
                draggedSyllable.classList.remove('dragging');
            });
        }

        function addDropEvents(element) {
            element.addEventListener('dragover', (e) => e.preventDefault());
            element.addEventListener('drop', (e) => {
                e.preventDefault();
                if (element.textContent !== '') return;
                const droppedSyllableText = draggedSyllable.textContent;
                const expectedSyllableText = element.dataset.expectedSyllable;
                if (droppedSyllableText === expectedSyllableText) {
                    element.textContent = droppedSyllableText;
                    element.classList.add('filled');
                    draggedSyllable.classList.add('used');
                    draggedSyllable.draggable = false;
                    correctlyPlacedCount++;
                    showFeedback('Muito bem!', 'success');
                    if (correctlyPlacedCount === words[currentWordIndex].syllables.length) {
                        showFeedback(`Você formou "${words[currentWordIndex].word}"!`, 'success');
                        nextWordButton.classList.remove('hidden');
                    }
                } else {
                    showFeedback('Ops! Tente outra sílaba.', 'error');
                }
            });
        }

        function showFeedback(message, type) {
            feedbackMessage.textContent = message;
            feedbackMessage.className = 'feedback-message ' + type;
        }

        function transitionToPart2() {
            fetch('?action=save_part1')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Progresso da Parte 1 salvo!');
                        gamePart1.classList.add('hidden');
                        gamePart2.classList.remove('hidden');
                        initializePart2();
                    } else {
                        showFeedback('Erro ao salvar progresso. Tente novamente.', 'error');
                    }
                }).catch(error => console.error('Erro na requisição:', error));
        }

        nextWordButton.addEventListener('click', () => {
            currentWordIndex++;
            if (currentWordIndex < words.length) {
                loadWord();
            } else {
                gamePart1.innerHTML = '<h1>Parabéns!</h1><p>Você completou a primeira parte. <br>Prepare-se para o próximo desafio!</p>';
                setTimeout(transitionToPart2, 2000);
            }
        });

        // --- LÓGICA PARTE 2: COMPLETAR PALAVRA (FINALIZADA) ---
        const challenges = [{
                word: "FOGO",
                display: "F O G _",
                image: "https://static.vecteezy.com/system/resources/previews/001/188/566/non_2x/fire-png.png",
                options: ["A", "O", "E"],
                answer: "O"
            },
            {
                word: "PATO",
                display: "_ A T O",
                image: "https://static.vecteezy.com/system/resources/previews/019/045/696/non_2x/duck-graphic-clipart-design-free-png.png",
                options: ["P", "B", "L"],
                answer: "P"
            },
            {
                word: "TEIA",
                display: "T _ I A",
                image: "https://images.vexels.com/media/users/3/202771/isolated/preview/3d318494eea2bc4ffa351b50da3c7195-icone-de-linha-de-teia-de-aranha.png",
                options: ["E", "A", "I"],
                answer: "E"
            },
            {
                word: "DADO",
                display: "D A _ O",
                image: "https://images.vexels.com/media/users/3/332338/isolated/preview/a0b00b708fa5aee9ee154d74d218444d-dois-icones-de-dados-de-jogo.png",
                options: ["V", "D", "T"],
                answer: "D"
            },
            {
                word: "NAVIO",
                display: "N A V I _",
                image: "https://images.vexels.com/media/users/3/265848/isolated/preview/c612f25b019e389238cd6e6c5eca51a0-navio-barco-plano.png",
                options: ["U", "A", "O"],
                answer: "O"
            }
        ];
        let currentChallengeIndex = 0;
        let processingClick = false; // Flag para controlar cliques

        const challengeContainer = document.getElementById('challenge-container');
        const finalMessageContainer = document.getElementById('final-message-container');
        const challengeWord = document.getElementById('challenge-word');
        const challengeImage = document.getElementById('challenge-image');
        const challengeOptionsContainer = document.getElementById('challenge-options');
        const resultadoDiv = document.getElementById('resultado');

        function loadChallenge(index) {
            const currentChallenge = challenges[index];
            resultadoDiv.innerHTML = '';
            challengeOptionsContainer.innerHTML = '';
            challengeWord.textContent = currentChallenge.display;
            challengeImage.innerHTML = `<img src="${currentChallenge.image}" alt="${currentChallenge.word}">`;

            shuffle(currentChallenge.options).forEach(option => {
                const card = document.createElement('div');
                card.className = 'card';
                card.innerHTML = `<p>${option}</p>`;
                card.dataset.resposta = (option === currentChallenge.answer) ? 'certa' : 'errada';
                challengeOptionsContainer.appendChild(card);
            });
            addCardClickEvents();
        }

        function addCardClickEvents() {
            const cards = challengeOptionsContainer.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('click', handleCardClick);
            });
        }

        function handleCardClick(event) {
            if (processingClick) return; // Se estiver processando, ignora o clique
            processingClick = true; // Bloqueia novos cliques

            const selectedCard = event.currentTarget;
            const isCorrect = selectedCard.dataset.resposta === 'certa';

            if (isCorrect) {
                selectedCard.classList.add("acerto");
                resultadoDiv.innerHTML = `<b class="success">Certo!</b>`;

                setTimeout(() => {
                    currentChallengeIndex++;
                    if (currentChallengeIndex < challenges.length) {
                        loadChallenge(currentChallengeIndex);
                    } else {
                        challengeContainer.classList.add('hidden');
                        finalMessageContainer.classList.remove('hidden');

                        // --- ADICIONE O CÓDIGO DO ÁUDIO AQUI ---
                        try {
                            var audio = document.getElementById('somDeVitoria');
                            audio.currentTime = 0; // Reinicia o áudio (caso precise)
                            audio.play();
                        } catch (e) {
                            console.error("Erro ao tocar o áudio:", e);
                        }
                        // --- FIM DO CÓDIGO DO ÁUDIO ---

                        saveFinalProgress();
                    }
                    processingClick = false; // Libera cliques para o próximo desafio
                }, 1500);

            } else {
                selectedCard.classList.add("erro");
                resultadoDiv.innerHTML = `<b class="error">Errado! Tente novamente.</b>`;
                // Remove o listener do card errado para não poder clicar de novo nele
                selectedCard.removeEventListener('click', handleCardClick);

                setTimeout(() => {
                    processingClick = false; // Libera para tentar outras opções
                }, 500);
            }
        }

        function saveFinalProgress() {
            fetch('?action=save_part2')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Progresso final salvo!');
                    } else {
                        console.error('Erro ao salvar progresso final:', data.message);
                    }
                });
        }

        function initializePart2() {
            currentChallengeIndex = 0;
            shuffle(challenges);
            loadChallenge(currentChallengeIndex);
        }

        // --- INICIA O JOGO ---
        shuffle(words);
        loadWord();
    </script>
</body>

</html>