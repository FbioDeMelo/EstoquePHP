<?php
session_start();
require_once 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha']; 
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND senha = ?");
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['ativo'] == 0) {
            $erro = "Usuário desativado. Contate o administrador.";
        } else {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit;
        }
    } else {
        $erro = "Credenciais inválidas!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Estoque</title>
  <link rel="stylesheet" href="css/login.css" />
</head>
<body>
  <div class="container">
    <div class="login-box">
      <div class="form-section">
        <img src="https://institutolibano.com.br/_next/image?url=%2Froot%2Flogo-website-colorida.webp&w=384&q=75" alt="Logo" class="logo" />
        <h2>Acesse o sistema de estoque</h2>
        <p>Digite suas credenciais para continuar.</p>

        <?php if (isset($erro)) echo "<p class='error'>$erro</p>"; ?>

        <form method="POST">
          <label for="email">E-mail</label>
          <input type="email" name="email" id="email" placeholder="seuemail@exemplo.com" required />

          <label for="senha">Senha</label>
          <input type="password" name="senha" id="senha"  required />

          <button type="submit" class="btn submit">Entrar</button>
        </form>

        <div class="register-text">
          <a href="recuperar.php" class="forgot-password">Esqueceu a senha?</a><br />
        </div>
      </div>

      <div class="info-section">
        <div class="carousel">
          <img src="img/teste02.png" />
          <div class="dots">
            <span class="dot active"></span>
            <span class="dot"></span>
            <span class="dot"></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
<script>
  const images = [
    'img/Gemini_Generated_Image_fiicjmfiicjmfiic.png',
    'img/Teste02.png',
    'img/teste03.png'
  ];

  let currentIndex = 0;
  const carouselImage = document.querySelector('.carousel img');
  const dots = document.querySelectorAll('.dot');

  function updateCarousel() {
    carouselImage.src = images[currentIndex];

    dots.forEach((dot, index) => {
      dot.classList.toggle('active', index === currentIndex);
    });

    currentIndex = (currentIndex + 1) % images.length;
  }

  setInterval(updateCarousel, 3000);
</script>
</html>
