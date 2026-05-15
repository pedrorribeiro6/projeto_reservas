<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Reservas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="../imagens/sys_logo.png" type="image/png">"
    <script src="../js/script.js" defer></script>
</head>
<body>
    <button id="theme-toggle" class="theme-btn" aria-label="Alternar Tema">🌞</button>
    <main class="selection-wrapper">
        <header class="hero-header">
            <h1 class="massive-title">SYS<span class="dot">.</span>RES</h1>
            <div class="glitch-line"></div>
            <p class="subtitle">SELECIONE O PROTOCOLO DE ACESSO</p>
        </header>

        <section class="role-grid">
            <a href="login_prof.php" class="role-card card-professor">
                <div class="card-bg"></div>
                <div class="card-content">
                    <span class="role-id">PROF</span>
                    <h2>Professor</h2>
                    <p>Acesso ao painel de reservas e equipamentos.</p>
                </div>
                <div class="icon-arrow">»</div>
            </a>

            <a href="login_adm.php" class="role-card card-admin">
                <div class="card-bg"></div>
                <div class="card-content">
                    <span class="role-id">ADM</span>
                    <h2>Administrador</h2>
                    <p>Controle total, relatórios e gestão do sistema.</p>
                </div>
                <div class="icon-arrow">»</div>
            </a>
        </section>

        <div class="action-bar">
            <a href="criacao_conta.php" class="btn-create-account">Criar conta</a>
        </div>
    </main>
</body>
</html>