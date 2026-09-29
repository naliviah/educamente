
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conexão com o banco de dados
    $server = "localhost";
    $user = "root";
    $pass = "";
    $bd = "educamente";

    $conn = new mysqli($server, $user, $pass, $bd);

    if ($conn->connect_error) {
        // Em um ambiente de produção, não exponha o erro ao usuário.
        die("Erro na conexão: " . $conn->connect_error);
    }

    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirmarsenha = $_POST['confirmarsenha'];

    if ($senha !== $confirmarsenha) {
        echo "<script>alert('As senhas não coincidem.'); window.history.back();</script>";
    } else {
        
        $sql_check = "SELECT id_aluno FROM cadastroaluno WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $resultado_check = $stmt_check->get_result();

        if ($resultado_check->num_rows === 0) {
            echo "<script>alert('E-mail não encontrado. Verifique se digitou corretamente.'); window.history.back();</script>";
        } else {

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sql_update = "UPDATE cadastroaluno SET senha = ? WHERE email = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ss", $senhaHash, $email);

            if ($stmt_update->execute()) {
                echo "<script>alert('Sua senha foi redefinida!'); window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('Erro ao redefinir senha.'); window.history.back();</script>";
            }

            $stmt_update->close();
        }
        
        $stmt_check->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir senha</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>
</head>
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

<body style="background-image: url('../imagens/redefinir.png');">
    <a href="../php/login.php" style="
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
    <fieldset><br>



        <h2>Redefinir senha</h2>
        <form action="redefinir.php" method="POST">
            <input type="email" id="email" name="email" placeholder="Email" required>
            <input type="password" id="password" name="senha" placeholder="Nova Senha" required>
            <input type="password" id="confirmsenha" name="confirmarsenha" placeholder="Confirmar Nova Senha" required>
            <button type="submit">Redefinir</button><br>

        </form>

    </fieldset>

</body>

</html>