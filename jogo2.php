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
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogo das Consoantes e Vogais</title>
    <style>
        /* Estilos Gerais */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
            background-color: rgba(18, 47, 148, 1);
            color: white;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
        }

        /* Container do Jogo */
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            max-width: 900px;
            margin-top: 20px;
        }

        .letters-container,
        .images-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
            border-radius: 15px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Itens Arrastáveis (Letras) */
        .draggable {
            width: 80px;
            height: 80px;
            background-color: rgba(18, 46, 148, 0.56);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            cursor: grab;
            border-radius: 50%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
        }

        .draggable:active {
            transform: scale(1.1);
        }

        /* Áreas de Soltar (Imagens) */
        .dropzone {
            width: 150px;
            height: 150px;
            border: 2px dashed #ccc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            transition: border-color 0.3s, background-color 0.3s;
            text-align: center;
            font-weight: bold;
            color: #555;
            padding: 10px;
            box-sizing: border-box;
        }

        .dropzone.hovered {
            border-color: #2196F3;
            background-color: #e3f2fd;
        }

        .dropzone.correct {
            border-color: #4CAF50;
            background-color: #e8f5e9;
        }

        .dropzone.occupied {
            border: 2px solid #ccc;
        }

        .dropzone img {
            max-width: 80%;
            max-height: 80px;
            object-fit: contain;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        .dropzone p {
            margin-top: 5px;
            font-size: 1.1em;
        }

        /* Mensagens e Controles */
        #message {
            margin-top: 30px;
            font-size: 24px;
            font-weight: bold;
            min-height: 70px;
            text-align: center;
            color: white;
        }

        #message button {
            background-color: #4c54afff;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 18px;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        #message button:hover {
            background-color: #454ba0ff;
        }

        /* Link de Voltar */
        #a {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        /* Classe para esconder elementos */
        .hidden {
            display: none !important;
        }

        /* Estilos da Tela Final (do Jogo das Vogais) */
        #end-game-screen {
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

        #end-game-screen h2 {
            font-size: 2.5em;
            color: #007bff;
            margin: 0;
        }

        #end-game-screen p {
            font-size: 1.5em;
            color: #343a40;
            margin-top: 5px;
        }

        #end-game-screen img {
            max-width: 180px;
            height: auto;
            margin: 20px 0;
        }

        #end-game-screen a {
            background-color: #007bff;
            color: white;
            padding: 12px 35px;
            font-size: 1.2em;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        #end-game-screen a:hover {
            background-color: #0056b3;
        }

        h2 {
            text-align: center;
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
        <a id="a" href="caminho.php">←</a>
        <h1 id="game-title"></h1>
        <h2>Relacione a letra com a palavra</h2>
        <div class="container">
            <div id="letters-container" class="letters-container"></div>
        </div>
        <div class="container">
            <div id="images-container" class="images-container"></div>
        </div>
        <div id="message"></div>
    </div>

    <div id="end-game-screen" class="hidden">
        <h2>Ótimo trabalho!</h2>
        <p>Se prepare para o próximo desafio.</p>
        <img src="imagens/13.png" alt="Fim de Jogo" />
        <a href="jogo2-2.php">Continuar</a>
    </div>

    <script>
        const allLevels = [{ // Fase 1
                title: 'Jogo das Consoante',
                type: '1',
                pairs: [{
                        letter: 'B',
                        text: 'Bola',
                        imageSrc: '../fases/imagens/bola.png'
                    },
                    {
                        letter: 'C',
                        text: 'Cama',
                        imageSrc: '../fases/imagens/cama.png'
                    },
                    {
                        letter: 'D',
                        text: 'Doce',
                        imageSrc: '../fases/imagens/doce.png'
                    },
                    {
                        letter: 'F',
                        text: 'Foca',
                        imageSrc: '../fases/imagens/foca.png'
                    }
                ]
            },
            { // Fase 2 (ORDENADA)
                title: 'Jogo das Consoantes',
                type: '2',
                pairs: [{
                        letter: 'G',
                        text: 'Gato',
                        imageSrc: '../fases/imagens/gato.png'
                    },
                    {
                        letter: 'H',
                        text: 'Helicóptero',
                        imageSrc: '../fases/imagens/helicoptero.png'
                    },
                    {
                        letter: 'J',
                        text: 'Joaninha',
                        imageSrc: '../fases/imagens/joaninha.png'
                    },
                    {
                        letter: 'K',
                        text: 'Kiwi',
                        imageSrc: '../fases/imagens/kiwi.png'
                    }
                ]
            },
            { // Fase 3
                title: 'Jogo das Consoantes',
                type: '1',
                pairs: [{
                        letter: 'L',
                        text: 'Lua',
                        imageSrc: '../fases/imagens/lua.png'
                    },
                    {
                        letter: 'M',
                        text: 'Mapa',
                        imageSrc: '../fases/imagens/mapa.webp'
                    },
                    {
                        letter: 'N',
                        text: 'Nuvem',
                        imageSrc: '../fases/imagens/nuvem.webp'
                    },
                    {
                        letter: 'P',
                        text: 'Pato',
                        imageSrc: '../fases/imagens/pato.png'
                    }
                ]
            },
            { // Fase 4
                title: 'Jogo das Consoantes',
                type: '1',
                pairs: [{
                        letter: 'Q',
                        text: 'Queijo',
                        imageSrc: '../fases/imagens/queijo.png'
                    },
                    {
                        letter: 'R',
                        text: 'Roupa',
                        imageSrc: '../fases/imagens/roupa.png'
                    },
                    {
                        letter: 'S',
                        text: 'Sabão',
                        imageSrc: '../fases/imagens/sabao.png'
                    },
                    {
                        letter: 'T',
                        text: 'Tatu',
                        imageSrc: '../fases/imagens/tatu.png'
                    }
                ]
            },

            { // Fase 5
                title: 'Jogo das Consoantes',
                type: '1',
                pairs: [{
                        letter: 'V',
                        text: 'Vaca',
                        imageSrc: '../fases/imagens/vaca.png'
                    },
                    {
                        letter: 'W',
                        text: 'Wi-fi',
                        imageSrc: '../fases/imagens/w.png'
                    },
                    {
                        letter: 'X',
                        text: 'Xícara',
                        imageSrc: '../fases/imagens/caneca.png'
                    },
                    {
                        letter: 'Y',
                        text: 'Youtube',
                        imageSrc: '../fases/imagens/youtube.png'
                    },
                    {
                        letter: 'Z',
                        text: 'Zebra',
                        imageSrc: '../fases/imagens/zebra.png'
                    }
                ]
            },
            { // Fase Final
                title: 'Jogo das Vogais',
                type: 'vowels',
                pairs: [{
                        letter: 'A',
                        text: 'Abelha',
                        imageSrc: '../fases/imagens/abelhinha.png'
                    },
                    {
                        letter: 'E',
                        text: 'Elefante',
                        imageSrc: '../fases/imagens/elefante.png'
                    },
                    {
                        letter: 'I',
                        text: 'Ilha',
                        imageSrc: '../fases/imagens/ilha.png'
                    },
                    {
                        letter: 'O',
                        text: 'Ovo',
                        imageSrc: '../fases/imagens/ovo.png'
                    },
                    {
                        letter: 'U',
                        text: 'Uva',
                        imageSrc: '../fases/imagens/uva.png'
                    }
                ]
            }
        ];

        let currentLevel = 0;
        let pairs = [];
        let correctCount = 0;
        let draggedElement = null;

        const gameContent = document.getElementById('game-content');
        const endGameScreen = document.getElementById('end-game-screen');
        const lettersContainer = document.getElementById('letters-container');
        const imagesContainer = document.getElementById('images-container');
        const message = document.getElementById('message');
        const gameTitle = document.getElementById('game-title');

        const shuffle = (array) => {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        };

        const createLetters = () => {
            const shuffledLetters = shuffle([...pairs.map(p => p.letter)]);
            shuffledLetters.forEach(letter => {
                const letterDiv = document.createElement('div');
                letterDiv.className = 'draggable';
                letterDiv.setAttribute('draggable', 'true');
                letterDiv.setAttribute('data-letter', letter);
                letterDiv.textContent = letter;
                lettersContainer.appendChild(letterDiv);
            });
        };

        const createImages = () => {
            const shuffledPairs = shuffle([...pairs]);
            shuffledPairs.forEach(pair => {
                const imageDiv = document.createElement('div');
                imageDiv.className = 'dropzone';
                imageDiv.setAttribute('data-target', pair.letter);
                imageDiv.innerHTML = `<img src="${pair.imageSrc}" alt="${pair.text}"><p>${pair.text}</p>`;
                imagesContainer.appendChild(imageDiv);
            });
        };

        function loadLevel(levelIndex) {
            lettersContainer.innerHTML = '';
            imagesContainer.innerHTML = '';
            message.innerHTML = '';
            correctCount = 0;

            gameContent.classList.remove('hidden');
            endGameScreen.classList.add('hidden');

            const levelData = allLevels[levelIndex];
            pairs = levelData.pairs;
            gameTitle.textContent = levelData.title;

            createLetters();
            createImages();
        }

        function nextLevel() {
            currentLevel++;
            if (currentLevel < allLevels.length) {
                loadLevel(currentLevel);
            }
        }

        document.addEventListener('dragstart', (e) => {
            if (e.target.classList.contains('draggable')) {
                draggedElement = e.target;
                e.dataTransfer.setData('text/plain', e.target.dataset.letter);
                message.textContent = '';
            }
        });

        document.addEventListener('dragover', (e) => {
            const dropzone = e.target.closest('.dropzone');
            if (dropzone && !dropzone.classList.contains('occupied')) {
                e.preventDefault();
                dropzone.classList.add('hovered');
            }
        });

        document.addEventListener('dragleave', (e) => {
            const dropzone = e.target.closest('.dropzone');
            if (dropzone) {
                dropzone.classList.remove('hovered');
            }
        });

        document.addEventListener('drop', (e) => {
            const dropzoneElement = e.target.closest('.dropzone');

            if (dropzoneElement && !dropzoneElement.classList.contains('occupied')) {
                e.preventDefault();
                dropzoneElement.classList.remove('hovered');

                const draggedLetter = e.dataTransfer.getData('text/plain');
                const targetLetter = dropzoneElement.dataset.target;

                if (draggedLetter === targetLetter) {
                    message.textContent = 'Acertou!';
                    message.style.color = 'lightgreen';
                    setTimeout(() => {
                        if (correctCount < pairs.length) message.textContent = '';
                    }, 2000);

                    dropzoneElement.classList.add('correct', 'occupied');
                    draggedElement.style.display = 'none';
                    correctCount++;

                    if (correctCount === pairs.length) {
                        const currentLevelData = allLevels[currentLevel];

                        // --- LÓGICA DE FIM DE JOGO MODIFICADA (SEM SALVAR PROGRESSO) ---
                        if (currentLevelData.type === 'vowels') {
                            message.textContent = 'Parabéns, você completou o jogo!';
                            message.style.color = 'lightgreen';

                            // Opcional: Adiciona um pequeno atraso antes de mostrar a tela final
                            setTimeout(() => {
                                gameContent.classList.add('hidden');
                                endGameScreen.classList.remove('hidden');
                            }, 1500);

                        } else {
                            message.innerHTML = 'Fase completa!<br><button onclick="nextLevel()">Próxima Fase →</button>';
                            message.style.color = 'white'; // Garante que a cor da mensagem e do botão sejam visíveis
                        }
                        // --- FIM DA LÓGICA DE FIM DE JOGO MODIFICADA ---
                    }
                } else {
                    message.textContent = 'Oops! Tente novamente.';
                    message.style.color = 'red';
                }
            }
        });

        loadLevel(currentLevel);
    </script>
</body>

</html>