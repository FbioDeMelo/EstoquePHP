<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['setor'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

// Atualizar status se houver ação
if (isset($_GET['toggle_id'])) {
    $id = intval($_GET['toggle_id']);

    // Busca status atual
    $stmt = $conn->prepare("SELECT ativo FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $novoStatus = $res['ativo'] ? 0 : 1;

        $stmt = $conn->prepare("UPDATE users SET ativo = ? WHERE id = ?");
        $stmt->bind_param("ii", $novoStatus, $id);
        $stmt->execute();
    }
    header("Location: manage_users.php");
    exit;
}

// Busca todos usuários
$result = $conn->query("SELECT id, nome, email, setor, ativo FROM users");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerenciar Usuários</title>
</head>
<body>
    <h2>Usuários</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Nome</th><th>Email</th><th>Setor</th><th>Status</th><th>Ação</th>
        </tr>
        <?php while($user = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($user['nome']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['setor']) ?></td>
                <td><?= $user['ativo'] ? 'Ativo' : 'Desativado' ?></td>
                <td>
                    <a href="manage_users.php?toggle_id=<?= $user['id'] ?>">
                        <?= $user['ativo'] ? 'Desativar' : 'Ativar' ?>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>
    <p><a href="dashboard.php">Voltar ao Dashboard</a></p>
</body>
</html>
