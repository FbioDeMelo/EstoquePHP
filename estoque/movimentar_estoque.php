<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION['user']['nome'];

// Atualizar estoque
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $produto_id = intval($_POST['produto_id'] ?? 0);
    $tipo = $_POST['tipo'];
    $quantidade = intval($_POST['quantidade']);

    if ($produto_id <= 0) {
        $_SESSION['popup_message'] = "Produto inválido. Por favor, selecione um produto da lista.";
        $_SESSION['popup_type'] = "error";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Buscar o produto atual
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $produto_id);
    $stmt->execute();
    $produto = $stmt->get_result()->fetch_assoc();

    if ($tipo === 'saida' && $produto['quantidade'] < $quantidade) {
        $_SESSION['popup_message'] = "Quantidade insuficiente no estoque!";
        $_SESSION['popup_type'] = "error";
    } else {
        $nova_quantidade = $tipo === 'entrada'
            ? $produto['quantidade'] + $quantidade
            : $produto['quantidade'] - $quantidade;

        $stmt = $conn->prepare("UPDATE products SET quantidade = ? WHERE id = ?");
        $stmt->bind_param("ii", $nova_quantidade, $produto_id);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO movimentos (produto_id, tipo, quantidade, usuario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $produto_id, $tipo, $quantidade, $usuario);
        $stmt->execute();

        $_SESSION['popup_message'] = "Movimentação registrada com sucesso.";
        $_SESSION['popup_type'] = "success";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Buscar produtos do setor atual
$setor = $_SESSION['user']['setor'];
if ($setor === 'Admin') {
    $sql = "SELECT * FROM products";
} else {
    $sql = "SELECT * FROM products WHERE setor = ?";
}
$stmt = $conn->prepare($sql);
if ($setor !== 'Admin') $stmt->bind_param("s", $setor);
$stmt->execute();
$produtos = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Movimentar Estoque</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
              * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f4f6f8;
            color: #333;
            padding: 30px;
        }
form input[type="text"], form input[type="number"], form input[list] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline: none;
    font-size: 14px;
    transition: border-color 0.3s;
}
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #222;
        }

        form {
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }

        select,
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            width: 100%;
        }

        button:hover {
            background-color: #0056b3;
        }

        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .popup {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup.hidden {
            display: none;
        }

        .popup-content {
            background: white;
            padding: 100px;
            border-radius: 10px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }

        .popup-content p {
            font-size: 18px;
            color: #333;
        }

        .popup-content.success {
            border-left: 6px solid green;
        }

        .popup-content.error {
            border-left: 6px solid red;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }
.botao-movimento {
  background-color: #4CAF50;
  color: white;
  padding: 12px 24px;
  font-size: 16px;
  font-weight: bold;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: background-color 0.3s ease, transform 0.2s ease;
}

.botao-movimento:hover {
  background-color: #45a049;
  transform: translateY(-2px);
}

.botao-movimento:active {
  background-color: #3e8e41;
  transform: translateY(0);
}
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: #ffffff;
  color: #000000;   
  display: flex;
  align-items: center;
  padding: 0 1.5rem;
  z-index: 1000;
  justify-content: space-between;
  box-shadow: 0 2px 8px rgb(0 0 0 / 0.15);
}

.navbar-logo {
  height: 40px;
  cursor: pointer;
  transition: filter 0.3s ease;
}

.navbar-logo:hover {
  filter: brightness(1.2);
}

.hamburger {
  display: none;
  cursor: pointer;
  font-size: 1.4rem;
  color: #fff;
}

/* User menu */
.user-menu {
  position: relative;
}

.avatar-btn {
  border: none;
  background: transparent;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 2px solid #fff;
  transition: box-shadow 0.3s ease;
}

.avatar-btn:hover .avatar {
  box-shadow: 0 0 8px #fff;
}

.dropdown-menu {
  position: absolute;
  right: 0;
  top: 50px;
  background: #fff;
  color: #333;
  list-style: none;
  padding: 0.5rem 0;
  border-radius: 6px;
  box-shadow: 0 4px 10px rgb(0 0 0 / 0.2);
  display: none;
  min-width: 120px;
  z-index: 1100;
}

.user-menu:hover .dropdown-menu {
  display: block;
}

.dropdown-menu li a {
  display: block;
  padding: 0.6rem 1rem;
  color: #333;
  text-decoration: none;
  font-size: 0.95rem;
  transition: background 0.2s ease;
}

.dropdown-menu li a:hover {
  background: #0d47a1;
  color: #fff;
}

/* Sidebar menu */
.menu {
  position: fixed;
  top: 60px;
  left: 0;
  width: 220px;
  height: calc(100vh - 60px);
  background: #ffffff;
  color: #000000;
  padding-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  box-shadow: 2px 0 6px rgb(0 0 0 / 0.15);
  z-index: 900;
  transition: width 0.3s ease;
  overflow-y: auto;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  padding: 0.8rem 1rem;
  text-decoration: none;
  color: inherit;
  font-weight: 600;
  font-size: 1rem;
  border-left: 4px solid transparent;
  transition: background 0.2s ease, border-color 0.2s ease;
}

.menu-item i {
  font-size: 1.3rem;
}

.menu-item:hover,
.menu-item.active {
  background: #ddd7d7;
  border-left-color: #EA005F;
  color: #000000;
}
section{
margin-left: 240px;
    padding: 1.5rem 2rem 2rem;
    margin-top: 70px;
    flex-grow: 1;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 6px 12px rgb(0 0 0 / 0.1);
    max-width: calc(100% - 280px);
    }
    </style>
</head>
<body>
<nav class="navbar">
    <a href="https://faculdadelibano.edu.br/" class="navbar-brand">
        <img src="https://faculdadelibano.edu.br/_next/image?url=%2Froot%2Flogo-website-colorida.webp&w=384&q=75" alt="Faculdade Libano" class="navbar-logo">
    </a>
    <div class="hamburger" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </div>
    <ul class="navbar-links"></ul>
    <div class="user-menu">
        <button class="avatar-btn" aria-haspopup="true" aria-expanded="false" aria-controls="userDropdown">
            <img src="img/user.png" alt="Avatar do usuário" class="avatar" />
        </button>
        <ul id="userDropdown" class="dropdown-menu">
            <li><a href="#"><?= htmlspecialchars($usuario) ?></a></li>
        </ul>
    </div>
</nav>

<nav class="menu">
    <a href="dashboard.php" class="menu-item"><i class="fas fa-home"></i><span class="text">Início</span></a>
    <a href="movimentar_estoque.php" class="menu-item"><i class="fas fa-boxes"></i><span class="text">Retirada de Item</span></a>
    <a href="graficos.php" class="menu-item"><i class="fas fa-boxes"></i><span class="text">Gráficos</span></a>
    <?php if ($setor === 'Admin'): ?>
        <a href="add_user.php" class="menu-item"><i class="fas fa-user-plus"></i><span class="text">Cadastrar Usuário</span></a>
        <a href="manage_users.php" class="menu-item"><i class="fas fa-users-cog"></i><span class="text">Gerenciar Usuários</span></a>
        <a href="movimentacoes.php" class="menu-item"><i class="fas fa-clipboard-list"></i><span class="text">Movimentações</span></a>
    <?php endif; ?>
    <a href="logout.php" class="menu-item"><i class="fas fa-sign-out-alt"></i><span class="text">Sair</span></a>
</nav>
<section>
<form method="POST">
  <h2>Movimentar Estoque - <?= htmlspecialchars($setor) ?></h2>
    <label>Produto:</label>
    <input type="text" id="produto_nome" list="produtos-list" placeholder="Digite o nome do produto..." required>
    <input type="hidden" name="produto_id" id="produto_id">
    <datalist id="produtos-list">
        <?php
        $stmt->execute();
        $produtos = $stmt->get_result();
        while ($row = $produtos->fetch_assoc()):
        ?>
            <option data-id="<?= $row['id'] ?>" value="<?= htmlspecialchars($row['nome']) ?> (<?= $row['quantidade'] ?> unid.)"></option>
        <?php endwhile; ?>
    </datalist>
    <input type="hidden" name="tipo" value="saida">
    <label>Quantidade:</label>
    <input type="number" name="quantidade" min="1" required placeholder="00..."><br><br>

    <button type="submit">Registrar Saída</button>
</form>

<div id="popup" class="popup hidden">
    <div class="popup-content">
        <span class="close-btn" onclick="fecharPopup()">&times;</span>
        <p id="popup-message"></p>
        <button type="button" onclick="fecharPopup()" class="botao-movimento">Ok</button>
    </div>
</div>
        </section>
<script>
    const datalist = document.getElementById('produtos-list');
    const inputProduto = document.getElementById('produto_nome');
    const inputProdutoId = document.getElementById('produto_id');

    inputProduto.addEventListener('input', function () {
        const options = datalist.options;
        const inputValue = this.value;

        for (let i = 0; i < options.length; i++) {
            if (options[i].value === inputValue) {
                const id = options[i].getAttribute('data-id');
                inputProdutoId.value = id;
                return;
            }
        }

        // Caso não encontre correspondência
        inputProdutoId.value = '';
    });

    function mostrarPopup(mensagem, tipo) {
        const popup = document.getElementById("popup");
        const msg = document.getElementById("popup-message");
        const content = popup.querySelector('.popup-content');

        msg.textContent = mensagem;
        content.classList.remove("success", "error");
        content.classList.add(tipo);
        popup.classList.remove("hidden");

        setTimeout(() => {
            popup.classList.add("hidden");
        }, 3000);
    }

    function fecharPopup() {
        document.getElementById("popup").classList.add("hidden");
    }

    <?php
    if (isset($_SESSION['popup_message'])) {
        $msg = addslashes($_SESSION['popup_message']);
        $tipo = $_SESSION['popup_type'] ?? 'success';
        echo "window.onload = function() {
            mostrarPopup('$msg', '$tipo');
        };";
        unset($_SESSION['popup_message'], $_SESSION['popup_type']);
    }
    ?>
</script>
</body>
</html>
