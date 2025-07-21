<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$setor = $_SESSION['user']['setor'];
$limite = isset($_GET['limite']) ? intval($_GET['limite']) : 10;

$params = [];
$innerSql = "
    SELECT p.nome,
           SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE 0 END) AS entrada,
           SUM(CASE WHEN m.tipo = 'saida' THEN m.quantidade ELSE 0 END) AS saida
    FROM movimentos m
    JOIN products p ON m.produto_id = p.id
";

if ($setor !== 'Admin') {
    $innerSql .= " WHERE p.setor = ?";
    $params[] = $setor;
}

$innerSql .= " GROUP BY p.nome";

$sql = "
    SELECT nome, entrada, saida
    FROM ( $innerSql ) AS t
    ORDER BY (entrada + saida) DESC
    LIMIT ?
";
$params[] = $limite;

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro ao preparar a consulta: " . $conn->error);
}

$types = '';
foreach ($params as $p) {
    $types .= is_int($p) ? 'i' : 's';
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$nomes = $entradas = $saidas = [];
while ($row = $result->fetch_assoc()) {
    $nomes[] = $row['nome'];
    $entradas[] = $row['entrada'];
    $saidas[] = $row['saida'];
}

// Gráfico por setor (apenas para Admin)
$setores = [];
if ($setor === 'Admin') {
    $sql2 = "
        SELECT p.setor,
               SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE 0 END) AS entrada
        FROM movimentos m
        JOIN products p ON m.produto_id = p.id
        GROUP BY p.setor
    ";
    $res2 = $conn->query($sql2);
    while ($row = $res2->fetch_assoc()) {
        $setores[$row['setor']] = $row['entrada'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Gráficos de Estoque</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="css/estilo.css" />
</head>
<body>
<nav class="navbar">
    <a href="https://faculdadelibano.edu.br/" class="navbar-brand">
        <img src="https://faculdadelibano.edu.br/_next/image?url=%2Froot%2Flogo-website-colorida.webp&w=384&q=75" alt="Faculdade Libano" class="navbar-logo">
    </a>
</nav>

<nav class="menu">
    <a href="dashboard.php" class="menu-item"><i class="fas fa-home"></i><span class="text">Início</span></a>
    <a href="movimentar_estoque.php" class="menu-item"><i class="fas fa-boxes"></i><span class="text">Retirada de Item</span></a>
    <a href="graficos.php" class="menu-item"><i class="fas fa-chart-bar"></i><span class="text">Gráficos</span></a>
    <?php if ($setor === 'Admin'): ?>
        <a href="add_user.php" class="menu-item"><i class="fas fa-user-plus"></i><span class="text">Cadastrar Usuário</span></a>
        <a href="manage_users.php" class="menu-item"><i class="fas fa-users-cog"></i><span class="text">Gerenciar Usuários</span></a>
        <a href="movimentacoes.php" class="menu-item"><i class="fas fa-clipboard-list"></i><span class="text">Movimentações</span></a>
    <?php endif; ?>
    <a href="logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i><span class="text">Sair</span></a>
</nav>

<div class="container">
    <h2>📊 Gráficos de Movimentação</h2>
    <form method="GET" class="filtro">
        <label for="limite">Mostrar:</label>
        <select name="limite" id="limite" onchange="this.form.submit()">
            <option value="10" <?= $limite == 10 ? 'selected' : '' ?>>Top 10</option>
            <option value="20" <?= $limite == 20 ? 'selected' : '' ?>>Top 20</option>
            <option value="50" <?= $limite == 50 ? 'selected' : '' ?>>Top 50</option>
        </select>
    </form>

    <canvas id="grafico"></canvas>

    <?php if ($setor === 'Admin'): ?>
        <h3>Por Setor (Entradas)</h3>
        <canvas id="setorGrafico"></canvas>
        <div class="btn-group">
            <a href="exportar_csv.php" class="btn btn-exportar">📥 Exportar todos os dados (CSV)</a>
            <button class="btn btn-download" onclick="baixarGraficoPDF()">📥 Baixar Ambos os Gráficos (PDF)</button>
        </div>
    <?php endif; ?>

    <a href="dashboard.php" class="link-voltar">← Voltar ao painel</a>
</div>

<script>
const ctx = document.getElementById('grafico').getContext('2d');
const grafico = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($nomes) ?>,
        datasets: [
            {
                label: 'Entradas',
                data: <?= json_encode($entradas) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)'
            },
            {
                label: 'Saídas',
                data: <?= json_encode($saidas) ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.6)'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

async function baixarGraficoPDF() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF();

    const canvasBarras = document.getElementById('grafico');
    const canvasPizza = document.getElementById('setorGrafico');

    const imgDataBarras = canvasBarras.toDataURL('image/png');
    const imgDataPizza = canvasPizza?.toDataURL('image/png');

    const pdfWidth = pdf.internal.pageSize.getWidth();
    const margin = 10;
    const imgWidth = pdfWidth - margin * 2;
    const imgHeightBarras = (canvasBarras.height / canvasBarras.width) * imgWidth;

    pdf.setFontSize(14);
    pdf.text("Gráfico de Barras", margin, 15);
    pdf.addImage(imgDataBarras, 'PNG', margin, 20, imgWidth, imgHeightBarras);

    if (imgDataPizza) {
        const imgHeightPizza = (canvasPizza.height / canvasPizza.width) * imgWidth;
        pdf.addPage();
        pdf.text("Gráfico de Pizza", margin, 15);
        pdf.addImage(imgDataPizza, 'PNG', margin, 20, imgWidth, imgHeightPizza);
    }

    pdf.save('graficos_estoque.pdf');
}

<?php if ($setor === 'Admin'): ?>
const setorCtx = document.getElementById('setorGrafico').getContext('2d');
new Chart(setorCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_keys($setores)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($setores)) ?>,
            backgroundColor: ['#4caf50', '#ff9800', '#2196f3', '#9c27b0', '#ff5722', '#607d8b', '#795548']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { padding: 15 }
            }
        }
    }
});
<?php endif; ?>
</script>
</body>
</html>
