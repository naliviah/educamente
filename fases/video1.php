<?php
session_start();
$faseAtual = 1;
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
            background-color: #122f94; /* Cor de fundo azul sólida */
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        #a { position: absolute; top: 20px; left: 20px; color: white; font-size: 28px; font-weight: bold; text-decoration: none; }

        #a2 { position: absolute; top: 680px; left: 700px; color: white; font-size: 28px; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>
    <a id="a" href="caminho.php">←</a>
    <iframe width="1000" height="600" src="https://www.youtube.com/embed/FXylvtji5uM?si=CnGqpJQRIQO8I0fP" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    <a id="a2" href="jogo1.php">Jogar →</a>
</body>
</html>