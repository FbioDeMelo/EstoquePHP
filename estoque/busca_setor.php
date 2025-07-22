<?php
require_once 'config/db.php';

$nome = $_GET['nome'] ?? '';

if ($nome !== '') {
    $stmt = $conn->prepare("SELECT setor FROM products WHERE nome = ? LIMIT 1");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo $row['setor'];
    } else {
        echo '';
    }
}
