<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$setor_usuario = $_SESSION['user']['setor'];
$nome_usuario = $_SESSION['user']['nome'];

$erro = '';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $quantidade = intval($_POST['quantidade']);
    $setor = $setor_usuario === 'Admin' ? $_POST['setor'] : $setor_usuario;

    // Valida setor para usuários não-admin
    if ($setor_usuario !== 'Admin' && $setor !== $setor_usuario) {
        die("Você não tem permissão para adicionar nesse setor.");
    }

    // Verifica se o produto já existe com o mesmo nome (independente do setor informado)
    $stmt_check = $conn->prepare("SELECT id, quantidade, setor FROM products WHERE nome = ?");
    $stmt_check->bind_param("s", $nome);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $produto = $result->fetch_assoc();

        // Se o setor do produto for diferente e o usuário não for Admin, bloqueia
        if ($produto['setor'] !== $setor && $setor_usuario !== 'Admin') {
            die("Este produto já existe no setor '{$produto['setor']}'. Você não tem permissão.");
        }

        // Atualiza a quantidade do produto existente
        $novo_total = $produto['quantidade'] + $quantidade;
        $stmt_update = $conn->prepare("UPDATE products SET quantidade = ? WHERE id = ?");
        $stmt_update->bind_param("ii", $novo_total, $produto['id']);
        $stmt_update->execute();

        $produto_id = $produto['id'];
    } else {
        // Insere um novo produto
        $stmt_insert = $conn->prepare("INSERT INTO products (nome, quantidade, setor) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("sis", $nome, $quantidade, $setor);
        $stmt_insert->execute();
        $produto_id = $stmt_insert->insert_id;
    }

    // Registra a movimentação
    $stmt_mov = $conn->prepare("INSERT INTO movimentos (produto_id, tipo, quantidade, usuario) VALUES (?, 'entrada', ?, ?)");
    $stmt_mov->bind_param("iis", $produto_id, $quantidade, $nome_usuario);
    $stmt_mov->execute();

    $mensagem = "Produto adicionado com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Produto</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            padding: 40px;
        }

        .container {
            background: white;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input, select, button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .msg {
            text-align: center;
            font-weight: bold;
            color: green;
        }

        .erro {
            text-align: center;
            font-weight: bold;
            color: red;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📦 Adicionar Produto ao Estoque</h2>

    <?php if ($mensagem): ?>
        <p class="msg"><?= $mensagem ?></p>
    <?php elseif ($erro): ?>
        <p class="erro"><?= $erro ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="nome" placeholder="Nome do Produto" required>
        <input type="number" name="quantidade" placeholder="Quantidade" required>

        <?php if ($setor_usuario === 'Admin'): ?>
<select name="setor">
    <option value="">Selecione o setor</option>
    <option value="Eventos">Eventos</option>
    <option value="Geral">Geral</option>
    <option value="certificados">Certificados</option>
</select>
        <?php else: ?>
            <input type="hidden" name="setor" value="<?= htmlspecialchars($setor_usuario) ?>">
            <p><strong>Setor:</strong> <?= htmlspecialchars($setor_usuario) ?></p>
        <?php endif; ?>

        <button type="submit">Cadastrar</button>
    </form>
    <a href="dashboard.php">← Voltar</a>
</div>
</body>
<script>
$(function() {
    $("input[name='nome']").autocomplete({
        source: 'busca_produtos.php',
        select: function(event, ui) {
            const nomeSelecionado = ui.item.value;
            $.get('busca_setor.php', { nome: nomeSelecionado }, function(setor) {
                if (setor) {
                    $("select[name='setor']").val(setor);
                }
            });
        }
    });
});
</script>
</html>
