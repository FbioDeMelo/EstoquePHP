<?php
require_once 'config/db.php';

$termo = $_GET['term'] ?? '';

$sugestoes = [];

if ($termo !== '') {
    $stmt = $conn->prepare("SELECT nome FROM products WHERE nome LIKE ? LIMIT 10");
    $like = '%' . $termo . '%';
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $sugestoes[] = $row['nome'];
    }
}

echo json_encode($sugestoes);
