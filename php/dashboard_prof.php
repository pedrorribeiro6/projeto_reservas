<?php
require 'auth.php';
proteger_pagina('professor');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Professor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style3.css">
    <script src="../js/script.js" defer></script>
    <link rel="icon" href="../imagens/sys_logo.png" type="image/png">
</head>
<body>
    <nav class="top-nav">
        <div class="nav-brand">PROF<span>.</span>RES</div>
        <ul class="nav-links">
            <li><a href="dashboard_prof.php" class="active">INÍCIO</a></li>
            <li><a href="agendamentos_prof.php">MEUS AGENDAMENTOS</a></li>
            <li><a href="agendar.php">AGENDAR</a></li>
            <li><a href="logout.php" class="btn-logout">SAIR</a></li>
            <li><button id="theme-toggle" class="nav-theme-btn" aria-label="Alternar Tema">🌞</button></li>
        </ul>
    </nav>

    <main class="dashboard-wrapper">
        <header class="dash-header">
            <h1 class="massive-title">BEM-VINDO, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'DOCENTE') ?></h1>
            <div class="glitch-line-prof"></div>
        </header>

        <section class="dash-stats">
            <div class="stat-card">
                <h2>AÇÕES RÁPIDAS</h2>
                <p>Navegue pelo menu superior para agendar novos equipamentos ou verificar suas reservas vigentes e antigas.</p>
                <a href="agendar.php" class="btn-action">NOVO AGENDAMENTO ↗</a>
            </div>
            
            <div class="stat-card">
                <h2>SUAS RESERVAS</h2>
                <p>Acompanhe o status e a quantidade de itens que você tem garantidos para as próximas aulas.</p>
                <a href="agendamentos_prof.php" class="btn-action" style="background: transparent; color: var(--text-color); border-color: var(--border-dark);">VER LISTA ↗</a>
            </div>
        </section>
    </main>
</body>
</html>
