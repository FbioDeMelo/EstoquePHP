<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$setor_usuario = $_SESSION['user']['setor'];
$nome_usuario = $_SESSION['user']['nome'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $quantidade = intval($_POST['quantidade']);
    $setor = $_POST['setor'];

    // Verificação de permissão por setor
    if (
        ($setor_usuario == 'Eventos' && $setor != 'Eventos') ||
        ($setor_usuario == 'Geral' && $setor != 'Geral')
    ) {
        die("Você não tem permissão para adicionar nesse setor.");
    }

    // Verifica se o produto já existe com o mesmo nome e setor
    $stmt_check = $conn->prepare("SELECT id, quantidade FROM products WHERE nome = ? AND setor = ?");
    $stmt_check->bind_param("ss", $nome, $setor);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        // Produto já existe: atualiza quantidade
        $produto = $result->fetch_assoc();
        $novo_total = $produto['quantidade'] + $quantidade;

        $stmt_update = $conn->prepare("UPDATE products SET quantidade = ? WHERE id = ?");
        $stmt_update->bind_param("ii", $novo_total, $produto['id']);
        $stmt_update->execute();

        $produto_id = $produto['id'];
    } else {
        // Produto não existe: insere novo
        $stmt_insert = $conn->prepare("INSERT INTO products (nome, quantidade, setor) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("sis", $nome, $quantidade, $setor);
        $stmt_insert->execute();
        $produto_id = $stmt_insert->insert_id;
    }

    // Registrar movimentação como "entrada"
    $stmt_mov = $conn->prepare("INSERT INTO movimentos (produto_id, tipo, quantidade, usuario) VALUES (?, 'entrada', ?, ?)");
    $stmt_mov->bind_param("iis", $produto_id, $quantidade, $nome_usuario);
    $stmt_mov->execute();

    header("Location: dashboard.php");
    exit;
}
?>

<h2>Adicionar Produto</h2>
<form method="POST">
    <input type="text" name="nome" placeholder="Nome do Produto" required>
    <input type="number" name="quantidade" placeholder="Quantidade" required>
    <label>Setor:</label>
    <select name="setor">
        <?php if ($setor_usuario == 'Admin'): ?>
            <option value="Eventos">Eventos</option>
            <option value="Geral">Geral</option>
            <option value="certificados">Certificados</option>
        <?php else: ?>
            <option value="<?= $setor_usuario ?>"><?= $setor_usuario ?></option>
        <?php endif; ?>
    </select>
    <button type="submit">Cadastrar</button>
</form>
