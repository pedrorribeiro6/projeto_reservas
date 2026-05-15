<?php
require 'auth.php';
require 'conexao.php';
proteger_pagina('admin');

$hoje = date('Y-m-d');

// Estatística: Reservas de hoje
$stmt1 = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE data_reserva = ?");
$stmt1->execute([$hoje]);
$total_hoje = $stmt1->fetchColumn();

// Estatística: Total de Professores Ativos
$stmt2 = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE tipo_conta = 'professor'");
$stmt2->execute();
$total_prof = $stmt2->fetchColumn();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrador - Reservas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style3.css">
    <link rel="stylesheet" href="../css/style_adm.css">
    <script src="../js/script.js" defer></script>
    <link rel="icon" href="../imagens/sys_logo.png" type="image/png">
</head>
<body>
        <nav class="top-nav">
        <div class="nav-brand">ADM<span>.</span>RES</div>
        <ul class="nav-links">
            <li><a href="dashboard_adm.php" class="active">INÍCIO</a></li>
            <li><a href="agendamentos_adm.php">TODAS AS RESERVAS</a></li>
            <li><a href="docentes.php">CORPO DOCENTE</a></li>
            <li><a href="logout.php" class="btn-logout">SAIR</a></li>
            <li><button id="theme-toggle" class="nav-theme-btn" aria-label="Alternar Tema">🌞</button></li>
        </ul>
    </nav>

    <main class="dashboard-wrapper">
        <header class="dash-header">
            <h1 class="massive-title">BEM VINDO, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></h1>
            <div class="glitch-line-prof"></div>
        </header>

        <section class="dash-stats">
            <div class="stat-card" style="border-top: 4px solid var(--accent-prof);">
                <h2>VISÃO DE HOJE</h2>
                <p>Existem <strong><?= $total_hoje ?></strong> reserva(s) registradas para a data de hoje.</p>
                <a href="agendamentos_adm.php" class="btn-action">GERENCIAR RESERVAS ↗</a>
            </div>
            
            <div class="stat-card" style="border-top: 4px solid var(--accent-prof);">
                <h2>CORPO DOCENTE</h2>
                <p>Atualmente o sistema possui <strong><?= $total_prof ?></strong> professor(es) cadastrado(s) no banco de dados.</p>
                <a href="docentes.php" class="btn-action">GERENCIAR DOCENTES ↗</a>
            </div>
        </section>
    </main>
</body>
</html>
