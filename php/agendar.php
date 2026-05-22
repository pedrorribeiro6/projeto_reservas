<?php
require 'auth.php';
require 'conexao.php';
proteger_pagina('professor');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Agendamento - Professor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style3.css?v=3.0">
    <link rel="stylesheet" href="../css/style4.css?v=3.0">

    <link rel="icon" href="../imagens/sys_logo.png" type="image/png">
</head>
<body>
    <nav class="top-nav">
        <div class="nav-brand">PROF<span>.</span>RES</div>
        <ul class="nav-links">
            <li><a href="dashboard_prof.php">INÍCIO</a></li>
            <li><a href="agendamentos_prof.php">MEUS AGENDAMENTOS</a></li>
            <li><a href="agendar.php" class="active">AGENDAR</a></li>
            <li><a href="logout.php" class="btn-logout">SAIR</a></li>
            <li><button id="theme-toggle" class="nav-theme-btn" aria-label="Alternar Tema">🌞</button></li>
        </ul>
    </nav>

    <main class="dashboard-wrapper">
        <header class="dash-header">
            <h1 class="massive-title">NOVA RESERVA</h1>
            <div class="glitch-line-prof"></div>
        </header>

        <form id="formAgendamento" class="brutalist-booking-form">
            <div class="form-section">
                <h2>1. DADOS DE TEMPO</h2>
                <div class="grid-inputs">
                    <div class="input-box">
                        <label>DATA DA AULA</label>
                        <input type="date" id="data_reserva" name="data_reserva" required>
                        <span id="aviso-fds" style="display:none; color:#ff4d4d; font-size:0.78rem; font-weight:700; margin-top:4px; letter-spacing:0.05em;">
                            ⛔ FINAIS DE SEMANA NÃO SÃO PERMITIDOS
                        </span>
                    </div>

                    
                    <div class="input-box">
                        <label>SEGMENTO</label>
                        <select id="segmento" name="segmento" required>
                            <option value="">SELECIONE...</option>
                            <option value="fundamental">Ensino Fundamental II</option>
                            <option value="medio">Ensino Médio</option>
                        </select>
                    </div>

                    <div class="input-box">
                        <label>ANO / TURMA</label>
                        <select id="ano_turma" name="ano_turma" required disabled>
                            <option value="">SELECIONE O SEGMENTO...</option>
                        </select>
                    </div>

                    <div class="input-box">
                        <label>DISCIPLINA</label>
                        <select id="disciplina" name="disciplina" required disabled>
                            <option value="">SELECIONE A TURMA...</option>
                        </select>
                    </div>

                    <div class="input-box">
                        <label>AULA DE INÍCIO</label>
                        <select id="aula_inicio" required disabled>
                            <option value="">SELECIONE A TURMA...</option>
                        </select>
                    </div>

                    <div class="input-box">
                        <label>DURAÇÃO</label>
                        <select id="duracao_aulas" required disabled>
                            <option value="1">1 Aula (50 minutos)</option>
                            <option value="2">2 Aulas (até 1h 40m)</option>
                        </select>
                    </div>
                </div>

                <!-- Campos ocultos para manter retrocompatibilidade com o banco de dados -->
                <input type="hidden" id="horario_inicio" name="horario_inicio">
                <input type="hidden" id="horario_fim" name="horario_fim">
            </div>

            <div class="form-section">
                <h2>2. EQUIPAMENTOS (MÁXIMOS PERMITIDOS)</h2>
                <div class="grid-equipamentos">
                    <?php
                    // Busca todos os equipamentos disponíveis
                    $stmt_eq = $pdo->query("SELECT * FROM equipamentos ORDER BY nome ASC");
                    $equipamentos = $stmt_eq->fetchAll();
                    
                    foreach ($equipamentos as $eq): 
                    ?>
                        <div class="equip-card" data-id="<?= $eq['id'] ?>">
                            <h3><?= htmlspecialchars($eq['nome']) ?></h3>
                            <p class="limit-badge">MÁX: <?= $eq['quantidade_total'] ?></p>
                            <input type="number" 
                                   id="equip_<?= $eq['id'] ?>" 
                                   name="quantidade[<?= $eq['id'] ?>]" 
                                   class="equip-input"
                                   min="0" 
                                   max="<?= $eq['quantidade_total'] ?>" 
                                   value="0"
                                   data-max-original="<?= $eq['quantidade_total'] ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div id="booking-feedback" class="feedback-msg hidden"></div>

            <button type="submit" class="btn-action btn-submit-booking">CONFIRMAR AGENDAMENTO</button>
        </form>
    </main>
    <script src="../js/script.js?v=2.2"></script>
    <script src="../js/reserva.js?v=2.2"></script>
    <script src="../js/funcionalidade.js?v=2.2"></script>
</body>
</html>
