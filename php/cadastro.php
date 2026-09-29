<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomeAluno = $_POST["nome_completo"];
    $idade = $_POST["idade"];
    $email = $_POST["email"];
    $telefone = $_POST["telefone"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $nomeResp = $_POST['nome_resp'];
    $emailResp = $_POST['email_resp'];
    $telefoneResp = $_POST['telefone_resp'];
    $parentesco = $_POST['parentesco'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('E-mail do aluno inválido! Verifique e tente novamente.'); window.history.back();</script>";
        exit;
    }

    if (!filter_var($emailResp, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('E-mail do responsável inválido! Verifique e tente novamente.'); window.history.back();</script>";
        exit;
    }

    include "conexao.php";

    // Primeiro insere o aluno
    $sqlAluno = "INSERT INTO cadastroaluno (nome_completo, idade, email, telefone, senha) VALUES ('$nomeAluno', '$idade', '$email', '$telefone', '$senha')";
    if (mysqli_query($con, $sqlAluno)) {
        // Pega o ID do aluno inserido
        $id_aluno = mysqli_insert_id($con);
        // Salva o ID na sessão
        $_SESSION['id_aluno'] = $id_aluno;

        // Agora insere o responsável
        $sqlResp = "INSERT INTO cadastroresponsavel (nome_resp, email_resp, telefone_resp, parentesco) VALUES ('$nomeResp', '$emailResp', '$telefoneResp', '$parentesco')";
        if (mysqli_query($con, $sqlResp)) {
            echo "<script>alert('Usuário cadastrado com sucesso!'); window.location.href='login.php';</script>";
            exit;
        } else {
            echo "<script>alert('Erro ao cadastrar responsável: " . mysqli_error($con) . "'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Erro ao cadastrar aluno: " . mysqli_error($con) . "'); window.history.back();</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>
</head>

<body style="background-image: url('../imagens/cadastro.png');">
    <a href="../index.html" style="
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

    <fieldset>
        <div id="cadastro">
            <form action="../php/cadastro.php" method="POST">
                <h2>DADOS DO ALUNO</h2>

                <label>NOME COMPLETO</label>
                <input type="text" name="nome_completo" required>

                <label>IDADE</label>
                <input type="text" name="idade" required>

                <label>TELEFONE</label>
                <input type="text" name="telefone" required>

                <label>EMAIL</label>
                <input type="email" name="email" required>

                <label>SENHA</label>
                <input type="password" name="senha" required>
                <br>
                <h2>DADOS DO RESPONSÁVEL</h2>

                <label>NOME COMPLETO</label>
                <input type="text" name="nome_resp" required>

                <label>EMAIL</label>
                <input type="email" name="email_resp" required>

                <label>TELEFONE</label>
                <input type="text" name="telefone_resp" required>

                <label>PARENTESCO</label>
                <input type="text" name="parentesco" required>

                <label style="display: flex; align-items: center; gap: 5px;">
                    <input type="checkbox" required>
                    <span>
                        Ao continuar, você concorda com os Termos de Uso do Educamente.
                        <a href="../html/politica.html">Leia nossa Política de Privacidade.</a>
                    </span>
                </label>
                <br>

                <button type="submit">CADASTRE-SE</button>
            </form>

        </div>

    </fieldset>
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
    <br>
    </div>
</body>
<style>
    fieldset {
        padding: 80px;
        max-width: 800px;
        position: relative;
    }
</style>

</html>