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


$sql = "SELECT fase_atual FROM progresso WHERE id_aluno = $id_aluno";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    // Já existe progresso salvo
    $row = $result->fetch_assoc();
    $_SESSION['fase_atual'] = $row['fase_atual'];
} else {
    // Ainda não tem progresso salvo, começa na fase_atual 1
    $_SESSION['fase_atual'] = 1;
    $con->query("INSERT INTO progresso (id_aluno, fase_atual) VALUES ($id_aluno, 1)");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página principal</title>
    <link rel="stylesheet" href="../css/pginicial.css">
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
        <main>
            <a href="sair.php" style="
            position: absolute;
            top: 30px;
            left: 30px;
            color: black;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            z-index: 10;">←
            </a>
            <section class="welcome-banner">
                <h1 class="welcome-title">
                    BEM-VINDO À NOSSA JORNADA<br>
                    DE ALFABETIZAÇÃO!
                </h1>
                <p class="welcome-subtitle">
                    Ajudamos todos a aprenderem a escrever e ler de uma forma<br>
                    divertida e interativa
                </p>
            </section>

            <section class="img">
                <img src="../imagens/inicial.png" alt="dragão brincando" height="320px">
            </section>

            <nav class="navigation-buttons">
                <button class="nav-button btn-knowledge">
                    <a href="../fases/caminho.php">CAMINHO DO<br>CONHECIMENTO</a>
                </button>
                <button class="nav-button btn-student">
                    <a href="perfil.php">ALUNO</a>
                </button>
                <button class="nav-button btn-activities">
                    <a href="../php/atvcomp.php">ATIVIDADES<br>COMPLEMENTARES</a>
                </button>
            </nav>
        </main>
</body>
</html>