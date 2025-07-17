<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['user']['nome'];

// Atualizar estoque
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $produto_id = $_POST['produto_id'];
    $tipo = $_POST['tipo'];
    $quantidade = intval($_POST['quantidade']);

    // Buscar o produto atual
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $produto_id);
    $stmt->execute();
    $produto = $stmt->get_result()->fetch_assoc();

    if ($tipo === 'saida' && $produto['quantidade'] < $quantidade) {
        echo "<p style='color:red;'>Quantidade insuficiente no estoque!</p>";
    } else {
        // Atualizar quantidade
        $nova_quantidade = $tipo === 'entrada' 
            ? $produto['quantidade'] + $quantidade 
            : $produto['quantidade'] - $quantidade;

        $stmt = $conn->prepare("UPDATE products SET quantidade = ? WHERE id = ?");
        $stmt->bind_param("ii", $nova_quantidade, $produto_id);
        $stmt->execute();

        // Registrar no log de movimentos
        $stmt = $conn->prepare("INSERT INTO movimentos (produto_id, tipo, quantidade, usuario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $produto_id, $tipo, $quantidade, $usuario);
        $stmt->execute();

        echo "<p style='color:green;'>Movimentação registrada com sucesso.</p>";
    }
}

// Buscar produtos do setor atual
$setor = $_SESSION['user']['setor'];
if ($setor === 'Admin') {
    $sql = "SELECT * FROM products";
} else {
    $sql = "SELECT * FROM products WHERE setor = ?";
}
$stmt = $conn->prepare($sql);
if ($setor !== 'Admin') $stmt->bind_param("s", $setor);
$stmt->execute();
$produtos = $stmt->get_result();
?>

<h2>Movimentar Estoque - <?= htmlspecialchars($setor) ?></h2>

<form method="POST">
    <label>Produto:</label>
    <select name="produto_id" required>
        <?php while ($row = $produtos->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>">
                <?= $row['nome'] ?> (<?= $row['quantidade'] ?> unid.)
            </option>
        <?php endwhile; ?>
    </select><br><br>

<label>Tipo de Movimento:</label>
<input type="hidden" name="tipo" value="saida">
<p>Tipo: Saída</p>

    <label>Quantidade:</label>
    <input type="number" name="quantidade" min="1" required><br><br>

    <button type="submit">Registrar Movimento</button>
</form>

<p><a href="dashboard.php">← Voltar ao Dashboard</a></p>
