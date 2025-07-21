<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$nomeUsuario = $_SESSION['user']['nome'];
$setor = $_SESSION['user']['setor'];

$limit = 20;
$offset = 0;

if ($setor === 'Admin') {
    $sql = "SELECT * FROM products LIMIT $limit OFFSET $offset";
} elseif ($setor === 'Eventos') {
    $sql = "SELECT * FROM products WHERE setor = 'Eventos' LIMIT $limit OFFSET $offset";
} elseif ($setor === 'Certificados') {  // aqui deve bater com o banco
    $sql = "SELECT * FROM products WHERE setor = 'Certificados' LIMIT $limit OFFSET $offset";
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
   <link rel="stylesheet" href="css/style.css" />
  <title>Dashboard - Estoque</title>
<head>
<body>
<nav class="navbar">
        <a href="https://faculdadelibano.edu.br/" class="navbar-brand">
            <img src="https://faculdadelibano.edu.br/_next/image?url=%2Froot%2Flogo-website-colorida.webp&w=384&q=75" alt="Faculdade Libano" class="navbar-logo">
        </a>
        <div class="hamburger" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>
        <ul class="navbar-links">
        </ul>
        
<div class="user-menu">
  <button class="avatar-btn" aria-haspopup="true" aria-expanded="false" aria-controls="userDropdown">
    <img src="img/user.png" alt="Avatar do usuário" class="avatar" />
  </button>
  <ul id="userDropdown" class="dropdown-menu">
    <li><a href="#"><?= htmlspecialchars($nomeUsuario) ?></a></li>
  </ul>
</div>
    </nav>

<!-- Sidebar -->
<nav class="menu">
  <a href="dashboard.php" class="menu-item">
    <i class="fas fa-home"></i>
    <span class="text">Início</span>
  </a>
  <a href="movimentar_estoque.php" class="menu-item">
    <i class="fas fa-boxes"></i>
    <span class="text">Retirada de Item</span>
  </a>
    <a href="graficos.php" class="menu-item">
    <i class="fas fa-boxes"></i>
    <span class="text">Graficos</span>
  </a>
  <?php if ($setor === 'Admin'): ?>
    <a href="add_user.php" class="menu-item">
      <i class="fas fa-user-plus"></i>
      <span class="text">Cadastrar Usuário</span>
    </a>
    <a href="manage_users.php" class="menu-item">
      <i class="fas fa-users-cog"></i>
      <span class="text">Gerenciar Usuários</span>
    </a>
    <a href="movimentacoes.php" class="menu-item">
      <i class="fas fa-clipboard-list"></i>
      <span class="text">Movimentações</span>
    </a>
  <?php endif; ?>
  <a href="logout.php" class="menu-item">
    <i class="fas fa-sign-out-alt"></i>
    <span class="text">Sair</span>
  </a>
</nav>
      <h2>Olá, <strong><?= htmlspecialchars($nomeUsuario) ?></strong>! Bem-vindo ao sistema! Seu setor: <strong><?= htmlspecialchars($setor) ?></strong></h2>
      <center>
    <section class="search-section">
    <h3>Pesquisar Produto</h3>  <input type="text" id="campoBusca" placeholder="🔍 Digite o nome do produto..." aria-label="Campo de busca de produto">
            <a href="add_product.php" class="buttton">+ Adicionar Produto</a>
      </center>
    </section>
    <center>
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
      </center>
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

<script>
    function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
  }
  const campoBusca = document.getElementById('campoBusca');
  const carregarMaisBtn = document.getElementById('carregarMais');
  const tbody = document.querySelector('table tbody');
  let offset = <?= count($produtos) ?>;
  const limit = 20;
  const setor = <?= json_encode($setor) ?>;

  // Busca dinâmica no banco de dados conforme digita
  campoBusca.addEventListener('input', () => {
    const termo = campoBusca.value.trim();

   if (termo.length < 2) {
  return; 
}

    fetch(`search_product.php?termo=${encodeURIComponent(termo)}&setor=${encodeURIComponent(setor)}`)
      .then(res => res.json())
      .then(data => {
        tbody.innerHTML = ''; // Limpa a tabela

        if (data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="4"> ☹️ Nenhum produto encontrado.</td></tr>';
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

        carregarMaisBtn.style.display = 'none'; // Oculta botão ao buscar
      });
  });

  // Botão "Carregar Mais"
  carregarMaisBtn.addEventListener('click', () => {
    carregarMaisBtn.disabled = true;
    carregarMaisBtn.textContent = 'Carregando...';

    fetch(`load_products.php?offset=${offset}&setor=${encodeURIComponent(setor)}`)
      .then(response => response.json())
      .then(data => {
        if (data.length === 0) {
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