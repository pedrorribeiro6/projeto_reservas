<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Professor - Reservas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style2.css">
    <script src="../js/script.js" defer></script>
    <script src="../js/login.js" defer></script>
    <link rel="icon" href="../imagens/sys_logo.png" type="image/png">
</head>
<body>
    <a href="index.php" class="btn-top-left" aria-label="Voltar para a página inicial">⬅ VOLTAR</a>
    <button id="theme-toggle" class="theme-btn" aria-label="Alternar Tema">🌞</button>
    
    <main class="login-wrapper">
        <header class="login-header">
            <h1 class="massive-title">PROF<span class="dot-prof">.</span></h1>
            <p class="subtitle">AUTENTICAÇÃO DE DOCENTE</p>
            <div class="glitch-line-prof"></div>
        </header>

        <form action="processa_login.php" method="POST" class="brutalist-login-form">
            <div class="input-group-prof">
                <label for="credencial">E-MAIL</label>
                <input type="text" id="credencial" name="credencial" placeholder="Digite seu acesso" required>
            </div>

            <div class="input-group-prof">
                <label for="senha">SENHA</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>

            <div class="form-actions-prof">
                <button type="submit" class="btn-submit-prof">ENTRAR NO SISTEMA</button>
            </div>
            
            <div class="form-footer-prof">
                <a href="criacao_conta.php">Não tem uma conta? Crie uma!</a>
            </div>
        </form>
    </main>
</body>
</html>
