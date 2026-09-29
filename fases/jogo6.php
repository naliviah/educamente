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
// --- LÓGICA DE SALVAMENTO UNIFICADA ---
// Esta seção lida com a chamada do JavaScript para desbloquear a fase final.
// Usamos POST para mais segurança.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'unlock_final_phase') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['id_aluno'])) {
        echo json_encode(['status' => 'error', 'message' => 'Sessão de aluno não encontrada.']);
        exit;
    }

    if (!isset($_POST['nova_fase'])) {
        echo json_encode(['status' => 'error', 'message' => 'Número da fase não fornecido.']);
        exit;
    }

    $id_aluno = $_SESSION['id_aluno'];
    $novaFase = intval($_POST['nova_fase']); // A fase a ser desbloqueada (neste caso, 7)

    // Só atualiza se o progresso atual for menor que a nova fase
    if (isset($_SESSION['fase_atual']) && $_SESSION['fase_atual'] < $novaFase) {
        $con = new mysqli("localhost", "root", "", "educamente");
        if ($con->connect_error) {
            echo json_encode(['status' => 'error', 'message' => 'Erro de conexão com o banco de dados.']);
            exit;
        }

        // Usa prepared statements para segurança
        $stmt = $con->prepare("UPDATE progresso SET fase_atual = ? WHERE id_aluno = ? AND fase_atual < ?");
        $stmt->bind_param("iii", $novaFase, $id_aluno, $novaFase);

        if ($stmt->execute()) {
            $_SESSION['fase_atual'] = $novaFase; // Atualiza a sessão
            echo json_encode(['status' => 'success', 'message' => 'Progresso salvo! Fase ' . $novaFase . ' desbloqueada.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar o progresso.']);
        }
        $stmt->close();
        $con->close();
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Nenhuma atualização necessária.']);
    }
    exit; // Importante: termina o script aqui para não carregar o HTML abaixo
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desafio de Frases</title>
    <style>
        /* --- ESTILOS GERAIS (PADRÃO EDUCAMENTE) --- */
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;800&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #122f94;
            color: white;
            padding: 20px;
            box-sizing: border-box;
            text-align: center;
            margin: 0;
        }

        .hidden {
            display: none !important;
        }

        #back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            font-size: 28px;
            font-weight: bold;
            text-decoration: none;
        }

        .game-container {
            width: 100%;
            max-width: 1000px;
            background-color: #ffffffc0;
            color: #333;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        h1 {
            font-size: 2.5em;
            font-weight: 800;
            color: #122f94;
            margin: 0 0 20px 0;
        }

        .game-container p {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 25px;
        }

        .draggables-container,
        .dropzones-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border-radius: 15px;
            min-height: 100px;
        }

        .dropzones-container {
            background-color: rgba(0, 0, 0, 0.05);
            border: 2px dashed #a0b0ff;
            margin-bottom: 30px;
        }

        .draggable {
            background-color: #f7b733;
            color: #333;
            font-weight: 700;
            font-size: 1.2em;
            padding: 15px 25px;
            cursor: grab;
            border-radius: 10px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
            user-select: none;
        }

        .draggable:active {
            transform: scale(1.05);
            cursor: grabbing;
        }

        .dropzone {
            width: 180px;
            height: 70px;
            border: 2px dashed #ccc;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            color: #aaa;
        }

        .dropzone.hovered {
            border-color: #f7b733;
            background-color: #fff8e1;
        }

        .dropzone.correct {
            border-style: solid;
            border-color: #28a745;
            background-color: #e8f5e9;
        }

        #incorrect-sentence-display {
            font-size: 1.6em;
            font-weight: 700;
            color: #dc3545;
            padding: 15px;
            background-color: #f8d7da;
            border-radius: 10px;
            border: 2px solid #f5c6cb;
        }

        #correct-sentence-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 20px;
            border-radius: 15px;
            min-height: 80px;
            background-color: rgba(0, 0, 0, 0.05);
            border: 2px dashed #a0b0ff;
            margin-bottom: 30px;
        }

        .word-slot {
            font-size: 1.4em;
            font-weight: 700;
            color: #28a745;
            background-color: #e8f5e9;
            padding: 10px 20px;
            border-radius: 8px;
            border: 2px solid #a7d7a9;
        }

        #word-options-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .word-option {
            background-color: #f7b733;
            color: #333;
            font-weight: 700;
            font-size: 1.2em;
            padding: 15px 25px;
            cursor: pointer;
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .word-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }

        .word-option.disabled {
            background-color: #ccc;
            color: #888;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .word-option.incorrect-flash {
            animation: shake 0.5s;
            background-color: #dc3545;
            color: white;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-8px);
            }

            40%,
            80% {
                transform: translateX(8px);
            }
        }

        #message {
            margin-top: 25px;
            font-size: 1.5em;
            font-weight: bold;
            min-height: 50px;
            text-align: center;
            color: white;
        }

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
    <div id="game-content">
        <a id="back-link" href="caminho.php">←</a>
        <div class="game-container">
            <h1 id="game-title"></h1>
            <div id="montar-frase-game" class="hidden">
                <p>Monte a frase nos espaços abaixo usando os cartões amarelos!</p>
                <div id="dropzones-container" class="dropzones-container"></div>
                <div id="draggables-container" class="draggables-container"></div>
            </div>
            <div id="corrigir-frase-game" class="hidden">
                <div id="incorrect-sentence-container">
                    <p>Veja a frase abaixo. Ela não está certa!</p>
                    <div id="incorrect-sentence-display"></div>
                </div>
                <p>Construa a frase correta aqui:</p>
                <div id="correct-sentence-container"></div>
                <p>Clique nas palavras corretas na ordem certa:</p>
                <div id="word-options-container"></div>
            </div>
        </div>
        <div id="message"></div>
    </div>

    <div id="end-game-screen" class="hidden">
        <div id="end-game-box">
            <h2>Excelente!</h2>
            <p>Você completou todos os desafios!</p>
            <audio id="somDeVitoria" preload="auto">
                <source src="vitoria.mp3" type="audio/mpeg">
            </audio>
            <img src="imagens/13.png" alt="Troféu de Fim de Jogo" />
            <a href="caminho.php">Voltar ao Menu</a>
        </div>
    </div>

    <script>
        // --- NÍVEIS UNIFICADOS (5 DE CADA) ---
        const allLevels = [
            // Jogo 1: Montar Frases
            {
                type: 'montar',
                data: {
                    sentenceParts: [{
                        id: '1a',
                        text: 'O gato'
                    }, {
                        id: '1b',
                        text: 'dorme'
                    }, {
                        id: '1c',
                        text: 'na cama.'
                    }],
                    dropzones: [{
                        targetId: '1a'
                    }, {
                        targetId: '1b'
                    }, {
                        targetId: '1c'
                    }]
                }
            },
            {
                type: 'montar',
                data: {
                    sentenceParts: [{
                        id: '2a',
                        text: 'A menina'
                    }, {
                        id: '2b',
                        text: 'come'
                    }, {
                        id: '2c',
                        text: 'a maçã.'
                    }],
                    dropzones: [{
                        targetId: '2a'
                    }, {
                        targetId: '2b'
                    }, {
                        targetId: '2c'
                    }]
                }
            },
            {
                type: 'montar',
                data: {
                    sentenceParts: [{
                        id: '3a',
                        text: 'O sol'
                    }, {
                        id: '3b',
                        text: 'brilha'
                    }, {
                        id: '3c',
                        text: 'no céu.'
                    }],
                    dropzones: [{
                        targetId: '3a'
                    }, {
                        targetId: '3b'
                    }, {
                        targetId: '3c'
                    }]
                }
            },
            {
                type: 'montar',
                data: {
                    sentenceParts: [{
                        id: '4a',
                        text: 'O bebê'
                    }, {
                        id: '4b',
                        text: 'bebe'
                    }, {
                        id: '4c',
                        text: 'o leite.'
                    }],
                    dropzones: [{
                        targetId: '4a'
                    }, {
                        targetId: '4b'
                    }, {
                        targetId: '4c'
                    }]
                }
            },
            {
                type: 'montar',
                data: {
                    sentenceParts: [{
                        id: '5a',
                        text: 'As crianças'
                    }, {
                        id: '5b',
                        text: 'brincam'
                    }, {
                        id: '5c',
                        text: 'no parque.'
                    }],
                    dropzones: [{
                        targetId: '5a'
                    }, {
                        targetId: '5b'
                    }, {
                        targetId: '5c'
                    }]
                }
            },

            // Jogo 2: Corrigir Frases
            {
                type: 'corrigir',
                data: {
                    incorrectSentence: "Nós foi no parque.",
                    correctSentence: ["Nós", "fomos", "ao", "parque."],
                    options: ["foi", "Nós", "no", "fomos", "parque.", "ao"]
                }
            },
            {
                type: 'corrigir',
                data: {
                    incorrectSentence: "As menina é legal.",
                    correctSentence: ["As", "meninas", "são", "legais."],
                    options: ["menina", "legais.", "As", "é", "meninas", "são", "legal."]
                }
            },
            {
                type: 'corrigir',
                data: {
                    incorrectSentence: "Eu comi um sorvetes.",
                    correctSentence: ["Eu", "comi", "um", "sorvete."],
                    options: ["sorvetes.", "Eu", "comi", "sorvete.", "uns", "um"]
                }
            },
            {
                type: 'corrigir',
                data: {
                    incorrectSentence: "As casa é bonita.",
                    correctSentence: ["As", "casas", "são", "bonitas."],
                    options: ["casa", "bonitas.", "As", "é", "bonita.", "são", "casas"]
                }
            },
            {
                type: 'corrigir',
                data: {
                    incorrectSentence: "Eu sai onte anoite.",
                    correctSentence: ["Eu", "saí", "ontem", "à", "noite."],
                    options: ["Eu", "saí", "sai", "ontem", "onte", "à", "noite."]
                }
            }
        ];

        let currentLevel = 0;
        let draggedElement = null;
        let correctWordsCount = 0;

        const gameContent = document.getElementById('game-content');
        const endGameScreen = document.getElementById('end-game-screen');

        function unlockNextPhase(phaseNumber) {
            fetch(window.location.pathname, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=unlock_final_phase&nova_fase=${phaseNumber}`
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Resposta do Servidor:', data.message);
                    gameContent.classList.add('hidden');
                    endGameScreen.classList.remove('hidden');

                    try {
                        var audio = document.getElementById('somDeVitoria');
                        audio.currentTime = 0; // Reinicia o áudio
                        audio.play();
                    } catch (e) {
                        console.error("Erro ao tocar o áudio:", e);

                        try {
                            var audio = document.getElementById('somDeVitoria');
                            audio.currentTime = 0; // Reinicia o áudio
                            audio.play();
                        } catch (e) {
                            console.error("Erro ao tocar o áudio:", e);
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar progresso:', error);
                    gameContent.classList.add('hidden');
                    endGameScreen.classList.remove('hidden');
                });
        }

        function levelComplete() {
            message.innerHTML = '<h2>Excelente!</h2>';
            setTimeout(() => {
                currentLevel++;
                if (currentLevel < allLevels.length) {
                    loadLevel(currentLevel);
                } else {
                    unlockNextPhase(7);
                }
            }, 2000);
        }

        const gameTitle = document.getElementById('game-title');
        const message = document.getElementById('message');
        const montarFraseGame = document.getElementById('montar-frase-game');
        const draggablesContainer = document.getElementById('draggables-container');
        const dropzonesContainer = document.getElementById('dropzones-container');
        const corrigirFraseGame = document.getElementById('corrigir-frase-game');
        const incorrectSentenceDisplay = document.getElementById('incorrect-sentence-display');
        const correctSentenceContainer = document.getElementById('correct-sentence-container');
        const wordOptionsContainer = document.getElementById('word-options-container');

        const shuffle = (array) => {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        };

        function setupMontarFrase(levelData) {
            gameTitle.textContent = 'Monte a Frase';
            montarFraseGame.classList.remove('hidden');
            corrigirFraseGame.classList.add('hidden');
            draggablesContainer.innerHTML = '';
            dropzonesContainer.innerHTML = '';
            const shuffledParts = shuffle([...levelData.sentenceParts]);
            shuffledParts.forEach(part => {
                const cardDiv = document.createElement('div');
                cardDiv.className = 'draggable';
                cardDiv.setAttribute('draggable', 'true');
                cardDiv.setAttribute('data-id', part.id);
                cardDiv.textContent = part.text;
                draggablesContainer.appendChild(cardDiv);
            });
            levelData.dropzones.forEach(zone => {
                const zoneDiv = document.createElement('div');
                zoneDiv.className = 'dropzone';
                zoneDiv.setAttribute('data-target-id', zone.targetId);
                dropzonesContainer.appendChild(zoneDiv);
            });
        }

        function setupCorrigirFrase(levelData) {
            gameTitle.textContent = 'Corrija a Frase';
            montarFraseGame.classList.add('hidden');
            corrigirFraseGame.classList.remove('hidden');
            correctSentenceContainer.innerHTML = '';
            wordOptionsContainer.innerHTML = '';
            incorrectSentenceDisplay.textContent = levelData.incorrectSentence;
            const shuffledOptions = shuffle([...levelData.options]);
            shuffledOptions.forEach(word => {
                const button = document.createElement('button');
                button.className = 'word-option';
                button.textContent = word;
                button.onclick = () => handleWordClick(button, word);
                wordOptionsContainer.appendChild(button);
            });
        }

        function handleWordClick(buttonElement, clickedWord) {
            const levelData = allLevels[currentLevel].data;
            const expectedWord = levelData.correctSentence[correctWordsCount];
            if (clickedWord === expectedWord) {
                message.textContent = 'Certo!';
                message.style.color = '#76ff7a';
                const wordSlot = document.createElement('div');
                wordSlot.className = 'word-slot';
                wordSlot.textContent = clickedWord;
                correctSentenceContainer.appendChild(wordSlot);
                buttonElement.classList.add('disabled');
                buttonElement.onclick = null;
                correctWordsCount++;
                if (correctWordsCount === levelData.correctSentence.length) {
                    levelComplete();
                }
            } else {
                message.textContent = 'Ops, palavra errada!';
                message.style.color = '#ff6b6b';
                buttonElement.classList.add('incorrect-flash');
                setTimeout(() => {
                    buttonElement.classList.remove('incorrect-flash');
                    message.textContent = '';
                }, 800);
            }
        }

        function loadLevel(levelIndex) {
            message.innerHTML = '';
            correctWordsCount = 0;
            const level = allLevels[levelIndex];
            if (level.type === 'montar') {
                setupMontarFrase(level.data);
            } else if (level.type === 'corrigir') {
                setupCorrigirFrase(level.data);
            }
        }

        document.addEventListener('dragstart', (e) => {
            if (e.target.classList.contains('draggable')) {
                draggedElement = e.target;
            }
        });
        document.addEventListener('dragover', (e) => {
            const dropzone = e.target.closest('.dropzone');
            if (dropzone && !dropzone.classList.contains('correct')) {
                e.preventDefault();
            }
        });
        document.addEventListener('drop', (e) => {
            e.preventDefault();
            const dropzone = e.target.closest('.dropzone');
            if (!dropzone || !draggedElement) return;
            const draggedId = draggedElement.getAttribute('data-id');
            const targetId = dropzone.getAttribute('data-target-id');
            if (draggedId === targetId) {
                message.textContent = 'Certo!';
                message.style.color = '#76ff7a';
                dropzone.classList.add('correct');
                dropzone.textContent = '';
                dropzone.appendChild(draggedElement);
                draggedElement.setAttribute('draggable', 'false');
                correctWordsCount++;
                const totalParts = allLevels[currentLevel].data.sentenceParts.length;
                if (correctWordsCount === totalParts) {
                    levelComplete();

                }
            } else {
                message.textContent = 'Oops! Posição errada.';
                message.style.color = '#ff6b6b';
            }
        });

        loadLevel(currentLevel);
    </script>
</body>

</html>