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

// Obtém o ID do aluno
$id_aluno = $_SESSION['id_aluno'];

// Verifica se o formulário foi enviado para atualizar os dados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_nome = $_POST['nome_completo'];
    $novo_email = $_POST['email'];

    // Prepara a consulta para atualizar os dados
    $sql = "UPDATE cadastroaluno SET nome_completo = ?, email = ? WHERE id_aluno = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssi", $novo_nome, $novo_email, $id_aluno);

    // Executa a consulta
    if ($stmt->execute()) {
        echo "<script>alert('Dados atualizados com sucesso!'); window.location.href='perfil.php';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar os dados.');</script>";
    }

    $stmt->close();
}

// Obtém os dados atuais do aluno
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
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../css/perfil.css">
    <script src="https://cdn.userway.org/widget.js" data-account="VVV2wxBC1o"></script>
</head>

<body>

    <a href="perfil.php" style="
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
            <h1>Editar Perfil</h1>
        </div>

        <div class="profile-content">
            <div class="profile-image">
                <img src="https://cdn-icons-png.flaticon.com/512/3106/3106921.png" alt="dino">
            </div>

            <form action="editar.php" method="POST" class="profile-fields">
                <div class="input-container">
                    <label for="nome_completo">Nome do aluno</label>
                    <input type="text" name="nome_completo" value="<?php echo htmlspecialchars($aluno['nome_completo']); ?>" required>
                </div>
                <br><br>
                <div class="input-container">
                    <label for="email">Email do aluno</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($aluno['email']); ?>" required>
                </div>

                <br><br>
                <button type="submit" class="btn">Salvar Alterações</button>
            </form>
        </div>
    </div>

<style>
    *{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #2E4BC6 0%, #1E3A8A 100%);
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    padding: 25px;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 40px 20px;
}

.header {
    display: flex;
    align-items: center;
    margin-bottom: 60px;
}

.voltar {
    color: white;
    font-size: 24px;
    margin-right: 20px;
    cursor: pointer;
    padding: 8px;
}

h1 {
    color: white;
    font-size: 32px;
    font-weight: 600;
    text-align: center;
    flex: 1;
    margin-right: 52px; /* Compensate for arrow width */
}

.profile-content {
    display: flex;
    align-items: center;
    gap: 80px;
    flex-wrap: wrap;
}

.profile-image {
    width: 280px;
    height: 280px;
    border-radius: 50%;
    background: #F5F5DC;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.profile-image img {
    width: 200px;
    height: 200px;
    object-fit: cover;
}

.profile-fields {
    flex: 1;
    min-width: 300px;
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.input-container {
    position: relative;
}

input {
    width: 100%;
    padding: 20px 60px 20px 25px;
    border: none;
    border-radius: 25px;
    background: white;
    font-size: 16px;
    font-weight: 500;
    color: #374151;
    outline: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

input::placeholder {
    color: #6B7280;
    font-weight: 400;
}

.edit-icon {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    cursor: pointer;
    color: #1E3A8A;
    text-decoration: none;
}

.edit-icon::before {
    content: "🖉";
    font-size: 18px;

}

@media (max-width: 768px) {
    .profile-content {
        flex-direction: column;
        gap: 40px;
        text-align: center;
    }

    .profile-image {
        width: 200px;
        height: 200px;
    }

    .profile-image img {
        width: 140px;
        height: 140px;
    }

    h1 {
        font-size: 24px;
        margin-right: 0;
    }

    .profile-fields {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 20px 10px;
    }

    input {
        padding: 18px 50px 18px 20px;
        font-size: 14px;
    }
}

.btn {
    margin-top: -24px;
    padding: 10px;
    color: #1E3A8A;
    font-size: larger;
    background-color: whitesmoke;
    border-radius: 26px;
    border: none;
}

.btn:hover{
    background-color: aliceblue;
}

label{
    color: aliceblue;
} 
</style>


</body>

</html>