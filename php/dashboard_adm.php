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
            <li><a href="dispositivos.php">DISPOSITIVOS</a></li>
            <li><a href="turmas.php">TURMAS</a></li>
            <li><a href="logout.php" class="btn-logout">SAIR</a></li>
            <li><button id="theme-toggle" class="nav-theme-btn" aria-label="Alternar Tema">🌞</button></li>
        </ul>
    </nav>

    <main class="dashboard-wrapper">
        <header class="dash-header">
            <h1 class="massive-title">PAINEL DE CONTROLE</h1>
            <div class="glitch-line-prof"></div>
        </header>

        <section class="dash-stats">
            <div class="stat-card">
                <h2>RESERVAS</h2>
                <p>Controle global de todos os agendamentos realizados pela instituição.</p>
                <a href="agendamentos_adm.php" class="btn-action">GERENCIAR →</a>
            </div>
            
            <div class="stat-card">
                <h2>CORPO DOCENTE</h2>
                <p>Visualize, busque e gerencie todos os professores cadastrados no sistema.</p>
                <a href="docentes.php" class="btn-action">VISUALIZAR →</a>
            </div>

            <div class="stat-card">
                <h2>DISPOSITIVOS</h2>
                <p>Configure o estoque total de computadores, tablets e novos itens.</p>
                <a href="dispositivos.php" class="btn-action">CONFIGURAR →</a>
            </div>

            <div class="stat-card">
                <h2>TURMAS</h2>
                <p>Cadastre turmas, defina períodos e associe disciplinas escolares para agendamentos.</p>
                <a href="turmas.php" class="btn-action">GERENCIAR →</a>
            </div>
        </section>
    </main>
</body>
</html>
