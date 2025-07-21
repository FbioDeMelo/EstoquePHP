<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['setor'] !== 'Admin') {
    exit('Acesso negado');
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="movimentos.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Produto', 'Setor', 'Tipo', 'Quantidade', 'Data', 'Usuário']);

$sql = "
    SELECT p.nome, p.setor, m.tipo, m.quantidade, m.data_movimento, m.usuario
    FROM movimentos m
    JOIN products p ON m.produto_id = p.id
    ORDER BY m.data_movimento DESC
";
$res = $conn->query($sql);

while ($row = $res->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
