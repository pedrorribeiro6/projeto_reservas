<?php
require 'auth.php';
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
    <link rel="stylesheet" href="../css/style3.css">
    <link rel="stylesheet" href="../css/style4.css">
    <script src="../js/script.js" defer></script>
    <script src="../js/reserva.js" defer></script>
    <script src="../js/funcionalidade.js" defer></script>
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
                    <script>
                        document.getElementById('data_reserva').addEventListener('change', function () {
                            const aviso = document.getElementById('aviso-fds');
                            if (!this.value) { aviso.style.display = 'none'; return; }
                            const dia = new Date(this.value + 'T12:00:00').getDay();
                            if (dia === 0 || dia === 6) {
                                aviso.style.display = 'block';
                                this.style.borderColor = '#ff4d4d';
                            } else {
                                aviso.style.display = 'none';
                                this.style.borderColor = '';
                            }
                        });
                    </script>
                    <div class="input-box">
                        <label>HORÁRIO INÍCIO</label>
                        <input type="time" id="horario_inicio" name="horario_inicio" required>
                    </div>
                    <div class="input-box">
                        <label>HORÁRIO FIM</label>
                        <input type="time" id="horario_fim" name="horario_fim" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>2. EQUIPAMENTOS (MÁXIMOS PERMITIDOS)</h2>
                <div class="grid-equipamentos">
                    <div class="equip-card">
                        <h3>COMPUTADORES</h3>
                        <p class="limit-badge">MÁX: 35</p>
                        <input type="number" id="qtd_computadores" name="qtd_computadores" min="0" max="35" value="0">
                    </div>
                    <div class="equip-card">
                        <h3>TABLETS</h3>
                        <p class="limit-badge">MÁX: 24</p>
                        <input type="number" id="qtd_tablets" name="qtd_tablets" min="0" max="24" value="0">
                    </div>
                    <div class="equip-card">
                        <h3>CELULARES</h3>
                        <p class="limit-badge">MÁX: 12</p>
                        <input type="number" id="qtd_celulares" name="qtd_celulares" min="0" max="12" value="0">
                    </div>
                </div>
            </div>
            
            <div id="booking-feedback" class="feedback-msg hidden"></div>

            <button type="submit" class="btn-action btn-submit-booking">CONFIRMAR AGENDAMENTO</button>
        </form>
    </main>
</body>
</html>
