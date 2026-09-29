
<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['email'];
    $senha = $_POST['senha'];

    // Certifique-se de que 'conexao.php' está no caminho correto
    include "conexao.php";

    // Busca o aluno e o status da conta
    // Alteração 1: Adicionando o campo 'status' à seleção
    $sql = "SELECT id_aluno, email, senha, status FROM cadastroaluno WHERE email = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        $aluno = $resultado->fetch_assoc();

        // Alteração 2: NOVA VERIFICAÇÃO DE STATUS
        if ($aluno['status'] == 'desativado') {
            echo "<script>alert('Sua conta está desativada.'); window.history.back();</script>";
            
            // É importante fechar a conexão e sair após o erro
            $stmt->close();
            $con->close();
            exit; 
        }

        // Verifica a senha informada com o hash do banco (SÓ CONTINUA SE A CONTA ESTIVER ATIVA)
        if (password_verify($senha, $aluno['senha'])) {
            $_SESSION['id_aluno'] = $aluno['id_aluno'];
            header("Location: principal.php");
            exit;
        } else {
            echo "<script>alert('Senha incorreta!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Email não encontrado!'); window.history.back();</script>";
    }

    $stmt->close();
    $con->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body style="background-image: url('../imagens/login.png');">

    <a href="../index.html" style="
  position: absolute;
  top: 30px;
  left: 30px;
  color: rgb(255, 255, 255);
  font-size: 24px;
  font-weight: bold;
  text-decoration: none;
  cursor: pointer;
  z-index: 10;
">←</a>
    <fieldset>

        <h2>Login</h2>
        <form action="login.php" method="POST">

            <label>EMAIL</label>
            <input type="email" id="email" name="email" required>

            <label>SENHA</label>
            <input type="password" id="senha" name="senha" required>

            <button type="submit">Entrar</button>
            <a href="redefinir.php">Esqueceu a senha?</a>
            <a href="cadastro.php">Não possui uma conta?</a>
        </form>

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
</body>
</fieldset>
<style>
    fieldset {
        padding: 60px;
        max-width: 500px;
        position: relative;
    }
</style>

<script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>

</html>