
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

// 1. Verifique se o aluno está logado. Se não, não continue.
if (!isset($_SESSION['id_aluno'])) {
    die("Erro: Aluno não identificado. Faça o login para continuar.");
}

// 2. Define qual é a fase atual deste arquivo.
$faseDesteJogo = 4; 
$proximaFase = 5; // A fase que será desbloqueada ao concluir este jogo.

// 3. Pega a fase atual do progresso do aluno na sessão.
$progressoAtualDoAluno = $_SESSION['fase_atual'] ?? 1;

// 4. Apenas atualiza o progresso se o aluno estiver concluindo esta fase.
if ($progressoAtualDoAluno == $faseDesteJogo) {
    $con = new mysqli("localhost", "root", "", "educamente");
    if ($con->connect_error) {
        die("Erro de conexão: " . $con->connect_error);
    }
    $id_aluno = $_SESSION['id_aluno'];
    $stmt = $con->prepare("UPDATE progresso SET fase_atual = ? WHERE id_aluno = ? AND fase_atual < ?");
    $stmt->bind_param("iii", $proximaFase, $id_aluno, $proximaFase);
    if ($stmt->execute()) {
        $_SESSION['fase_atual'] = $proximaFase;
    }
    $stmt->close();
    $con->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aventura das Palavras</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #181c92ff;
        }
        .hidden { display: none !important; }
        .game-container {
            text-align: center;
            background-color: #ffffffc0;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            color: #333;
            font-size: 2em;
            margin-bottom: 20px;
        }
        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            height: 250px;
        }
        #word-image {
            max-width: 100%;
            max-height: 250px;
            border-radius: 8px;
        }
        .input-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        #word-input {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1.2em;
            width: 70%;
            text-align: center;
        }
        .game-button {
            padding: 10px 20px;
            background-color: #122f94;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .game-button:hover {
            background-color: #0d226a;
        }
        #feedback-message {
            min-height: 30px;
            font-size: 1.2em;
            font-weight: bold;
        }
        .correct { color:rgb(18, 104, 38); }
        .incorrect { color:rgb(121, 26, 36); }
        #a { position: absolute; top: 20px; left: 20px; color: white; font-size: 28px; font-weight: bold; text-decoration: none; }

        /* --- ESTILOS DA NOVA TELA FINAL --- */
        #end-game-screen { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            display: flex; align-items: center; justify-content: center; 
            background-color: rgba(18, 47, 148, 0.9);
            z-index: 100;
        }
        #end-game-box { 
            background-color: white; color: #333; border-radius: 15px; 
            padding: 40px 50px; width: 90%; max-width: 450px; 
            box-shadow: 0 8px 16px rgba(0,0,0,0.2); 
            display: flex; flex-direction: column; align-items: center; text-align: center; 
        }
        #end-game-box h2 { font-size: 2.5em; color: #007bff; margin: 0; }
        #end-game-box p { font-size: 1.5em; color: #343a40; margin-top: 5px; }
        #end-game-box img { 
            max-width: 180px; /* <<-- TAMANHO DA IMAGEM FINAL AJUSTADO AQUI */
            height: auto; 
            margin: 20px 0; 
        }
        #end-game-box a { 
            background-color: #007bff; color: white; padding: 12px 35px; 
            font-size: 1.2em; font-weight: bold; text-decoration: none; border-radius: 8px; 
            transition: background-color 0.3s, transform 0.2s; 
        }
        #end-game-box a:hover { background-color: #0056b3; transform: scale(1.05); }
    </style>
</head>
<body>
    <a id="a" href="caminho.php">←</a>
    <main class="game-container">
        <h1>Escreva o nome do objeto</h1>
        <div class="image-container">
            <img id="word-image" src="" alt="Imagem da palavra">
        </div>
        <div class="input-container">
            <input type="text" id="word-input" placeholder="Escreva aqui" maxlength="15" autofocus>
            <button id="check-button" class="game-button">Verificar</button>
        </div>
        <div id="feedback-message"></div>
        </main>

    <div id="end-game-screen" class="hidden">
        <div id="end-game-box">
            <h2>Parabéns!</h2>
            <p>Você completou o desafio!</p>
            <audio id="somDeVitoria" preload="auto">
                <source src="vitoria.mp3" type="audio/mpeg">
            </audio>
            <img src="imagens/17.png" alt="Parabéns">
            <a href="caminho.php">Continuar</a>
        </div>
    </div>
    
    <div vw class="enabled"><div vw-access-button class="active"></div><div vw-plugin-wrapper><div class="vw-plugin-top-wrapper"></div></div></div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script> new window.VLibras.Widget("https://vlibras.gov.br/app"); </script>
    <script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>
    <script>
        const words = [
            { name: "cadeira", image: "https://images.vexels.com/media/users/3/148291/isolated/preview/f27be06684b5b9115edf3b641216b4c6-desenho-de-cadeira-de-windsor.png" },
            { name: "mesa",    image: "https://images.vexels.com/media/users/3/208460/isolated/preview/26bf281ff1a4b2acbe988667fda013fe-ilustracao-de-mesa-redonda-de-madeira.png" },
            { name: "lapis",   image: "https://images.vexels.com/media/users/3/153265/isolated/preview/631005e89f6ff9017d236113be33ba85-ilustracao-de-escola-de-lapis.png" },
            { name: "livro",   image: "https://static.vecteezy.com/system/resources/previews/028/700/182/non_2x/blue-book-cartoon-free-png.png" },
            { name: "garrafa", image: "https://images.vexels.com/media/users/3/145688/isolated/preview/7a4ffca7630428839cc53cf346539cae-garrafa-de-cerveja-verde.png" },
            { name: "carro",   image: "https://static.vecteezy.com/system/resources/thumbnails/018/974/668/small_2x/cartoon-car-icon-png.png" },
            { name: "esmalte", image: "https://images.vexels.com/media/users/3/204539/isolated/preview/4bcf75014423193d9394a54d44f29c87-esmalte-colorido-desenhado-a-mao.png" },
            { name: "porta",   image: "https://images.vexels.com/media/users/3/198119/isolated/preview/b7a167b83c4b04525720486cd137077a-porta-isometrica-em-forma-de-arco.png" },
            { name: "copo",    image: "https://static.vecteezy.com/system/resources/previews/017/784/936/non_2x/water-glass-icon-on-transparent-background-free-png.png" },
            { name: "garfo",   image: "https://images.vexels.com/media/users/3/151731/isolated/preview/945c3897d2be2b964c63e44f0ca33f86-icone-plano-de-garfo.png" }
        ];

        let currentWordIndex = 0;

        const gameContainer = document.querySelector('.game-container');
        const endGameScreen = document.getElementById('end-game-screen');
        const wordImage = document.getElementById('word-image');
        const wordInput = document.getElementById('word-input');
        const checkButton = document.getElementById('check-button');
        const feedbackMessage = document.getElementById('feedback-message');

        function loadNewWord() {
            if (currentWordIndex < words.length) {
                const currentWord = words[currentWordIndex];
                wordImage.src = currentWord.image;
                wordInput.value = '';
                feedbackMessage.textContent = '';
                wordInput.focus();
            } else {
                // --- LÓGICA DE FIM DE JOGO ATUALIZADA ---
                gameContainer.classList.add('hidden'); // Esconde o container do jogo
                endGameScreen.classList.remove('hidden'); // Mostra a nova tela final

                try {
                    var audio = document.getElementById('somDeVitoria');
                    audio.currentTime = 0; // Reinicia o áudio
                    audio.play();
                } catch (e) {
                    console.error("Erro ao tocar o áudio:", e);
                }
            }
        }

        function checkAnswer() {
            if (currentWordIndex >= words.length) return;
            const userAnswer = wordInput.value.toLowerCase().trim().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const correctAnswer = words[currentWordIndex].name;
            if (userAnswer === correctAnswer) {
                feedbackMessage.textContent = "Parabéns, você acertou!";
                feedbackMessage.classList.remove('incorrect');
                feedbackMessage.classList.add('correct');
                currentWordIndex++;
                setTimeout(loadNewWord, 1200);
            } else {
                feedbackMessage.textContent = "Ops, tente novamente!";
                feedbackMessage.classList.remove('correct');
                feedbackMessage.classList.add('incorrect');
            }
        }

        checkButton.addEventListener('click', checkAnswer);
        wordInput.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                checkAnswer();
            }
        });

        loadNewWord();
    </script>
</body>
</html>