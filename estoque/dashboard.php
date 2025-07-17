<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$nomeUsuario = $_SESSION['user']['nome'];
$setor = $_SESSION['user']['setor'];

// Consulta produtos conforme setor
if ($setor === 'Admin') {
    $sql = "SELECT * FROM products";
} elseif ($setor === 'Eventos') {
    $sql = "SELECT * FROM products WHERE setor = 'Eventos'";
} else {
    $sql = "SELECT * FROM products WHERE setor = 'Geral'";
}
$limit = 20;
$offset = 0;

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


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Estoque</title>
  <style>
    :root {
      --primary-color: #007bff;
      --primary-hover: #0056b3;
      --bg-color: #f4f6f9;
      --white: #ffffff;
      --gray-light: #f8f9fa;
      --gray-medium: #dee2e6;
      --text-color: #333;
      --font-main: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: var(--font-main);
      background-color: var(--bg-color);
      color: var(--text-color);
      line-height: 1.6;
      padding: 1rem;
    }

    .container {
      max-width: 1100px;
      margin: 2rem auto;
      background: var(--white);
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      padding: 2rem;
    }

    header {
      margin-bottom: 2rem;
    }

    h2 {
      font-size: 1.75rem;
      margin-bottom: 0.5rem;
    }

    p {
      font-size: 1rem;
      color: #555;
    }

    nav {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-top: 1.5rem;
      background-color: var(--gray-light);
      padding: 0.75rem 1rem;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    nav a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    nav a:hover {
      color: var(--primary-hover);
      text-decoration: underline;
    }

    .search-section {
      margin-bottom: 1.5rem;
    }

    .search-section input[type="text"] {
      width: 100%;
      max-width: 400px;
      padding: 0.6rem 1rem;
      font-size: 1rem;
      border: 1px solid var(--gray-medium);
      border-radius: 6px;
    }

    ul#sugestoes {
      list-style: none;
      padding: 0;
      border: 1px solid var(--gray-medium);
      margin-top: 0.25rem;
      border-top: none;
      border-radius: 0 0 6px 6px;
      max-height: 200px;
      overflow-y: auto;
      background: var(--white);
      z-index: 10;
      display: none;
      position: absolute;
    }

    ul#sugestoes li {
      padding: 0.6rem 1rem;
      cursor: pointer;
    }

    ul#sugestoes li:hover {
      background-color: #f0f0f0;
    }

    h3 {
      margin-top: 1.5rem;
      font-size: 1.25rem;
      color: black;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    th,
    td {
      padding: 1rem;
      text-align: center;
    }

    th {
      background-color: var(--primary-color);
      color: var(--white);
    }

    tr:hover {
      background-color: #f9f9f9;
    }

    a.button {
      display: inline-block;
      margin-top: 1rem;
      padding: 0.5rem 1rem;
      background-color: #ff0000ff;
      color: var(--white);
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
    }

    a.button:hover {
      background-color: #c50404ff;
    }
  a.buttton {
      display: inline-block;
      margin-top: 1rem;
      padding: 0.5rem 1rem;
      background-color: #07f723ff;
      color: var(--white);
      border-radius: 6px;
      text-decoration: none;
      font-weight: 500;
    }

    a.buttton:hover {
      background-color: #01cc19ff;
    }
    
    @media (max-width: 600px) {
      nav {
        flex-direction: column;
      }

      .search-section input[type="text"] {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
          <nav>
      <a href="movimentar_estoque.php">Movimentar Estoque</a>
      <?php if ($setor === 'Admin'): ?>
        <a href="add_user.php">Cadastrar Novo Usuário</a>
        <a href="manage_users.php">Gerenciar Usuários</a>
      <?php endif; ?>
    </nav>
    </header>
      <h2>Olá, <strong><?= htmlspecialchars($nomeUsuario) ?></strong>! Bem-vindo ao sistema!</h2>
      <p>Seu setor: <strong><?= htmlspecialchars($setor) ?></strong></p>
<center>
    <section class="search-section">
    <h3>Pesquisar Produto</h3>  <input type="text" id="campoBusca" placeholder="🔍 Digite o nome do produto..." aria-label="Campo de busca de produto">
      <ul id="sugestoes" role="listbox"></ul>
            <a href="add_product.php" class="buttton">+ Adicionar Produto</a>
      </center>
    </section>
    <section>
      <table aria-label="Tabela de estoque">
        <thead>
  <tr>
    <th>Nome</th>
    <th>Quantidade</th>
    <th>Setor</th>
    <th>Ações</th>
  </tr>
</thead>
        <tbody>
<?php foreach ($produtos as $item): ?>
  <tr data-produto="<?= htmlspecialchars($item['nome']) ?>">
    <td><?= htmlspecialchars($item['nome']) ?></td>
    <td><?= htmlspecialchars($item['quantidade']) ?></td>
    <td><?= htmlspecialchars($item['setor']) ?></td>
    <td>
        <center>
      <a href="movimentar_estoque.php?id=<?= $item['id'] ?>" class="button" style="padding: 0.3rem 0.6rem; font-size: 0.9rem;">Remover</a>
</center>
    </td>
  </tr>
<?php endforeach; ?>

        </tbody>
      </table>
    </section>
    <button id="carregarMais" style="margin-top: 1rem; padding: 0.6rem 1.2rem; font-size: 1rem; cursor: pointer;">Carregar Mais</button>

  </div>
   <script>
  let offset = <?= count($produtos) ?>; // começa no total carregado (normalmente 20)
  const limit = 20;
  const setor = <?= json_encode($setor) ?>;
  const carregarMaisBtn = document.getElementById('carregarMais');
  const tbody = document.querySelector('table tbody');

  carregarMaisBtn.addEventListener('click', () => {
    carregarMaisBtn.disabled = true;
    carregarMaisBtn.textContent = 'Carregando...';

    fetch(`load_products.php?offset=${offset}`)
      .then(response => response.json())
      .then(data => {
        if (data.length === 0) {
          // Não tem mais produtos
          carregarMaisBtn.style.display = 'none';
          return;
        }

        data.forEach(item => {
          const tr = document.createElement('tr');
          tr.setAttribute('data-produto', item.nome);

          tr.innerHTML = `
            <td>${item.nome}</td>
            <td>${item.quantidade}</td>
            <td>${item.setor}</td>
            <td>
              <center>
                <a href="movimentar_estoque.php?id=${item.id}" class="button" style="padding: 0.3rem 0.6rem; font-size: 0.9rem;">Remover</a>
              </center>
            </td>
          `;

          tbody.appendChild(tr);
        });

        offset += data.length;
        carregarMaisBtn.disabled = false;
        carregarMaisBtn.textContent = 'Carregar Mais';
      })
      .catch(err => {
        console.error('Erro ao carregar produtos:', err);
        carregarMaisBtn.disabled = false;
        carregarMaisBtn.textContent = 'Carregar Mais';
      });
  });
</script>
</body>
</html>