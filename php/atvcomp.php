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
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Atividades Complementares</title>
  <script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>
</head>

<body style="background-image: url('../imagens/atv.png');">
  <div class="container">

    <a href="principal.php" style="
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
  </div>

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

  <!-- título fora da container -->
  <h1 id="ativi-comp">ATIVIDADES COMPLEMENTARES</h1>
  <br><br><br>

  <div class="container">
    <!-- Coluna Exercícios -->
    <div class="card">
      <h2>EXERCÍCIOS</h2>
      <a href="../atv1.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>ATIVIDADE 1</span></div>
      </a>
      <a href="../atv2.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>ATIVIDADE 2</span></div>
      </a>
      <a href="../atv3.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>ATIVIDADE 3</span></div>
      </a>
      <a href="../atv4.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>ATIVIDADE 4</span></div>
      </a>
      <a href="../atv5.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>ATIVIDADE 5</span></div>
      </a>
      <a href="../atv6.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>ATIVIDADE 6</span></div>
      </a>
    </div>

    <!-- Coluna Leitura -->
    <div class="card">
      <h2>LEITURA</h2>
      <a href="../historia1.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>HISTÓRIA 1</span></div>
      </a>
      <a href="../historia2.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>HISTÓRIA 2</span></div>
      </a>
      <a href="../historia3.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>HISTÓRIA 3</span></div>
      </a>
      <a href="../historia4.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>HISTÓRIA 4</span></div>
      </a>
      <a href="../historia5.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>HISTÓRIA 5</span></div>
      </a>
      <a href="../historia6.pdf" download>
        <div class="item"><img src="../imagens/pdf.png"><span>HISTÓRIA 6</span></div>
      </a>
    </div>
  </div>
</body>
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 30px;
    background: url('../imagens/fundo.png') no-repeat center center fixed;
    background-size: cover;
  }

  #ativi-comp {
    text-align: center;
    font-size: 32px;
    margin: 40px 0;
  }

  .container {
    display: flex;
    gap: 40px;
    width: 90%;
    max-width: 1100px;
    justify-content: center;
    margin: 0 auto;
  }

  .card {
    flex: 1;
    background: rgba(255, 255, 255, 0.693);
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
  }

  h2 {
    color: #1d3faa;
    font-size: 20px;
    margin-bottom: 20px;
  }

  .item {
    display: flex;
    align-items: center;
    background-color: #c7d3f3;
    padding: 15px;
    margin: 10px 0;
    border-radius: 12px;
    cursor: pointer;
    transition: 0.3s;
  }

  .item:hover {
    background-color: #aebdea;
  }

  .item img {
    width: 35px;
    margin-right: 12px;
  }

  .item span {
    font-size: 16px;
    color: #000;
    font-weight: bold;
  }
</style>

</html>