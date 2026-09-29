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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Jogo de Rimas</title>
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
            /* Cor de fundo azul sólida */
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        h1 {
            color: white;
            font-size: 2.5em;
            font-weight: 800;
            /* Mais peso para o título */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
            /* Espaço abaixo do título */
            text-align: center;
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

        .game-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 60px;
            background-color: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 800px;
            flex-wrap: wrap;
        }

        .main-item {
            text-align: center;
        }

        .main-item img {
            max-width: 250px;
            height: auto;
            border-radius: 10px;
        }

        .main-item p {
            font-size: 2.5em;
            font-weight: 800;
            color: #007bff;
            /* Palavra principal em azul */
            margin-top: 15px;
        }

        .options-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .option {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            background-color: #ffffff;
            /* Fundo branco para as opções */
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #ddd;
            width: 250px;
        }

        .option:hover {
            transform: scale(1.05);
            border-color: #007bff;
        }

        .option img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .option p {
            margin: 0;
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
        }

        #message {
            margin-top: 30px;
            font-size: 2em;
            font-weight: bold;
            height: 40px;
            /* A cor será definida via JS */
        }

        .hidden {
            display: none !important;
        }

        #message {
            text-align: center;
        }

        /* Estilos da tela final permanecem os mesmos */
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
    <div id="game-content">
        <h1>O QUE RIMA COM:</h1>
        <div class="game-container">
            <div class="main-item" id="main-rhyme"></div>
            <div class="options-container" id="options"></div>
        </div>
        <div id="message"></div>
    </div>

    <div id="end-game-screen" class="hidden">
        <div id="end-game-box">
            <h2>Ótimo trabalho!</h2>
            <p>Se prepare para o próximo desafio.</p>
            <img src="../fases/imagens/15.png" alt="Fim de Jogo" /> <a href="jogo1-3.php">Continuar</a>
        </div>
    </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // A lista de fases (FASES) continua aqui, como na versão anterior.
            // O código JavaScript não precisa ser alterado.
            const FASES = [{
                    titulo: 'GIRAFA',
                    imagem_principal: '../fases/imagens/girafinha.png',
                    rima_correta: 'GARRAFA',
                    opcoes: [{
                            palavra: 'GARRAFA',
                            imagem: '../fases/imagens/garrafa.png'
                        },
                        {
                            palavra: 'SEREIA',
                            imagem: '../fases/imagens/sereia.png'
                        },
                        {
                            palavra: 'PICOLÉ',
                            imagem: '../fases/imagens/picole.png'
                        }
                    ]
                },
                {
                    titulo: 'BONECA',
                    imagem_principal: '../fases/imagens/boneca.png',
                    rima_correta: 'CANECA',
                    opcoes: [{
                            palavra: 'CANECA',
                            imagem: '../fases/imagens/caneca.png'
                        },
                        {
                            palavra: 'MALA',
                            imagem: '../fases/imagens/mala.webp'
                        },
                        {
                            palavra: 'VENTILADOR',
                            imagem: '../fases/imagens/ventilador.webp'
                        }
                    ]
                },
                {
                    titulo: 'PÃO',
                    imagem_principal: '../fases/imagens/pao.png',
                    rima_correta: 'MÃO',
                    opcoes: [{
                            palavra: 'OLHO',
                            imagem: '../fases/imagens/olho.png'
                        },
                        {
                            palavra: 'MÃO',
                            imagem: '../fases/imagens/mao.png'
                        },
                        {
                            palavra: 'ROSTO',
                            imagem: '../fases/imagens/rosto.png'
                        }
                    ]
                },
                {
                    titulo: 'ANEL',
                    imagem_principal: '../fases/imagens/anel.png',
                    rima_correta: 'PINCEL',
                    opcoes: [{
                            palavra: 'PINCEL',
                            imagem: '../fases/imagens/pincel.png'
                        },
                        {
                            palavra: 'FACA',
                            imagem: '../fases/imagens/faca.png'
                        },
                        {
                            palavra: 'LÁPIS',
                            imagem: '../fases/imagens/lapis.png'
                        }
                    ]
                },
                {
                    titulo: 'JANELA',
                    imagem_principal: '../fases/imagens/janela.png',
                    rima_correta: 'PANELA',
                    opcoes: [{
                            palavra: 'CAMA',
                            imagem: '../fases/imagens/cama.png'
                        },
                        {
                            palavra: 'PANELA',
                            imagem: '../fases/imagens/panela.png'
                        },
                        {
                            palavra: 'SOFÁ',
                            imagem: '../fases/imagens/sofa.png'
                        }
                    ]
                },
                {
                    titulo: 'GATO',
                    imagem_principal: '../fases/imagens/gato.png',
                    rima_correta: 'PATO',
                    opcoes: [{
                            palavra: 'PATO',
                            imagem: '../fases/imagens/pato.png'
                        },
                        {
                            palavra: 'LEÃO',
                            imagem: '../fases/imagens/leao.png'
                        },
                        {
                            palavra: 'SAPO',
                            imagem: '../fases/imagens/sapo.png'
                        }
                    ]
                },
                {
                    titulo: 'SOLDADO',
                    imagem_principal: '../fases/imagens/soldado.png',
                    rima_correta: 'DADO',
                    opcoes: [{
                            palavra: 'BOLA',
                            imagem: '../fases/imagens/bola.png'
                        },
                        {
                            palavra: 'DADO',
                            imagem: '../fases/imagens/dado.png'
                        },
                        {
                            palavra: 'CARRO',
                            imagem: '../fases/imagens/carro.webp'
                        }
                    ]
                },
                {
                    titulo: 'COELHO',
                    imagem_principal: '../fases/imagens/coelho.png',
                    rima_correta: 'ESPELHO',
                    opcoes: [{
                            palavra: 'ESPELHO',
                            imagem: '../fases/imagens/espelho.png'
                        },
                        {
                            palavra: 'PENTE',
                            imagem: '../fases/imagens/pente.png'
                        },
                        {
                            palavra: 'ESCOVA',
                            imagem: '../fases/imagens/escova.png'
                        }
                    ]
                }
            ];

            let nivelAtual = 0;
            let podeJogar = true;

            const gameContent = document.getElementById('game-content');
            const endGameScreen = document.getElementById('end-game-screen');
            const mainRhymeDiv = document.getElementById("main-rhyme");
            const optionsDiv = document.getElementById("options");
            const messageDiv = document.getElementById("message");

            function renderizarFase(nivel) {
                podeJogar = true;
                messageDiv.innerHTML = '';
                optionsDiv.innerHTML = '';

                const faseAtualData = FASES[nivel];

                mainRhymeDiv.innerHTML = `
                    <img src="${faseAtualData.imagem_principal}" alt="${faseAtualData.titulo}">
                    <p>${faseAtualData.titulo}</p>
                `;

                const opcoesEmbaralhadas = [...faseAtualData.opcoes].sort(() => Math.random() - 0.5);

                opcoesEmbaralhadas.forEach(opcao => {
                    const optionDiv = document.createElement("div");
                    optionDiv.className = "option";
                    optionDiv.setAttribute("data-word", opcao.palavra);

                    optionDiv.innerHTML = `
                        <img src="${opcao.imagem}" alt="${opcao.palavra}">
                        <p>${opcao.palavra}</p>
                    `;

                    optionDiv.addEventListener("click", () => handleOptionClick(optionDiv, faseAtualData.rima_correta));
                    optionsDiv.appendChild(optionDiv);
                });
            }

            function handleOptionClick(elementoClicado, rimaCorreta) {
                if (!podeJogar) return;

                const palavraSelecionada = elementoClicado.getAttribute('data-word');

                if (palavraSelecionada === rimaCorreta) {
                    podeJogar = false;
                    messageDiv.textContent = "Parabéns, você acertou!";
                    messageDiv.style.color = "#36ca58ff";
                    elementoClicado.classList.add("correct");

                    setTimeout(() => {
                        nivelAtual++;
                        if (nivelAtual < FASES.length) {
                            renderizarFase(nivelAtual);
                        } else {
                            gameContent.classList.add('hidden');
                            endGameScreen.classList.remove('hidden');
                        }
                    }, 1500);

                } else {
                    messageDiv.textContent = "Ops, não rima. Tente de novo!";
                    messageDiv.style.color = "#dc3545";
                    elementoClicado.classList.add("incorrect");
                    setTimeout(() => {
                        elementoClicado.classList.remove("incorrect");
                        messageDiv.textContent = "";
                    }, 1000);
                }
            }
            renderizarFase(nivelAtual);
        });
    </script>

</body>

</html>