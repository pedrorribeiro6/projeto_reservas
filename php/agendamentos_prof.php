<?php
require 'auth.php';
require 'conexao.php';
proteger_pagina('professor');

$id_professor = $_SESSION['usuario_id'];

// Busca os agendamentos deste professor ordenados pela data mais próxima
$stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE id_professor = ? ORDER BY data_reserva ASC, horario_inicio ASC");
$stmt->execute([$id_professor]);
$agendamentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos - Professor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style3.css">
    <link rel="stylesheet" href="../css/style5.css">
    <script src="../js/script.js" defer></script>
    <script src="../js/funcionalidade.js?v=<?= time() ?>" defer></script>
    <link rel="icon" href="../imagens/sys_logo.png" type="image/png">
</head>
<body>
    <nav class="top-nav">
        <div class="nav-brand">PROF<span>.</span>RES</div>
        <ul class="nav-links">
            <li><a href="dashboard_prof.php">INÍCIO</a></li>
            <li><a href="agendamentos_prof.php" class="active">MEUS AGENDAMENTOS</a></li>
            <li><a href="agendar.php">AGENDAR</a></li>
            <li><a href="logout.php" class="btn-logout">SAIR</a></li>
            <li><button id="theme-toggle" class="nav-theme-btn" aria-label="Alternar Tema">🌞</button></li>
        </ul>
    </nav>

    <main class="dashboard-wrapper">
        <header class="dash-header">
            <h1 class="massive-title">MEUS AGENDAMENTOS</h1>
            <div class="glitch-line-prof"></div>
        </header>

        <section class="bookings-list">
            <?php 
            if (count($agendamentos) > 0): 
                foreach ($agendamentos as $ag): 
                    // Busca os itens específicos desta reserva
                    $stmt_itens = $pdo->prepare("
                        SELECT i.quantidade, e.nome 
                        FROM agendamento_itens i 
                        JOIN equipamentos e ON i.id_equipamento = e.id 
                        WHERE i.id_agendamento = ?
                    ");
                    $stmt_itens->execute([$ag['id']]);
                    $itens = $stmt_itens->fetchAll();
            ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <span class="booking-date"><?= date('d/m/Y', strtotime($ag['data_reserva'])) ?></span>
                            <span class="booking-time"><?= date('H:i', strtotime($ag['horario_inicio'])) ?> - <?= date('H:i', strtotime($ag['horario_fim'])) ?></span>
                        </div>
                        
                        <?php if (!empty($ag['segmento']) || !empty($ag['ano_turma']) || !empty($ag['disciplina'])): ?>
                            <div style="padding: 0.6rem 1.5rem; background: rgba(255, 255, 255, 0.04); border-bottom: 2px solid var(--border-dark); font-size: 0.95rem; font-family: 'Rajdhani', sans-serif; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: flex; gap: 1rem; flex-wrap: wrap;">
                                <span>🏫 <span style="color: var(--accent-prof);"><?= htmlspecialchars($ag['ano_turma']) ?></span> (<?= $ag['segmento'] === 'fundamental' ? 'Fund. II' : 'Médio' ?>)</span>
                                <span>📚 DISCIPLINA: <span style="color: var(--accent-prof);"><?= htmlspecialchars($ag['disciplina']) ?></span></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="booking-body" style="border-top: <?= !empty($ag['segmento']) ? 'none' : 'default' ?>;">
                            <?php if (count($itens) > 0): ?>
                                <?php foreach ($itens as $item): ?>
                                    <div class="equip-tag">📦 <?= htmlspecialchars($item['nome']) ?>: <strong><?= $item['quantidade'] ?></strong></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Fallback para reservas legadas (antes da migração) -->
                                <?php if($ag['qtd_computadores'] > 0): ?><div class="equip-tag">💻 PCs: <strong><?= $ag['qtd_computadores'] ?></strong></div><?php endif; ?>
                                <?php if($ag['qtd_tablets'] > 0): ?><div class="equip-tag">📱 Tablets: <strong><?= $ag['qtd_tablets'] ?></strong></div><?php endif; ?>
                                <?php if($ag['qtd_celulares'] > 0): ?><div class="equip-tag">📲 Celulares: <strong><?= $ag['qtd_celulares'] ?></strong></div><?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="booking-footer" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>ID da Reserva: #<?= str_pad($ag['id'], 4, '0', STR_PAD_LEFT) ?></span>
                            <button class="btn-delete" onclick="confirmarExclusao(<?= $ag['id'] ?>)">EXCLUIR</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h2>NENHUM AGENDAMENTO ENCONTRADO</h2>
                    <p>Você ainda não realizou nenhuma reserva de equipamentos.</p>
                    <a href="agendar.php" class="btn-action" style="margin-top: 1rem;">FAZER RESERVA</a>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
