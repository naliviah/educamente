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

// --- LÓGICA DE PROGRESSÃO (AJAX) ---
// Esta ação só é chamada no final do SEGUNDO jogo.
if (isset($_GET['action']) && $_GET['action'] == 'completar_fase_5') {
    header('Content-Type: application/json');

    if (!isset($_SESSION['id_aluno'])) {
        echo json_encode(['status' => 'error', 'message' => 'Sessão de aluno não encontrada.']);
        exit;
    }

    $id_aluno = $_SESSION['id_aluno'];
    $con = new mysqli("localhost", "root", "", "educamente");

    if ($con->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Erro de conexão com o banco de dados.']);
        exit;
    }

    // Ao completar a fase 5, a próxima fase é a 6.
    $novaFase = 6;
    if ($_SESSION['fase_atual'] < $novaFase) {
        $stmt = $con->prepare("UPDATE progresso SET fase_atual = ? WHERE id_aluno = ?");
        $stmt->bind_param("ii", $novaFase, $id_aluno);
        if ($stmt->execute()) {
            $_SESSION['fase_atual'] = $novaFase;
            echo json_encode(['status' => 'success', 'message' => 'Progresso salvo para a Fase ' . $novaFase]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Fase 5 já completa.']);
    }

    $con->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desafio Duplo - Fase 5</title>
    <style>
        /* --- ESTILO GERAL --- */
        body {
            font-family: 'Verdana', sans-serif;
            background-color: #181c92ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #444;
            transition: background-color 0.5s ease;
        }

        .hidden {
            display: none !important;
        }

        /* --- ESTILOS JOGO 1: DETETIVE DE LETRAS --- */
        #game-container-detetive {
            background-color: #ffffffc0;
            padding: 20px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 90%;
            max-width: 700px;
        }

        #game-container-detetive h1 {
            color: rgba(7, 30, 86, 1);
        }

        #word-display {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 30px 0;
            min-height: 80px;
            flex-wrap: wrap;
        }

        .letter-box {
            width: 70px;
            height: 70px;
            background-color: #e9ecef;
            border: 2px solid #ced4da;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2.2em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            text-transform: uppercase;
        }

        .letter-box:hover {
            background-color: #d1d9e0;
            transform: translateY(-5px);
        }

        .letter-box.selected {
            background-color: #5883e7ff;
            border-color: #143d77ff;
            transform: scale(1.1);
        }

        .letter-box.correct {
            background-color: #28a745;
            border-color: #218838;
            color: white;
            animation: bounce 0.5s;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }
        }

        #letter-choices {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
            min-height: 60px;
        }

        .choice-button {
            width: 70px;
            height: 70px;
            background-color: rgba(63, 130, 224, 1);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.8em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
            text-transform: uppercase;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .choice-button:hover {
            background-color: rgba(80, 65, 194, 1);
        }

        #game-container-detetive.error-shake {
            animation: screen-shake 0.4s;
        }

        @keyframes screen-shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        .feedback-message {
            font-size: 1.2em;
            font-weight: bold;
            min-height: 30px;
        }

        .feedback-success {
            color: #28a745;
        }

        .feedback-error {
            color: #dc3545;
        }

        #next-case-button {
            background-color: rgb(71, 151, 255);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.2em;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        #next-case-button:hover {
            background-color: rgb(55, 87, 229);
        }

        /* --- ESTILOS JOGO 2: ARRASTAR E COMPLETAR --- */
        #game-container-arrastar {
            text-align: center;
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #game-container-arrastar h1 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 20px;
        }

        .sentence-container {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        /* CORREÇÃO APLICADA AQUI */
        #drop-zone {
            width: 100px;
            height: 40px;
            border: 2px dashed #a0a0a0;
            /* Corrigido de #a0a0a00 para #a0a0a0 */
            border-radius: 5px;
            margin: 0 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s, border-color 0.3s;
            font-size: 0.8em;
            font-weight: normal;
        }

        .drop-over {
            background-color: #e0e0e0;
            border-color: #28a745;
        }

        #drag-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .draggable-word {
            padding: 10px 20px;
            background-color: #122f94;
            color: #fff;
            border: 2px solid #122f94;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: grab;
            transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .dragging {
            opacity: 0.5;
            transform: scale(0.95);
            cursor: grabbing;
        }

        #feedback-message-arrastar {
            min-height: 30px;
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
        }

        .feedback-correct-arrastar {
            color: #28a745;
        }

        .feedback-incorrect-arrastar {
            color: #dc3545;
        }

        #congrats-image {
            max-width: 250px;
            margin-top: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        #final-message-container p {
            font-size: 1.8em;
            font-weight: bold;
            color: #122f94;
            margin-bottom: 15px;
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
    <a href="../fases/caminho.php" style="position: absolute; top: 30px; left: 30px; color: white; font-size: 24px; font-weight: bold; text-decoration: none; cursor: pointer; z-index: 10;">←</a>

    <div id="game-container-detetive">
        <div id="game-area-detetive">
            <h1>🕵️ Detetive de Letras</h1>
            <p>Encontre a letra errada e corrija a palavra!</p>
            <div id="word-display"></div>
            <div id="letter-choices"></div>
            <button id="next-case-button" class="hidden">Próximo Caso</button>
        </div>
        <div id="feedback-detetive" class="feedback-message"></div>
    </div>

    <main id="game-container-arrastar" class="hidden">
        <h1 id="challenge-text">Complete a frase</h1>
        <div class="sentence-container">
            <p id="sentence-start"></p>
            <div id="drop-zone"></div>
            <p id="sentence-end"></p>
        </div>
        <div id="drag-options"></div>
        <div id="feedback-message-arrastar"></div>
        <div id="final-message-container" class="hidden" style="text-align: center;">
            <img id="congrats-image" src="imagens/14.png" alt="Dragão fofo de parabéns">
            <p>Parabéns!</p>
            <audio id="somDeVitoria" preload="auto">
                <source src="vitoria.mp3" type="audio/mpeg">
            </audio>
            <a href="../fases/caminho.php" style="font-size: 1.5em; font-weight: bold; text-decoration: none; color:rgb(74, 71, 255); margin-top: 15px; display: inline-block;">
                Continuar →
            </a>
        </div>
    </main>

    <script>
        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        const game1 = {
            cases: [{
                    wrongWord: "KASA",
                    correctLetter: "C",
                    wrongLetterIndex: 0,
                    choices: ["C", "P", "L"]
                },
                {
                    wrongWord: "JIRAFA",
                    correctLetter: "G",
                    wrongLetterIndex: 0,
                    choices: ["V", "G", "F"]
                },
                {
                    wrongWord: "PALHASO",
                    correctLetter: "Ç",
                    wrongLetterIndex: 5,
                    choices: ["C", "S", "Ç"]
                },
                {
                    wrongWord: "PÍRULA",
                    correctLetter: "L",
                    wrongLetterIndex: 2,
                    choices: ["X", "Z", "L"]
                },
                {
                    wrongWord: "CELÉBRO",
                    correctLetter: "R",
                    wrongLetterIndex: 2,
                    choices: ["R", "L", "M"]
                }
            ],
            currentCaseIndex: 0,
            letterBoxes: [],
            step: 'find',
            elements: {
                container: document.getElementById('game-container-detetive'),
                gameArea: document.getElementById('game-area-detetive'),
                wordDisplay: document.getElementById('word-display'),
                letterChoicesContainer: document.getElementById('letter-choices'),
                feedbackMessage: document.getElementById('feedback-detetive'),
                nextCaseButton: document.getElementById('next-case-button')
            },
            init() {
                this.cases = shuffleArray(this.cases);
                this.loadCase();
                this.elements.nextCaseButton.addEventListener('click', () => this.handleNextCase());
            },
            loadCase() {
                this.step = 'find';
                this.elements.feedbackMessage.textContent = '';
                this.elements.wordDisplay.innerHTML = '';
                this.elements.letterChoicesContainer.innerHTML = '';
                this.letterBoxes = [];
                this.elements.nextCaseButton.classList.add('hidden');
                const currentCase = this.cases[this.currentCaseIndex];
                currentCase.wrongWord.split('').forEach((letter, index) => {
                    const box = document.createElement('div');
                    box.classList.add('letter-box');
                    box.textContent = letter;
                    box.dataset.index = index;
                    box.addEventListener('click', (e) => this.handleLetterClick(e));
                    this.elements.wordDisplay.appendChild(box);
                    this.letterBoxes.push(box);
                });
            },
            handleLetterClick(event) {
                if (this.step !== 'find') return;
                const clickedIndex = parseInt(event.target.dataset.index);
                const correctIndex = this.cases[this.currentCaseIndex].wrongLetterIndex;
                if (clickedIndex === correctIndex) {
                    this.step = 'correct';
                    this.letterBoxes.forEach(box => box.style.cursor = 'default');
                    event.target.classList.add('selected');
                    this.showFeedback('Ótimo! Agora escolha a letra certa.', 'success');
                    this.showLetterChoices();
                } else {
                    this.showFeedback('Essa letra parece estar certa. Tente outra!', 'error');
                }
            },
            showLetterChoices() {
                const currentCase = this.cases[this.currentCaseIndex];
                shuffleArray(currentCase.choices).forEach(choice => {
                    const button = document.createElement('button');
                    button.classList.add('choice-button');
                    button.textContent = choice;
                    button.addEventListener('click', (e) => this.handleChoiceClick(e));
                    this.elements.letterChoicesContainer.appendChild(button);
                });
            },
            handleChoiceClick(event) {
                if (this.step !== 'correct') return;
                const chosenLetter = event.currentTarget.textContent;
                const currentCase = this.cases[this.currentCaseIndex];
                if (chosenLetter === currentCase.correctLetter) {
                    this.step = 'done';
                    this.elements.letterChoicesContainer.innerHTML = '';
                    const wrongLetterBox = document.querySelector('.letter-box.selected');
                    wrongLetterBox.textContent = currentCase.correctLetter;
                    wrongLetterBox.classList.remove('selected');
                    wrongLetterBox.classList.add('correct');
                    const correctWord = currentCase.wrongWord.split('');
                    correctWord[currentCase.wrongLetterIndex] = currentCase.correctLetter;
                    this.showFeedback(`Caso resolvido! A palavra correta é "${correctWord.join('')}"!`, 'success');
                    this.elements.nextCaseButton.classList.remove('hidden');
                } else {
                    this.showFeedback('Não parece ser essa. Tente de novo!', 'error');
                    this.elements.container.classList.add('error-shake');
                    setTimeout(() => this.elements.container.classList.remove('error-shake'), 500);
                }
            },
            handleNextCase() {
                this.currentCaseIndex++;
                if (this.currentCaseIndex < this.cases.length) {
                    this.loadCase();
                } else {
                    this.endGame();
                }
            },
            showFeedback(message, type) {
                this.elements.feedbackMessage.className = 'feedback-message';
                this.elements.feedbackMessage.textContent = message;
                this.elements.feedbackMessage.classList.add(type === 'success' ? 'feedback-success' : 'feedback-error');
            },
            endGame() {
                this.elements.gameArea.innerHTML = "<h2>Ótimo trabalho, detetive!</h2><p>Prepare-se para o próximo desafio...</p>";
                setTimeout(() => {
                    this.elements.container.classList.add('hidden');
                    document.body.style.backgroundColor = '#122f94';
                    game2.init();
                }, 2000);
            }
        };

        const game2 = {
            challenges: [{
                    sentence: ["Eu gosto de maçã", "laranja."],
                    correctWord: "e",
                    options: ["com", "e", "de"]
                },
                {
                    sentence: ["O cachorro brinca", "a bola."],
                    correctWord: "com",
                    options: ["mas", "para", "com"]
                },
                {
                    sentence: ["Ele queria sorvete,", "não tinha dinheiro."],
                    correctWord: "mas",
                    options: ["e", "para", "mas"]
                },
                {
                    sentence: ["Você quer ir ao parque", "ao cinema?"],
                    correctWord: "ou",
                    options: ["ou", "com", "mas"]
                },
                {
                    sentence: ["Eu preciso estudar", "a prova."],
                    correctWord: "para",
                    options: ["e", "para", "de"]
                },
                {
                    sentence: ["Ela não foi à festa", "estava doente."],
                    correctWord: "porque",
                    options: ["quando", "porque", "se"]
                }
            ],
            currentChallengeIndex: 0,
            draggedItem: null,
            elements: {
                container: document.getElementById('game-container-arrastar'),
                challengeText: document.getElementById('challenge-text'),
                dropZone: document.getElementById('drop-zone'),
                dragOptionsContainer: document.getElementById('drag-options'),
                feedbackMessage: document.getElementById('feedback-message-arrastar'),
                finalMessageContainer: document.getElementById('final-message-container'),
                sentenceStart: document.getElementById('sentence-start'),
                sentenceEnd: document.getElementById('sentence-end')
            },
            init() {
                this.elements.container.classList.remove('hidden');
                this.challenges = shuffleArray(this.challenges);
                this.loadNewChallenge();
                document.addEventListener('dragstart', e => this.handleDragStart(e));
                document.addEventListener('dragend', e => this.handleDragEnd(e));
                this.elements.dropZone.addEventListener('dragover', e => e.preventDefault());
                this.elements.dropZone.addEventListener('drop', e => this.handleDrop(e));
            },
            loadNewChallenge() {
                this.elements.feedbackMessage.textContent = '';
                this.elements.dropZone.textContent = '';
                if (this.currentChallengeIndex < this.challenges.length) {
                    const currentChallenge = this.challenges[this.currentChallengeIndex];
                    this.elements.sentenceStart.textContent = currentChallenge.sentence[0];
                    this.elements.sentenceEnd.textContent = currentChallenge.sentence[1];
                    this.elements.dragOptionsContainer.innerHTML = '';
                    currentChallenge.options.forEach(word => {
                        const draggableWord = document.createElement('div');
                        draggableWord.className = 'draggable-word';
                        draggableWord.textContent = word;
                        draggableWord.draggable = true;
                        draggableWord.dataset.word = word;
                        this.elements.dragOptionsContainer.appendChild(draggableWord);
                    });
                } else {
                    this.endGame();
                }
            },
            handleDragStart(e) {
                if (e.target.classList.contains('draggable-word')) {
                    this.draggedItem = e.target;
                    setTimeout(() => this.draggedItem.classList.add('dragging'), 0);
                }
            },
            handleDragEnd(e) {
                if (e.target.classList.contains('draggable-word')) {
                    e.target.classList.remove('dragging');
                }
            },
            handleDrop(e) {
                e.preventDefault();
                const selectedWord = this.draggedItem.dataset.word;
                const correctWord = this.challenges[this.currentChallengeIndex].correctWord;
                if (selectedWord === correctWord) {
                    this.elements.dropZone.textContent = selectedWord;
                    this.showFeedback("Parabéns, você acertou!", 'correct');
                    this.currentChallengeIndex++;
                    setTimeout(() => this.loadNewChallenge(), 1200);
                } else {
                    this.showFeedback("Ops, tente novamente!", 'incorrect');
                }
            },
            showFeedback(message, type) {
                this.elements.feedbackMessage.textContent = message;
                this.elements.feedbackMessage.className = `feedback-${type}-arrastar`;
            },
            endGame() {
                this.elements.challengeText.style.display = 'none';
                document.querySelector('.sentence-container').style.display = 'none';
                this.elements.dragOptionsContainer.style.display = 'none';
                this.elements.feedbackMessage.style.display = 'none';
                fetch('?action=completar_fase_5')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.elements.finalMessageContainer.classList.remove('hidden');

                            try {
                                var audio = document.getElementById('somDeVitoria');
                                audio.currentTime = 0; // Reinicia o áudio
                                audio.play();
                            } catch (e) {
                                console.error("Erro ao tocar o áudio:", e);
                            }
                        } else {
                            this.elements.feedbackMessage.style.display = 'block';
                            this.showFeedback(`Erro ao salvar: ${data.message}`, 'incorrect');
                        }
                    });
            }
        };

        // --- INICIA O JOGO ---
        game1.init();
    </script>
</body>

</html>