<?php
require_once 'config/db.php';

$termo = $_GET['termo'] ?? '';
$setor = $_GET['setor'] ?? '';

$termo = '%' . $termo . '%'; // LIKE no SQL

if ($setor === 'Admin') {
    $stmt = $conn->prepare("SELECT * FROM products WHERE nome LIKE ?");
    $stmt->bind_param("s", $termo);
} else {
    $stmt = $conn->prepare("SELECT * FROM products WHERE nome LIKE ? AND setor = ?");
    $stmt->bind_param("ss", $termo, $setor);
}

$stmt->execute();
$result = $stmt->get_result();
$produtos = [];

while ($row = $result->fetch_assoc()) {
    $produtos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($produtos);
