<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['setor'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
$senha = $_POST['senha'];
    $setor = $_POST['setor'];
    $ativo = $_POST['ativo'];

    $stmt = $conn->prepare("INSERT INTO users (nome, email, senha, setor, ativo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $nome, $email, $senha, $setor, $ativo);

    if ($stmt->execute()) {
        echo "Usuário cadastrado com sucesso!";
    } else {
        echo "Erro ao cadastrar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastro de Usuário</title>
</head>
<body>
    <h2>Cadastrar Novo Usuário</h2>
    <form method="POST" action="">
        <label>Nome:</label><br>
        <input type="text" name="nome" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Senha:</label><br>
        <input type="password" name="senha" required><br><br>

        <label>Setor:</label><br>
        <select name="setor" required>
            <option value="Admin">Admin</option>
            <option value="Eventos">Eventos</option>
            <option value="Geral">Geral</option>
        </select><br><br>

        <label>Status:</label><br>
        <select name="ativo" required>
            <option value="1" selected>Ativo</option>
            <option value="0">Desativado</option>
        </select><br><br>

        <button type="submit">Cadastrar</button>
    </form>
    <br>
    <a href="dashboard.php">Voltar ao Dashboard</a>
</body>
</html>
