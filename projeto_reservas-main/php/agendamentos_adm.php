<?php
require 'auth.php';
require 'conexao.php';
proteger_pagina('admin');

// Busca TODAS as reservas e junta com a tabela de usuários para puxar o nome do Docente responsável
$query = "
    SELECT a.*, u.nome as professor_nome 
    FROM agendamentos a 
    JOIN usuarios u ON a.id_professor = u.id 
    ORDER BY a.data_reserva ASC, a.horario_inicio ASC
";
$stmt = $pdo->query($query);
$agendamentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle Global - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style3.css">
    <link rel="stylesheet" href="../css/style5.css">
    <link rel="stylesheet" href="../css/style_adm.css">
    <link rel="stylesheet" href="../css/style6.css">
    <script src="../js/script.js" defer></script>
    <script src="../js/funcionalidade.js?v=<?= time() ?>" defer></script>
    <script src="../js/filtro_adm.js?v=<?= time() ?>" defer></script>
    <link rel="icon" href="../imagens/sys_logo.png" type="image/png">
</head>
<body>
    <nav class="top-nav">
        <div class="nav-brand">ADM<span>.</span>RES</div>
        <ul class="nav-links">
            <li><a href="dashboard_adm.php">INÍCIO</a></li>
            <li><a href="agendamentos_adm.php" class="active">TODAS AS RESERVAS</a></li>
            <li><a href="docentes.php">CORPO DOCENTE</a></li>
            <li><a href="dispositivos.php">DISPOSITIVOS</a></li>
            <li><a href="logout.php" class="btn-logout">SAIR</a></li>
            <li><button id="theme-toggle" class="nav-theme-btn" aria-label="Alternar Tema">🌞</button></li>
        </ul>
    </nav>

    <main class="dashboard-wrapper">
        <header class="dash-header">
            <h1 class="massive-title">CONTROLE GLOBAL</h1>
            <div class="glitch-line-prof"></div>
        </header>

        <div class="search-bar">
            <input type="text" id="searchReserva" placeholder="BUSCAR RESERVAS POR NOME DO PROFESSOR...">
        </div>

        <section class="bookings-list" id="bookingsGrid">
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
                    <div class="booking-card" data-professor="<?= strtolower(htmlspecialchars($ag['professor_nome'])) ?>" style="border-left: 6px solid var(--accent-prof);">
                        <div class="booking-header">
                            <span class="booking-date"><?= date('d/m/Y', strtotime($ag['data_reserva'])) ?></span>
                            <span class="booking-time"><?= date('H:i', strtotime($ag['horario_inicio'])) ?> - <?= date('H:i', strtotime($ag['horario_fim'])) ?></span>
                        </div>
                        
                        <div style="padding: 1rem 1.5rem; background: rgba(0,0,0,0.2); border-bottom: 2px solid var(--border-dark); border-top: 2px solid var(--border-dark);">
                            <span style="color: #888; font-weight: bold; font-size:1.1rem;">DOCENTE:</span> 
                            <span style="font-size: 1.4rem; font-weight: 700; margin-left: 0.5rem; color: var(--text-color);"><?= htmlspecialchars($ag['professor_nome']) ?></span>
                        </div>

                        <div class="booking-body">
                            <?php if (count($itens) > 0): ?>
                                <?php foreach ($itens as $item): ?>
                                    <div class="equip-tag">📦 <?= htmlspecialchars($item['nome']) ?>: <strong><?= $item['quantidade'] ?></strong></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Fallback para reservas legadas -->
                                <?php if($ag['qtd_computadores'] > 0): ?><div class="equip-tag">💻 PCs: <strong><?= $ag['qtd_computadores'] ?></strong></div><?php endif; ?>
                                <?php if($ag['qtd_tablets'] > 0): ?><div class="equip-tag">📱 Tablets: <strong><?= $ag['qtd_tablets'] ?></strong></div><?php endif; ?>
                                <?php if($ag['qtd_celulares'] > 0): ?><div class="equip-tag">📲 Celulares: <strong><?= $ag['qtd_celulares'] ?></strong></div><?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="booking-footer" style="display: flex; justify-content: space-between; align-items: center;">
                            <span>ID da Reserva: #<?= str_pad($ag['id'], 4, '0', STR_PAD_LEFT) ?></span>
                            <button class="btn-delete" onclick="confirmarExclusao(<?= $ag['id'] ?>)">EXCLUIR FORÇADAMENTE</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h2>SISTEMA VAZIO</h2>
                    <p>Nenhuma reserva foi encontrada no banco de dados da escola.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
