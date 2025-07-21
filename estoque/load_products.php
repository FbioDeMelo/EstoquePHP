<?php
require_once 'config/db.php';

$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = 20;
$setor = $_GET['setor'] ?? '';

// Consulta conforme setor
if ($setor === 'Admin') {
    $sql = "SELECT * FROM products LIMIT $limit OFFSET $offset";
} else {
    $sql = "SELECT * FROM products WHERE setor = ? LIMIT $limit OFFSET $offset";
}

if ($setor === 'Admin') {
    $stmt = $conn->prepare($sql);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $setor);
}

$stmt->execute();
$result = $stmt->get_result();

$produtos = [];
while ($row = $result->fetch_assoc()) {
    $produtos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($produtos);
