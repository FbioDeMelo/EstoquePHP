<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$setor = $_SESSION['user']['setor'];
$limit = 20;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

if ($setor === 'Admin') {
    $sql = "SELECT * FROM products LIMIT $limit OFFSET $offset";
} elseif ($setor === 'Eventos') {
    $sql = "SELECT * FROM products WHERE setor = 'Eventos' LIMIT $limit OFFSET $offset";
} else {
    $sql = "SELECT * FROM products WHERE setor = 'Geral' LIMIT $limit OFFSET $offset";
}

$result = $conn->query($sql);

$produtos = [];
while ($row = $result->fetch_assoc()) {
    $produtos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($produtos);
