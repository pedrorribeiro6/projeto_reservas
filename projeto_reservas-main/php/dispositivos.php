<?php
require 'auth.php';
require 'conexao.php';
proteger_pagina('admin');

// Busca todos os equipamentos
$stmt = $pdo->query("SELECT * FROM equipamentos ORDER BY nome ASC");
$equipamentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Dispositivos - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style3.css">
    <link rel="stylesheet" href="../css/style_adm.css">
    <link rel="stylesheet" href="../css/style7.css">
    <script src="../js/script.js" defer></script>
    <script src="../js/funcionalidade.js" defer></script>
    <script src="../js/dispositivos.js" defer></script>
    <link rel="icon" href="../imagens/sys_logo.png" type="image/png">
</head>
<body>
    <nav class="top-nav">
        <div class="nav-brand">ADM<span>.</span>RES</div>
        <ul class="nav-links">
            <li><a href="dashboard_adm.php">INÍCIO</a></li>
            <li><a href="agendamentos_adm.php">TODAS AS RESERVAS</a></li>
            <li><a href="docentes.php">CORPO DOCENTE</a></li>
            <li><a href="dispositivos.php" class="active">DISPOSITIVOS</a></li>
            <li><a href="logout.php" class="btn-logout">SAIR</a></li>
            <li><button id="theme-toggle" class="nav-theme-btn" aria-label="Alternar Tema">🌞</button></li>
        </ul>
    </nav>

    <main class="dashboard-wrapper">
        <header class="devices-header">
            <div>
                <h1 class="massive-title">GERENCIAR DISPOSITIVOS</h1>
                <div class="glitch-line-prof" style="background: var(--accent-prof);"></div>
            </div>
            <button class="btn-add-device" id="openAddModal">ADICIONAR NOVO +</button>
        </header>

        <section class="devices-grid" id="devicesGrid">
            <?php foreach ($equipamentos as $eq): ?>
                <div class="device-card" data-id="<?= $eq['id'] ?>">
                    <div class="device-info">
                        <h3><?= htmlspecialchars($eq['nome']) ?></h3>
                        <p class="device-stock">ESTOQUE TOTAL: <span><?= $eq['quantidade_total'] ?></span></p>
                    </div>
                    <div class="device-actions">
                        <button class="btn-edit" onclick="editDevice(<?= $eq['id'] ?>, '<?= htmlspecialchars($eq['nome']) ?>', <?= $eq['quantidade_total'] ?>)">EDITAR</button>
                        <button class="btn-delete-device" onclick="deleteDevice(<?= $eq['id'] ?>)">EXCLUIR</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </main>

    <!-- Modal Adicionar/Editar -->
    <div id="deviceModal" class="device-modal">
        <div class="device-modal-content">
            <h2 id="modalTitle">ADICIONAR DISPOSITIVO</h2>
            <form id="deviceForm" class="modal-form">
                <input type="hidden" id="deviceId" name="id">
                <div class="modal-input-box">
                    <label>NOME DO DISPOSITIVO</label>
                    <input type="text" id="deviceName" name="nome" placeholder="Ex: Chromebooks" required>
                </div>
                <div class="modal-input-box">
                    <label>QUANTIDADE TOTAL NO ESTOQUE</label>
                    <input type="number" id="deviceQty" name="quantidade_total" min="1" value="1" required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-modal-cancel" id="closeModal">CANCELAR</button>
                    <button type="submit" class="btn-modal-save">SALVAR ALTERAÇÕES</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
