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

// Prepara e executa a consulta
$sql = "SELECT nome_completo, email FROM cadastroaluno WHERE id_aluno = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_aluno);
$stmt->execute();
$result = $stmt->get_result();

// Verifica se o aluno foi encontrado
if ($result->num_rows === 0) {
    echo "Aluno não encontrado.";
    exit;
}

$aluno = $result->fetch_assoc();

$con->close(); // fecha a conexão
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Aluno</title>
    <link rel="stylesheet" href="../css/perfil.css">
    <script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>
</head>

<body>

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

    <div class="container">
        <div class="header">
            <h1>Perfil do Aluno</h1>
        </div>

        <div class="profile-content">
            <div class="profile-image">
                <img src="https://cdn-icons-png.flaticon.com/512/3106/3106921.png" alt="dino">
            </div>

            <div class="profile-fields">
                <div class="input-container">
                    <input name="nome" placeholder="Nome do aluno" disabled value="<?php echo htmlspecialchars($aluno['nome_completo']); ?>">
                    <a class="edit-icon" href="editar.php"></a>
                </div>
                <br><br>
                <div class="input-container">
                    <input name="email" placeholder="Email do aluno" disabled value="<?php echo htmlspecialchars($aluno['email']); ?>">
                    <a class="edit-icon" href="editar.php"></a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>