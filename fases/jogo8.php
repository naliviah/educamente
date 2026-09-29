<?php
//jogo das vogais
session_start();

// Define qual fase este arquivo representa
$faseAtual = 8;

// Se o usuário já está nessa fase, ao abrir esta página ele "conclui"
if ($_SESSION['fase_atual'] == $faseAtual) {
    $_SESSION['fase_atual']++; // libera a próxima fase
}

$novaFase = 9;
$_SESSION['fase_atual'] = $novaFase;

$id_aluno = $_SESSION['id_aluno'];
$con = new mysqli("localhost", "root", "", "educamente");
$con->query("UPDATE progresso SET fase_atual = $novaFase WHERE id_aluno = $id_aluno");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arrastar e Completar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #122f94; /* O azul do fundo */
        }

        /* Estilos para o container principal (a caixa branca) */
        .game-container {
            text-align: center;
            background-color: #fff; /* Fundo branco */
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
            
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Estilos do título */
        h1 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 20px;
        }

        /* Estilos para a imagem da palavra */
        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
        }

        #word-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
        }

        /* Estilos para a frase e a área de soltura */
        .sentence-container {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
            flex-wrap: wrap; /* Permite que a frase quebre a linha se for muito longa */
        }

        #drop-zone {
            width: 100px;
            height: 40px;
            border: 2px dashed #a0a0a0;
            border-radius: 5px;
            margin: 0 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s, border-color 0.3s;
            font-size: 0.8em; /* Para a palavra caber */
            font-weight: normal;
        }

        /* Efeito ao arrastar um item sobre a zona */
        .drop-over {
            background-color: #e0e0e0;
            border-color: #28a745;
        }

        /* Estilos para as palavras arrastáveis */
        #drag-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .draggable-word {
            padding: 10px 20px;
            background-color: #122f94; /* Cor do botão para as palavras */
            color: #fff;
            border: 2px solid #122f94;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: grab;
            transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Efeito ao segurar o item para arrastar */
        .dragging {
            opacity: 0.5;
            transform: scale(0.95);
            cursor: grabbing;
        }

        /* Estilos para as mensagens de feedback */
        #feedback-message {
            min-height: 30px;
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 15px;
        }

        .fim{
            color:#122f94;
        }

        .correct {
            color: #28a745;
        }

        .incorrect {
            color: #dc3545;
        }

        /* Estilo para a imagem de parabéns */
        #congrats-image {
            max-width: 450px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <main class="game-container">
        <h1 id="challenge-text">Complete a frase</h1>
        
        <div class="image-container">
            <img id="word-image" src="" alt="Imagem da frase">
        </div>
        
        <div class="sentence-container">
            <p id="sentence-start"></p>
            <div id="drop-zone"></div>
            <p id="sentence-end"></p>
        </div>
        
        <div id="drag-options" class="letter-options">
            </div>

        <div id="feedback-message"></div>

        <img id="congrats-image" src="imagens/parabens.png" alt="Imagem de parabéns" style="display:none;">
    </main>
    <script>
        // Desafios com palavras de ligação
        const challenges = [
            {
                sentence: ["Eu gosto de maçã", "laranja."],
                image: "imagens/pensativo.png",
                correctWord: "e",
                options: ["com", "e", "de"]
            },
            {
                sentence: ["O cachorro brinca", "a bola."],
                image: "imagens/pensativo.png",
                correctWord: "com",
                options: ["mas", "para", "com"]
            },
            {
                sentence: ["Ele queria sorvete,", "não tinha dinheiro."],
                image: "imagens/pensativo.png",
                correctWord: "mas",
                options: ["e", "para", "mas"]
            },
            // Adicione mais desafios aqui
        ];

        let currentChallengeIndex = 0;
        let draggedItem = null;

        const challengeText = document.getElementById('challenge-text');
        const wordImage = document.getElementById('word-image');
        const dropZone = document.getElementById('drop-zone');
        const dragOptionsContainer = document.getElementById('drag-options');
        const feedbackMessage = document.getElementById('feedback-message');
        const congratsImage = document.getElementById('congrats-image');
        const sentenceStart = document.getElementById('sentence-start');
        const sentenceEnd = document.getElementById('sentence-end');

        // Função para carregar um novo desafio
        function loadNewChallenge() {
            congratsImage.style.display = 'none';
            feedbackMessage.textContent = '';
            
            challengeText.style.display = 'block';
            wordImage.style.display = 'block';
            dropZone.textContent = ''; // Limpa a zona de soltura
            dropZone.style.backgroundColor = '';
            
            // Mostra os elementos do jogo
            document.querySelector('.sentence-container').style.display = 'flex';
            dragOptionsContainer.style.display = 'flex';

            if (currentChallengeIndex < challenges.length) {
                const currentChallenge = challenges[currentChallengeIndex];

                // Atualiza a frase na tela
                sentenceStart.textContent = currentChallenge.sentence[0];
                sentenceEnd.textContent = currentChallenge.sentence[1];
                
                wordImage.src = currentChallenge.image;

                // Limpa e recria as palavras arrastáveis
                dragOptionsContainer.innerHTML = '';
                currentChallenge.options.forEach(word => {
                    const draggableWord = document.createElement('div');
                    draggableWord.classList.add('draggable-word');
                    draggableWord.textContent = word;
                    draggableWord.draggable = true; // Torna o elemento arrastável
                    draggableWord.setAttribute('data-word', word);
                    dragOptionsContainer.appendChild(draggableWord);
                });

            } else {
                // Fim do jogo
                challengeText.style.display = 'none';
                wordImage.style.display = 'none';
                document.querySelector('.sentence-container').style.display = 'none';
                dragOptionsContainer.style.display = 'none';
                
                feedbackMessage.textContent = "Parabéns! Você completou o desafio!";
                feedbackMessage.classList.remove('incorrect');
                feedbackMessage.classList.remove('correct');
                feedbackMessage.classList.add('fim');
                congratsImage.style.display = 'block';
            }
        }

        // Lógica de Drag and Drop
        document.addEventListener('dragstart', (e) => {
            if (e.target.classList.contains('draggable-word')) {
                draggedItem = e.target;
                setTimeout(() => {
                    draggedItem.classList.add('dragging');
                }, 0);
            }
        });

        document.addEventListener('dragend', (e) => {
            e.target.classList.remove('dragging');
        });

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault(); // Necessário para permitir a soltura
            dropZone.classList.add('drop-over');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drop-over');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drop-over');

            const selectedWord = draggedItem.getAttribute('data-word');
            const currentChallenge = challenges[currentChallengeIndex];
            const correctWord = currentChallenge.correctWord;

            if (selectedWord === correctWord) {
                dropZone.textContent = selectedWord; // Coloca a palavra na zona de soltura
                feedbackMessage.textContent = "Parabéns, você acertou!";
                feedbackMessage.classList.remove('incorrect');
                feedbackMessage.classList.add('correct');
                
                currentChallengeIndex++;
                setTimeout(loadNewChallenge, 900);
            } else {
                feedbackMessage.textContent = "Ops, tente novamente!";
                feedbackMessage.classList.remove('correct');
                feedbackMessage.classList.add('incorrect');
                
                setTimeout(() => {
                    draggedItem.style.border = '2px solid #ddd';
                    feedbackMessage.textContent = '';
                }, 1500);
            }
        });

        // Inicia o jogo
        loadNewChallenge();
    </script>
</body>
</html>