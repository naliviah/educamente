<?php
require_once 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['password'];

    // Buscar o aluno pelo e-mail
    $sql = "SELECT * FROM cadastroaluno WHERE email = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows === 1) {
        $aluno = $resultado->fetch_assoc();

        // Verificar a senha digitada com a armazenada (criptografada)
        if (password_verify($senha, $aluno['senha'])) {
            // Atualiza o status para 'desativado'
            $update = "UPDATE cadastroaluno SET status = 'desativado' WHERE email = ?";
            $stmtUpdate = $con->prepare($update);
            $stmtUpdate->bind_param("s", $email);
            if ($stmtUpdate->execute()) {
                echo "<script>alert('Conta desativada com sucesso!'); window.location.href='../index.html';</script>";
                exit;
            } else {
                echo "<script>alert('Erro ao desativar a conta.'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Senha incorreta.'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Email não encontrado.'); window.history.back();</script>";
        exit;
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
    <title>Excluir conta</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>
</head>

<body style="background-image: url('../imagens/excluir.png');">
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
    <fieldset><br>
        <h2>Excluir conta</h2>
        <form action="excluir.php" method="POST">
            <input type="email" id="email" name="email" placeholder="Email">
            <input type="password" id="password" name="password" placeholder="Senha">
            <button type="submit">Excluir</button><br>

        </form>

    </fieldset>

</body>

</html>