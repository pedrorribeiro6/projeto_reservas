<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - Reservas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
    <link rel="icon" href="../imagens/sys_logo.png" type="image/png">
</head>
<body>
    <a href="index.php" class="btn-top-left" aria-label="Voltar para a página inicial">⬅ VOLTAR</a>
    <button id="theme-toggle" class="theme-btn" aria-label="Alternar Tema">🌞</button>
    <main class="form-wrapper">
        <header class="form-header">
            <h1 class="massive-title">CRIAÇÃO DE CONTA<span class="dot"></span></h1>
            <p class="subtitle">REGISTRO DE USUÁRIO</p>
        </header>

        <form action="processa_cadastro.php" method="POST" class="brutalist-form">
            <?php if(isset($_SESSION['erro_cadastro'])): ?>
                <div style="background: rgba(250, 30, 78, 0.1); border: 2px solid var(--accent-red); color: var(--accent-red); padding: 1rem; margin-bottom: 1.5rem; text-align: center; font-weight: bold; font-family: 'Rajdhani', sans-serif; font-size: 1.2rem;">
                    <?= $_SESSION['erro_cadastro']; unset($_SESSION['erro_cadastro']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['sucesso_cadastro'])): ?>
                <div style="background: rgba(0, 255, 102, 0.1); border: 2px solid #00FF66; color: #00FF66; padding: 1rem; margin-bottom: 1.5rem; text-align: center; font-weight: bold; font-family: 'Rajdhani', sans-serif; font-size: 1.2rem;">
                    <?= $_SESSION['sucesso_cadastro']; unset($_SESSION['sucesso_cadastro']); ?>
                </div>
            <?php endif; ?>

            <div class="input-group">
                <label for="nome">NOME COMPLETO</label>
                <input type="text" id="nome" name="nome" placeholder="Digite seu nome" required>
            </div>
            
            <div class="input-group">
                <label for="email">E-MAIL</label>
                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
            </div>

            <div class="input-group">
                <label for="senha">SENHA</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>

            <div class="input-group">
                <label for="tipo_conta">TIPO DE CONTA</label>
                <div class="select-wrapper">
                    <select id="tipo_conta" name="tipo_conta" required>
                        <option value="" disabled selected>Selecione uma opção</option>
                        <option value="professor">Professor</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">REGISTRAR</button>
                <a href="index.php" class="btn-back">VOLTAR</a>
            </div>
        </form>
    </main>
</body>
</html>
