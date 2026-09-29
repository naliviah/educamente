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

// --- LÓGICA DE SALVAMENTO PARA DESBLOQUEAR A FASE 8 ---
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
    $novaFase = intval($_POST['nova_fase']);

    if (isset($_SESSION['fase_atual']) && $_SESSION['fase_atual'] < $novaFase) {
        $con = new mysqli("localhost", "root", "", "educamente");
        if ($con->connect_error) {
            echo json_encode(['status' => 'error', 'message' => 'Erro de conexão com o banco de dados.']);
            exit;
        }

        $stmt = $con->prepare("UPDATE progresso SET fase_atual = ? WHERE id_aluno = ? AND fase_atual < ?");
        $stmt->bind_param("iii", $novaFase, $id_aluno, $novaFase);

        if ($stmt->execute()) {
            $_SESSION['fase_atual'] = $novaFase;
            echo json_encode(['status' => 'success', 'message' => 'Progresso salvo! Fase ' . $novaFase . ' desbloqueada.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar o progresso.']);
        }
        $stmt->close();
        $con->close();
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Nenhuma atualização necessária.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desafio Final</title>
    <style>
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

        .game-container p.instructions {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 25px;
        }

        /* --- ESTILOS FASE 1: MONTAR HISTÓRIAS --- */
        #story-parts-container,
        #story-dropzones-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-radius: 15px;
            min-height: 100px;
        }

        #story-dropzones-container {
            background-color: rgba(0, 0, 0, 0.05);
            border: 2px dashed #a0b0ff;
            margin-bottom: 30px;
        }

        .story-part {
            background-color: #f7b733;
            color: #333;
            font-weight: 700;
            font-size: 1.1em;
            padding: 15px 25px;
            cursor: grab;
            border-radius: 10px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
            user-select: none;
            width: 90%;
            text-align: center;
        }

        .story-part:active {
            transform: scale(1.02);
            cursor: grabbing;
        }

        .dropzone {
            width: 95%;
            height: 70px;
            border: 2px dashed #ccc;
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
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

        /* --- ESTILOS FASE 2: INTERPRETAÇÃO --- */
        #reading-text-container {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 20px;
            border-radius: 10px;
            text-align: left;
            font-size: 1.2em;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* --- ESTILOS FASE 2 e 3: PERGUNTAS E OPÇÕES --- */
        #question-container {
            text-align: center;
        }

        #question-text,
        #question-text-quiz {
            font-size: 1.5em;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
        }

        #options-container,
        #options-container-quiz {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            /* <<-- ESPAÇAMENTO AUMENTADO AQUI -- */
        }

        .option-button {
            background-color: #007bff;
            color: white;
            font-weight: 700;
            font-size: 1.2em;
            padding: 15px 25px;
            cursor: pointer;
            border-radius: 10px;
            border: none;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .option-button:hover:not(.disabled) {
            transform: translateY(-3px);
            background-color: #0056b3;
        }

        .option-button.correct {
            background-color: #28a745;
        }

        .option-button.incorrect {
            background-color: #dc3545;
            animation: shake 0.5s;
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

        .option-button.disabled {
            pointer-events: none;
            opacity: 0.7;
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
            <p class="instructions" id="instructions"></p>

            <div id="phase1-story" class="hidden">
                <div id="story-dropzones-container"></div>
                <div id="story-parts-container"></div>
            </div>

            <div id="phase2-reading" class="hidden">
                <div id="reading-text-container"></div>
                <div id="question-container">
                    <div id="question-text"></div>
                    <div id="options-container"></div>
                </div>
            </div>

            <div id="phase3-quiz" class="hidden">
                <div id="question-container-quiz">
                    <div id="question-text-quiz"></div>
                    <div id="options-container-quiz"></div>
                </div>
            </div>

        </div>
        <div id="message"></div>
    </div>

    <div id="end-game-screen" class="hidden">
        <div id="end-game-box">
            <h2>Parabéns!</h2>
            <p>Você concluiu a revisão final!</p>
            <audio id="somDeVitoria" preload="auto">
                <source src="vitoria.mp3" type="audio/mpeg">
            </audio>
            <img src="imagens/13.png" alt="Troféu" />
            <a href="caminho.php">Voltar ao Menu</a>
        </div>
    </div>

    <script>
        // --- CONTEÚDO DOS JOGOS ---
        const gamePhases = [{
                type: 'montar-historia',
                title: 'Organize a História',
                instruction: 'Arraste as partes da história para os espaços, colocando-as na ordem certa.',
                levels: [{
                        storyParts: [{
                            id: 1,
                            text: "Bia encontrou um gatinho perdido na rua."
                        }, {
                            id: 2,
                            text: "Ela o levou para casa e deu leite."
                        }, {
                            id: 3,
                            text: "O gatinho ronronou feliz e dormiu no seu colo."
                        }]
                    },
                    {
                        storyParts: [{
                            id: 1,
                            text: "Lucas ganhou uma bola nova de aniversário."
                        }, {
                            id: 2,
                            text: "Ele chamou seus amigos para brincar no parque."
                        }, {
                            id: 3,
                            text: "Todos jogaram futebol até cansar."
                        }]
                    }
                ]
            },
            {
                type: 'interpretar-texto',
                title: 'Leia e Responda',
                instruction: 'Leia o texto com atenção e depois escolha a resposta certa.',
                levels: [{
                        text: "A girafa Sofia é muito alta. Ela adora comer as folhas mais verdes do topo das árvores. Seu pescoço comprido a ajuda a alcançar a comida mais gostosa, onde outros animais não conseguem chegar.",
                        question: "Por que a girafa Sofia consegue comer as folhas mais altas?",
                        options: ["Porque ela sabe pular.", "Porque seu pescoço é comprido.", "Porque as árvores são baixas."],
                        correctAnswer: "Porque seu pescoço é comprido."
                    },
                    {
                        text: "O palhaço Pimpão trabalha no circo. Ele usa um sapato gigante e um nariz vermelho. Sua alegria é fazer as crianças rirem com suas brincadeiras. Quando o espetáculo acaba, ele fica feliz em ver todos aplaudindo.",
                        question: "Qual é a principal alegria do palhaço Pimpão?",
                        options: ["Usar um nariz vermelho.", "Fazer as crianças rirem.", "Receber aplausos."],
                        correctAnswer: "Fazer as crianças rirem."
                    }
                ]
            },
            {
                type: 'quiz-geral',
                title: 'Quiz Rápido',
                instruction: 'Vamos revisar! Escolha a resposta certa para cada pergunta.',
                levels: [{
                        question: "Qual palavra rima com 'JANELA'?",
                        options: ["CASA", "PANELA", "PORTA"],
                        correctAnswer: "PANELA"
                    },
                    {
                        question: "Qual é a forma correta de escrever a frase: 'As borboleta voa'?",
                        options: ["As borboletas voam.", "As borboleta voam.", "As borboletas voa."],
                        correctAnswer: "As borboletas voam."
                    },
                    {
                        question: "Quantas sílabas (pedaços) tem a palavra 'CA-VA-LO'?",
                        options: ["Duas", "Três", "Quatro"],
                        correctAnswer: "Três"
                    },
                    {
                        question: "Qual palavra começa com a mesma letra de 'SAPO'?",
                        options: ["BOLA", "TIGRE", "SOL"],
                        correctAnswer: "SOL"
                    }
                ]
            }
        ];

        let currentPhaseIndex = 0;
        let currentLevelIndex = 0;

        function loadGamePhase() {
            const phase = gamePhases[currentPhaseIndex];
            document.getElementById('game-title').textContent = phase.title;
            document.getElementById('instructions').textContent = phase.instruction;

            document.getElementById('phase1-story').classList.add('hidden');
            document.getElementById('phase2-reading').classList.add('hidden');
            document.getElementById('phase3-quiz').classList.add('hidden');

            if (phase.type === 'montar-historia') {
                document.getElementById('phase1-story').classList.remove('hidden');
                setupMontarHistoria();
            } else if (phase.type === 'interpretar-texto') {
                document.getElementById('phase2-reading').classList.remove('hidden');
                setupInterpretarTexto();
            } else if (phase.type === 'quiz-geral') {
                document.getElementById('phase3-quiz').classList.remove('hidden');
                setupQuizGeral();
            }
        }

        function nextLevelOrPhase() {
            const phase = gamePhases[currentPhaseIndex];
            currentLevelIndex++;
            if (currentLevelIndex < phase.levels.length) {
                loadGamePhase();
            } else {
                currentPhaseIndex++;
                currentLevelIndex = 0;
                if (currentPhaseIndex < gamePhases.length) {
                    loadGamePhase();
                } else {
                    unlockNextPhase(8);
                }
            }
        }

        let correctPartsCount = 0;
        let draggedElement = null;

        function setupMontarHistoria() {
            correctPartsCount = 0;
            const levelData = gamePhases[currentPhaseIndex].levels[currentLevelIndex];
            const dropzonesContainer = document.getElementById('story-dropzones-container');
            const partsContainer = document.getElementById('story-parts-container');
            dropzonesContainer.innerHTML = '';
            partsContainer.innerHTML = '';

            levelData.storyParts.forEach((part, index) => {
                const zoneDiv = document.createElement('div');
                zoneDiv.className = 'dropzone';
                zoneDiv.setAttribute('data-target-id', index + 1);
                dropzonesContainer.appendChild(zoneDiv);
            });

            const shuffledParts = [...levelData.storyParts].sort(() => Math.random() - 0.5);
            shuffledParts.forEach(part => {
                const partDiv = document.createElement('div');
                partDiv.className = 'story-part';
                partDiv.setAttribute('draggable', 'true');
                partDiv.setAttribute('data-id', part.id);
                partDiv.textContent = part.text;
                partsContainer.appendChild(partDiv);
            });
        }

        function setupQuestionAndOptions(containerId, levelData) {
            const questionTextEl = document.querySelector(`${containerId} #question-text, ${containerId} #question-text-quiz`);
            const optionsContainerEl = document.querySelector(`${containerId} #options-container, ${containerId} #options-container-quiz`);
            optionsContainerEl.innerHTML = '';
            questionTextEl.textContent = levelData.question;

            levelData.options.forEach(option => {
                const button = document.createElement('button');
                button.className = 'option-button';
                button.textContent = option;
                button.onclick = () => checkAnswer(button, option, levelData.correctAnswer);
                optionsContainerEl.appendChild(button);
            });
        }

        function setupInterpretarTexto() {
            const levelData = gamePhases[currentPhaseIndex].levels[currentLevelIndex];
            document.getElementById('reading-text-container').textContent = levelData.text;
            setupQuestionAndOptions('#phase2-reading', levelData);
        }

        function setupQuizGeral() {
            const levelData = gamePhases[currentPhaseIndex].levels[currentLevelIndex];
            setupQuestionAndOptions('#phase3-quiz', levelData);
        }

        function checkAnswer(button, selectedAnswer, correctAnswer) {
            const allButtons = button.parentElement.querySelectorAll('.option-button');
            allButtons.forEach(btn => btn.classList.add('disabled'));

            if (selectedAnswer === correctAnswer) {
                button.classList.add('correct');
                message.textContent = 'Resposta Certa!';
                message.style.color = '#76ff7a';
                setTimeout(nextLevelOrPhase, 2000);
            } else {
                button.classList.add('incorrect');
                message.textContent = 'Resposta Errada!';
                message.style.color = '#ff6b6b';
                setTimeout(nextLevelOrPhase, 2000);
            }
        }

        document.addEventListener('dragstart', (e) => {
            if (e.target.classList.contains('story-part')) {
                draggedElement = e.target;
            }
        });
        document.addEventListener('dragover', (e) => {
            const dz = e.target.closest('.dropzone');
            if (dz && !dz.classList.contains('correct')) {
                e.preventDefault();
                dz.classList.add('hovered');
            }
        });
        document.addEventListener('dragleave', (e) => {
            const dz = e.target.closest('.dropzone');
            if (dz) dz.classList.remove('hovered');
        });
        document.addEventListener('drop', (e) => {
            e.preventDefault();
            const dropzone = e.target.closest('.dropzone');
            if (!dropzone || !draggedElement) return;
            dropzone.classList.remove('hovered');
            const draggedId = parseInt(draggedElement.getAttribute('data-id'));
            const targetId = parseInt(dropzone.getAttribute('data-target-id'));
            if (draggedId === targetId) {
                dropzone.classList.add('correct');
                dropzone.appendChild(draggedElement);
                draggedElement.setAttribute('draggable', 'false');
                correctPartsCount++;
                if (correctPartsCount === gamePhases[currentPhaseIndex].levels[currentLevelIndex].storyParts.length) {
                    message.textContent = 'História completa!';
                    message.style.color = '#76ff7a';
                    setTimeout(nextLevelOrPhase, 2000);
                }
            } else {
                message.textContent = 'Essa não é a parte certa da história.';
                message.style.color = '#ff6b6b';
                setTimeout(() => message.textContent = '', 1500);
            }
        });

        const message = document.getElementById('message');

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
                    document.getElementById('game-content').classList.add('hidden');
                    document.getElementById('end-game-screen').classList.remove('hidden');

                    try {
                        var audio = document.getElementById('somDeVitoria');
                        audio.currentTime = 0; // Reinicia o áudio
                        audio.play();
                    } catch (e) {
                        console.error("Erro ao tocar o áudio:", e);
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar progresso:', error);
                    document.getElementById('game-content').classList.add('hidden');
                    document.getElementById('end-game-screen').classList.remove('hidden');

                    try {
                        var audio = document.getElementById('somDeVitoria');
                        audio.currentTime = 0; // Reinicia o áudio
                        audio.play();
                    } catch (e) {
                        console.error("Erro ao tocar o áudio:", e);
                    }
                });
        }

        loadGamePhase();
    </script>
</body>

</html>