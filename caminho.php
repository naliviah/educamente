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


// Se não existir progresso ainda, começa na fase 1
if (!isset($_SESSION['fase_atual'])) {
    $_SESSION['fase_atual'] = 1;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Sistema de Fases</title>
    <link rel="stylesheet" href="trilha.css">
    <div vw class="enabled">
          <div vw-access-button class="active"></div>
          <div vw-plugin-wrapper>
              <div class="vw-plugin-top-wrapper"></div>
          </div>
      </div>
      <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
      <script>
          new window.VLibras.Widget('https://vlibras.gov.br/app');
      </script>
      <script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>
</head>

<body>
    <a href="../php/principal.php" style="
        position: absolute;
        top: 30px;
        left: 30px;
        color: white;
        font-size: 24px;
        font-weight: bold;
        text-decoration: none;
        cursor: pointer;
        z-index: 10;
    ">←</a>
    <h1>Caminho do Conhecimento</h1>

    <div class="fases">
        <?php for ($i = 1; $i <= 7; $i++): ?>
            <div class="fase <?php echo ($i <= $_SESSION['fase_atual']) ? 'aberta' : 'bloqueada'; ?>">
                <img src="imagens/ovo<?php echo $i; ?>.png"
                    alt="Ovo da fase <?php echo $i; ?>"
                    class="ovo ovo<?php echo $i; ?>">
                <?php if ($i < $_SESSION['fase_atual']): ?>
                    <p class="status">Já concluída!</p>
                <?php elseif ($i == $_SESSION['fase_atual']): ?>
                    <?php if ($i == 1): ?>
                        <a href="video1.php" class="btn">Entrar</a>
                    <?php else: ?>
                        <a href="jogo<?php echo $i; ?>.php" class="btn">Entrar</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="status">Bloqueada</p>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</body>

</html>