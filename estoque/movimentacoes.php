<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['setor'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Filtros
$produtoFiltro = $_GET['produto'] ?? '';
$tipoFiltro = $_GET['tipo'] ?? '';
$usuarioFiltro = $_GET['usuario'] ?? '';

// Montar consulta com filtros
$where = [];
$params = [];
$types = '';

if ($produtoFiltro !== '') {
    $where[] = "p.nome LIKE ?";
    $params[] = "%$produtoFiltro%";
    $types .= 's';
}
if ($tipoFiltro !== '') {
    $where[] = "m.tipo = ?";
    $params[] = $tipoFiltro;
    $types .= 's';
}
if ($usuarioFiltro !== '') {
    $where[] = "m.usuario LIKE ?";
    $params[] = "%$usuarioFiltro%";
    $types .= 's';
}

$whereSQL = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT m.id, p.nome AS produto, m.tipo, m.quantidade, m.data_movimento, m.usuario 
        FROM movimentos m
        JOIN products p ON m.produto_id = p.id
        $whereSQL
        ORDER BY m.data_movimento DESC";

$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>📦 Movimentações de Estoque</h2>

<p><a href="dashboard.php">← Voltar ao Dashboard</a></p>
<p><a href="?export=csv">⬇️ Exportar CSV</a></p>

<!-- Filtros -->
<form method="GET" style="margin: 20px 0;">
    <label>Produto:</label>
    <input type="text" name="produto" value="<?= htmlspecialchars($produtoFiltro) ?>">

    <label>Tipo:</label>
    <select name="tipo">
        <option value="">Todos</option>
        <option value="entrada" <?= $tipoFiltro == 'entrada' ? 'selected' : '' ?>>Entrada</option>
        <option value="saida" <?= $tipoFiltro == 'saida' ? 'selected' : '' ?>>Saída</option>
    </select>

    <label>Usuário:</label>
    <input type="text" name="usuario" value="<?= htmlspecialchars($usuarioFiltro) ?>">

    <button type="submit">🔍 Filtrar</button>
</form>

<!-- Tabela -->
<table border="1" cellpadding="6">
    <thead>
        <tr>
            <th>ID</th>
            <th>Produto</th>
            <th>Tipo</th>
            <th>Quantidade</th>
            <th>Data</th>
            <th>Usuário</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="6">Nenhuma movimentação encontrada com os filtros selecionados.</td></tr>
        <?php else: ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['produto']) ?></td>
                <td><?= $row['tipo'] === 'entrada' ? '📥 Entrada' : '📤 Saída' ?></td>
                <td><?= $row['quantidade'] ?></td>
                <td><?= date("d/m/Y H:i", strtotime($row['data_movimento'])) ?></td>
                <td><?= htmlspecialchars($row['usuario']) ?></td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </tbody>
</table>
