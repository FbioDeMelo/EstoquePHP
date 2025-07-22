<?php
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Remove o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroi a sessão
session_destroy();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Logout</title>
    <meta http-equiv="refresh" content="3;url=index.php">
    <style>
        body {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            text-align: center;
            padding-top: 15%;
        }
        .message {
            font-size: 1.5rem;
            animation: fadeIn 2s ease-in-out;
        }
        .loader {
            margin: 20px auto;
            border: 6px solid #fff;
            border-top: 6px solid #764ba2;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes spin {
            0% { transform: rotate(0deg);}
            100% { transform: rotate(360deg);}
        }
    </style>
</head>
<body>
    <div class="message">Você saiu com sucesso. Redirecionando para a página de login...</div>
    <div class="loader"></div>
</body>
</html>
