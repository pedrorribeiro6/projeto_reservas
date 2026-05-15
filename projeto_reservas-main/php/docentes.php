<?php
require 'auth.php';
require 'conexao.php';
proteger_pagina('admin');

// Busca todos os professores
$stmt = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE tipo_conta = 'professor' ORDER BY nome ASC");
$stmt->execute();
$professores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corpo Docente - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style3.css">
    <link rel="stylesheet" href="../css/style_adm.css">
    <link rel="stylesheet" href="../css/style6.css">
    <script src="../js/script.js" defer></script>
    <script src="../js/docentes.js" defer></script>
    <link rel="icon" href="../imagens/sys_logo.png" type="image/png">
</head>
<body>
    <nav class="top-nav">
        <div class="nav-brand">ADM<span>.</span>RES</div>
        <ul class="nav-links">
            <li><a href="dashboard_adm.php">INÍCIO</a></li>
            <li><a href="agendamentos_adm.php">TODAS AS RESERVAS</a></li>
            <li><a href="docentes.php" class="active">CORPO DOCENTE</a></li>
            <li><a href="logout.php" class="btn-logout">SAIR</a></li>
            <li><button id="theme-toggle" class="nav-theme-btn" aria-label="Alternar Tema">🌞</button></li>
        </ul>
    </nav>

    <main class="dashboard-wrapper">
        <header class="dash-header">
            <h1 class="massive-title">CORPO DOCENTE</h1>
            <div class="glitch-line-prof"></div>
        </header>

        <div class="search-bar">
            <input type="text" id="searchDocente" placeholder="BUSCAR POR NOME OU E-MAIL...">
        </div>

        <section class="docentes-grid" id="docentesGrid">
            <?php if (count($professores) > 0): ?>
                <?php foreach ($professores as $prof): ?>
                    <div class="docente-card" data-nome="<?= strtolower(htmlspecialchars($prof['nome'])) ?>" data-email="<?= strtolower(htmlspecialchars($prof['email'])) ?>">
                        <div class="docente-info">
                            <h3><?= htmlspecialchars($prof['nome']) ?></h3>
                            <p class="docente-email"><?= htmlspecialchars($prof['email']) ?></p>
                        </div>
                        <div class="docente-stats">
                            <?php
                            // Opcional: Contar reservas deste professor
                            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE id_professor = ?");
                            $stmt_count->execute([$prof['id']]);
                            $reservas_count = $stmt_count->fetchColumn();
                            ?>
                            <span class="badge-reservas"><?= $reservas_count ?> RESERVAS</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h2>NENHUM DOCENTE ENCONTRADO</h2>
                    <p>Não há professores cadastrados no sistema.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
