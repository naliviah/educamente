
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

// --- 1. CONFIGURAÇÃO DE TODAS AS FASES DO JOGO ---
// Adicionei mais exemplos para você!
$fases = [
    1 => [
        'pergunta' => 'Qual dessas frutas começa com a letra M?',
        'imagem_principal' => null,
        'opcoes' => [
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/maca.png', 'texto' => 'Maçã', 'alt' => 'Maçã'],
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/banana.png', 'texto' => 'Banana', 'alt' => 'Banana'],
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/uva.png', 'texto' => 'Uva', 'alt' => 'Uva'],
        ],
        'resposta_certa' => 'Maçã'
    ],
    2 => [
        'pergunta' => 'Com qual letra começa a palavra CACHORRO?',
        'imagem_principal' => '../fases/imagens/cachorro.png',
        'opcoes' => [
            ['tipo' => 'texto', 'valor' => 'T', 'texto' => 'T'],
            ['tipo' => 'texto', 'valor' => 'G', 'texto' => 'G'],
            ['tipo' => 'texto', 'valor' => 'C', 'texto' => 'C'],
        ],
        'resposta_certa' => 'C'
    ],
    3 => [
        'pergunta' => 'Qual animal abaixo começa com a letra G?',
        'imagem_principal' => null,
        'opcoes' => [
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/sapo.png', 'texto' => 'Sapo', 'alt' => 'Sapo'],
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/gato.png', 'texto' => 'Gato', 'alt' => 'Gato'],
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/pato.png', 'texto' => 'Pato', 'alt' => 'Pato'],
        ],
        'resposta_certa' => 'Gato'
    ],
    4 => [
        'pergunta' => 'Com qual letra começa a palavra SOL?',
        'imagem_principal' => '../fases/imagens/sol.png',
        'opcoes' => [
            ['tipo' => 'texto', 'valor' => 'S', 'texto' => 'S'],
            ['tipo' => 'texto', 'valor' => 'L', 'texto' => 'L'],
            ['tipo' => 'texto', 'valor' => 'F', 'texto' => 'F'],
        ],
        'resposta_certa' => 'S'
    ],
    // --- NOVOS EXEMPLOS ---
    5 => [
        'pergunta' => 'Qual objeto começa com a letra B?',
        'imagem_principal' => null,
        'opcoes' => [
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/pipa.webp', 'texto' => 'Pipa', 'alt' => 'Pipa'],
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/bola.png', 'texto' => 'Bola', 'alt' => 'Bola'],
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/carro.webp', 'texto' => 'Carro', 'alt' => 'Carro'],
        ],
        'resposta_certa' => 'Bola'
    ],
    6 => [
        'pergunta' => 'Com qual letra começa ABELHA?',
        'imagem_principal' => '../fases/imagens/abelhinha.png',
        'opcoes' => [
            ['tipo' => 'texto', 'valor' => 'E', 'texto' => 'E'],
            ['tipo' => 'texto', 'valor' => 'O', 'texto' => 'O'],
            ['tipo' => 'texto', 'valor' => 'A', 'texto' => 'A'],
        ],
        'resposta_certa' => 'A'
    ],
    7 => [
        'pergunta' => 'Qual desses animais começa com a letra O?',
        'imagem_principal' => null,
        'opcoes' => [
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/ovelha.webp', 'texto' => 'Ovelha', 'alt' => 'Ovelha'],
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/vaca.png', 'texto' => 'Vaca', 'alt' => 'Vaca'],
            ['tipo' => 'imagem', 'valor' => '../fases/imagens/porco.webp', 'texto' => 'Porco', 'alt' => 'Porco'],
        ],
        'resposta_certa' => 'Ovelha'
    ]
];

// --- LÓGICA PARA CONTROLAR A FASE ATUAL (NÃO PRECISA MEXER AQUI) ---
$nivelAtual = $_SESSION['nivel_atual_sons'] ?? 1;
if (isset($_GET['acao']) && $_GET['acao'] === 'proximo_nivel') {
    $nivelAtual++;
    $_SESSION['nivel_atual_sons'] = $nivelAtual;
    header("Location: jogo1.php");
    exit();
}
if (!isset($fases[$nivelAtual])) {
    unset($_SESSION['nivel_atual_sons']);
    $jogoAcabou = true;
} else {
    $jogoAcabou = false;
    $fase = $fases[$nivelAtual];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Jogo dos Sons Iniciais</title>
    
    <style>
        /* Fonte mais amigável importada do Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap');

        /* Animação para o texto de resultado */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body { 
            margin: 0; 
            font-family: 'Nunito', sans-serif; /* Nova fonte aplicada */
            /* Fundo com gradiente suave */
            background-color: #122f94ff;
            color: white; 
            text-align: center; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #a { position: absolute; top: 20px; left: 20px; color: white; font-size: 28px; font-weight: bold; text-decoration: none; }
        
        h1 { 
            font-size: 2.5em; /* Título um pouco maior */
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2); /* Sombra no texto */
            margin-top: 50px;
        }
        p.sub { color: #ccc; margin-top: -10px; font-size: 1.2em; }
        
        .container { display: flex; justify-content: center; gap: 30px; margin-top: 40px; flex-wrap: wrap; }
        
        .card { 
            background: white; 
            color: black; 
            border-radius: 16px; 
            border: 1px solid #ddd; /* Borda sutil */
            padding: 15px; 
            width: 18%; 
            min-height: 260px; 
            text-align: center; 
            cursor: pointer; 
            /* Transição mais suave para hover */
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
            /* Sombra mais suave */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15); 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
        }
        .card:hover { 
            transform: translateY(-8px) scale(1.03); /* Efeito de "levantar" */
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2); 
        }
        .card img { width: 130px; height: auto; }
        .card p { margin-top: 15px; font-size: 1.8em; font-weight: bold; }
        
        .acerto { 
            background-color: #d4edda; 
            border: 3px solid #28a745; 
            transform: scale(1.05); /* Destaca o card certo */
        }
        .erro { 
            background-color: #f8d7da; 
            border: 3px solid #dc3545; 
            animation: shake 0.5s; /* Animação de tremer no erro */
        }

        /* Animação para o card errado */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-8px); }
            40%, 80% { transform: translateX(8px); }
        }

        #resultado { 
            font-size: 1.5em; /* Fonte maior */
            text-align: center; 
            min-height: 30px; 
            margin-top: 5px;
            animation: fadeIn 0.5s ease-in-out; /* Animação aplicada */
        }

        .hidden { display: none !important; }

        /* Estilos da tela final (mantidos e centralizados) */
        #end-game-screen, #game-content { width: 100%; padding: 20px; box-sizing: border-box; }
        #end-game-screen-inner { background-color: white; color: #333; border-radius: 15px; padding: 40px 50px; width: 90%; max-width: 450px; box-shadow: 0 8px 16px rgba(0,0,0,0.2); display: flex; flex-direction: column; align-items: center; margin: 40px auto; }
        #end-game-screen-inner h2 { font-size: 2.5em; color: #007bff; margin: 0; }
        #end-game-screen-inner p { font-size: 1.5em; color: #343a40; margin-top: 5px; }
        #end-game-screen-inner img { max-width: 180px; height: auto; margin: 20px 0; }
        #end-game-screen-inner a { background-color: #007bff; color: white; padding: 12px 35px; font-size: 1.2em; font-weight: bold; text-decoration: none; border-radius: 8px; transition: background-color 0.3s, transform 0.2s; }
        #end-game-screen-inner a:hover { background-color: #0056b3; transform: scale(1.05); }

        #imagem-principal { max-width: 120px; height: auto; margin-bottom: 5px; }
    </style>
</head>
<body>

    <div id="game-content" class="<?php if ($jogoAcabou) echo 'hidden'; ?>">
        <a id="a" href="caminho.php">←</a>
        <h1><?php echo $fase['pergunta']; ?></h1>
        <p class="sub">Clique na resposta certa</p>
        
        <?php if ($fase['imagem_principal']): ?>
            <img id="imagem-principal" src="<?php echo $fase['imagem_principal']; ?>" alt="Imagem da fase" />
        <?php endif; ?>

        <div class="container">
            <?php foreach ($fase['opcoes'] as $opcao): 
                $dataResposta = ($opcao['texto'] === $fase['resposta_certa']) ? 'certa' : 'errada';
            ?>
                <div class="card" data-resposta="<?php echo $dataResposta; ?>">
                    <?php if ($opcao['tipo'] === 'imagem'): ?>
                        <img src="<?php echo $opcao['valor']; ?>" alt="<?php echo $opcao['alt']; ?>" />
                    <?php endif; ?>
                    <p class="nome"><?php echo $opcao['texto']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div id="resultado"></div>
    </div>

    <div id="end-game-screen" class="<?php if (!$jogoAcabou) echo 'hidden'; ?>">
        <div id="end-game-screen-inner">
            <h2>Ótimo trabalho!</h2>
            <p>Se prepare para o próximo desafio.</p>
            <img src="../fases/imagens/15.png" alt="Fim de Jogo" /> <a href="video2.php">Continuar</a>
        </div>
    </div>

    <div vw class="enabled"><div vw-access-button class="active"></div><div vw-plugin-wrapper><div class="vw-plugin-top-wrapper"></div></div></div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script> new window.VLibras.Widget("https://vlibras.gov.br/app"); </script>
    <script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            if (document.getElementById('game-content').classList.contains('hidden')) return;

            const cards = document.querySelectorAll(".card");
            const resultado = document.getElementById('resultado');
            let podeJogar = true;

            cards.forEach((card) => {
                card.addEventListener("click", () => {
                    if (!podeJogar) return;

                    const resposta = card.getAttribute("data-resposta");

                    // Limpa o resultado anterior antes de mostrar o novo
                    resultado.innerHTML = '';
                    cards.forEach(c => c.classList.remove("acerto", "erro"));

                    if (resposta === "certa") {
                        podeJogar = false;
                        card.classList.add("acerto");
                        resultado.innerHTML = `<b>Resposta certa!</b>`;

                        setTimeout(() => {
                            window.location.href = "jogo1.php?acao=proximo_nivel";
                        }, 1500);

                    } else {
                        card.classList.add("erro");
                        resultado.innerHTML = `<b>Resposta errada! Tente novamente.</b>`;
                        // Remove a classe de erro depois da animação para permitir nova tentativa
                        setTimeout(() => {
                           card.classList.remove('erro');
                        }, 600);
                    }
                });
            });
        });
    </script>
</body>
</html>