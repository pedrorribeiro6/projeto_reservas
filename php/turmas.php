<?php
require 'auth.php';
require 'conexao.php';
proteger_pagina('admin');

// Busca todas as matérias globais
$stmt_mat = $pdo->query("SELECT * FROM materias ORDER BY nome ASC");
$materias_globais = $stmt_mat->fetchAll();

// Busca todas as turmas agrupadas por segmento
$stmt_fund = $pdo->query("SELECT * FROM turmas WHERE segmento = 'fundamental' ORDER BY nome ASC");
$turmas_fundamental = $stmt_fund->fetchAll();

$stmt_med = $pdo->query("SELECT * FROM turmas WHERE segmento = 'medio' ORDER BY nome ASC");
$turmas_medio = $stmt_med->fetchAll();

// Função para buscar matérias de uma turma específica
function buscarMateriasDaTurma($pdo, $id_turma) {
    $stmt = $pdo->prepare("
        SELECT m.id, m.nome 
        FROM materias m
        JOIN turma_materias tm ON tm.id_materia = m.id
        WHERE tm.id_turma = ?
        ORDER BY m.nome ASC
    ");
    $stmt->execute([$id_turma]);
    return $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Turmas - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style3.css">
    <link rel="stylesheet" href="../css/style_adm.css">
    <link rel="stylesheet" href="../css/style7.css">
    <link rel="stylesheet" href="../css/style8.css?v=<?= time() ?>">
    <script src="../js/script.js" defer></script>
    <script src="../js/turmas.js?v=<?= time() ?>" defer></script>
    <link rel="icon" href="../imagens/sys_logo.png" type="image/png">
</head>
<body>
    <nav class="top-nav">
        <div class="nav-brand">ADM<span>.</span>RES</div>
        <ul class="nav-links">
            <li><a href="dashboard_adm.php">INÍCIO</a></li>
            <li><a href="agendamentos_adm.php">TODAS AS RESERVAS</a></li>
            <li><a href="docentes.php">CORPO DOCENTE</a></li>
            <li><a href="dispositivos.php">DISPOSITIVOS</a></li>
            <li><a href="turmas.php" class="active">TURMAS</a></li>
            <li><a href="logout.php" class="btn-logout">SAIR</a></li>
            <li><button id="theme-toggle" class="nav-theme-btn" aria-label="Alternar Tema">🌞</button></li>
        </ul>
    </nav>

    <main class="dashboard-wrapper">
        <header class="turmas-header">
            <div>
                <h1 class="massive-title">GERENCIAR TURMAS</h1>
                <div class="glitch-line-prof" style="background: var(--accent-prof);"></div>
            </div>
            <div class="turmas-header-actions">
                <button class="btn-add-global-materia" id="openAddMateriaModal">NOVA DISCIPLINA +</button>
                <button class="btn-add-turma" id="openAddTurmaModal">ADICIONAR TURMA +</button>
            </div>
        </header>

        <!-- Painel de Disciplinas Globais -->
        <section class="global-subjects-section">
            <h2>Disciplinas Cadastradas</h2>
            <p>Pool global de matérias que podem ser associadas às turmas escolares.</p>
            <div class="global-subjects-list" id="globalSubjectsList">
                <?php if (count($materias_globais) > 0): ?>
                    <?php foreach ($materias_globais as $mat): ?>
                        <div class="global-subject-card" data-id="<?= $mat['id'] ?>">
                            <span><?= htmlspecialchars($mat['nome']) ?></span>
                            <div>
                                <button class="btn-edit-sub" onclick="editarMateria(<?= $mat['id'] ?>, '<?= htmlspecialchars($mat['nome'], ENT_QUOTES) ?>')" title="Editar Nome">✏️</button>
                                <button class="btn-delete-sub" onclick="excluirMateria(<?= $mat['id'] ?>)" title="Excluir Disciplina">🗑️</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="color: #666; font-weight: bold; width: 100%;">Nenhuma disciplina cadastrada no sistema.</div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Ensino Fundamental II -->
        <section class="segment-container">
            <h2 class="segment-title">Ensino Fundamental II <span>Fund. II</span></h2>
            <div class="turmas-grid">
                <?php if (count($turmas_fundamental) > 0): ?>
                    <?php foreach ($turmas_fundamental as $turma): 
                        $materias = buscarMateriasDaTurma($pdo, $turma['id']);
                        $materia_ids = array_map(function($m) { return $m['id']; }, $materias);
                    ?>
                        <div class="turma-card" data-id="<?= $turma['id'] ?>">
                            <div class="turma-card-header">
                                <h3><?= htmlspecialchars($turma['nome']) ?></h3>
                                <span class="periodo-badge <?= $turma['periodo'] === 'manha' ? 'periodo-manha' : 'periodo-tarde' ?>">
                                    <?= $turma['periodo'] === 'manha' ? '☀️ Manhã' : '🌙 Tarde' ?>
                                </span>
                            </div>
                            <div class="turma-card-body">
                                <h4>Disciplinas Ativas:</h4>
                                <div class="turma-subjects">
                                    <?php if (count($materias) > 0): ?>
                                        <?php foreach ($materias as $mat): ?>
                                            <span class="subject-tag"><?= htmlspecialchars($mat['nome']) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span style="color: #666; font-style: italic;">Nenhuma associada</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="turma-card-footer">
                                <button class="btn-edit" onclick="abrirEditarTurma(<?= $turma['id'] ?>, '<?= htmlspecialchars($turma['nome'], ENT_QUOTES) ?>', '<?= $turma['segmento'] ?>', '<?= $turma['periodo'] ?>', <?= htmlspecialchars(json_encode($materia_ids)) ?>)">EDITAR</button>
                                <button class="btn-delete-device" onclick="excluirTurma(<?= $turma['id'] ?>)">EXCLUIR</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state-card">
                        <h3>Sem turmas cadastradas</h3>
                        <p>Não há turmas do Ensino Fundamental II atualmente no sistema.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Ensino Médio -->
        <section class="segment-container">
            <h2 class="segment-title">Ensino Médio <span>Médio</span></h2>
            <div class="turmas-grid">
                <?php if (count($turmas_medio) > 0): ?>
                    <?php foreach ($turmas_medio as $turma): 
                        $materias = buscarMateriasDaTurma($pdo, $turma['id']);
                        $materia_ids = array_map(function($m) { return $m['id']; }, $materias);
                    ?>
                        <div class="turma-card" data-id="<?= $turma['id'] ?>">
                            <div class="turma-card-header">
                                <h3><?= htmlspecialchars($turma['nome']) ?></h3>
                                <span class="periodo-badge <?= $turma['periodo'] === 'manha' ? 'periodo-manha' : 'periodo-tarde' ?>">
                                    <?= $turma['periodo'] === 'manha' ? '☀️ Manhã' : '🌙 Tarde' ?>
                                </span>
                            </div>
                            <div class="turma-card-body">
                                <h4>Disciplinas Ativas:</h4>
                                <div class="turma-subjects">
                                    <?php if (count($materias) > 0): ?>
                                        <?php foreach ($materias as $mat): ?>
                                            <span class="subject-tag"><?= htmlspecialchars($mat['nome']) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span style="color: #666; font-style: italic;">Nenhuma associada</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="turma-card-footer">
                                <button class="btn-edit" onclick="abrirEditarTurma(<?= $turma['id'] ?>, '<?= htmlspecialchars($turma['nome'], ENT_QUOTES) ?>', '<?= $turma['segmento'] ?>', '<?= $turma['periodo'] ?>', <?= htmlspecialchars(json_encode($materia_ids)) ?>)">EDITAR</button>
                                <button class="btn-delete-device" onclick="excluirTurma(<?= $turma['id'] ?>)">EXCLUIR</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state-card">
                        <h3>Sem turmas cadastradas</h3>
                        <p>Não há turmas do Ensino Médio atualmente no sistema.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Modal Adicionar/Editar Turma -->
    <div id="turmaModal" class="device-modal">
        <div class="device-modal-content" style="max-width: 600px;">
            <h2 id="turmaModalTitle">ADICIONAR NOVA TURMA</h2>
            <form id="turmaForm" class="modal-form">
                <input type="hidden" id="turmaId" name="id">
                <input type="hidden" id="turmaAcao" name="acao" value="criar_turma">
                
                <div class="modal-input-box">
                    <label>NOME DA TURMA</label>
                    <input type="text" id="turmaNome" name="nome" placeholder="Ex: 6°C, 3°EM C..." required>
                </div>

                <div class="modal-input-box" id="segmentoWrapper">
                    <label>SEGMENTO ESCOLAR</label>
                    <select id="turmaSegmento" name="segmento" style="background:transparent; border:2px solid var(--border-dark); color:var(--text-color); padding:1rem; font-size:1.2rem; font-family:'Rajdhani',sans-serif;" required>
                        <option value="fundamental">Ensino Fundamental II</option>
                        <option value="medio">Ensino Médio</option>
                    </select>
                </div>

                <div class="modal-input-box">
                    <label>PERÍODO</label>
                    <select id="turmaPeriodo" name="periodo" style="background:transparent; border:2px solid var(--border-dark); color:var(--text-color); padding:1rem; font-size:1.2rem; font-family:'Rajdhani',sans-serif;" required>
                        <option value="manha">Manhã</option>
                        <option value="tarde">Tarde</option>
                    </select>
                </div>

                <div class="modal-input-box">
                    <label>DISCIPLINAS ASSOCIADAS</label>
                    <div class="checkbox-grid" id="materiasChecklist">
                        <?php foreach ($materias_globais as $mat): ?>
                            <label class="checkbox-item">
                                <input type="checkbox" name="materias[]" value="<?= $mat['id'] ?>" class="materia-checkbox">
                                <?= htmlspecialchars($mat['nome']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-modal-cancel" id="closeTurmaModal">CANCELAR</button>
                    <button type="submit" class="btn-modal-save">SALVAR TURMA</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Adicionar/Editar Matéria Global -->
    <div id="materiaModal" class="device-modal">
        <div class="device-modal-content">
            <h2 id="materiaModalTitle">CADASTRAR NOVA DISCIPLINA</h2>
            <form id="materiaForm" class="modal-form">
                <input type="hidden" id="materiaId" name="id">
                <input type="hidden" id="materiaAcao" name="acao" value="criar_materia">
                
                <div class="modal-input-box">
                    <label>NOME DA DISCIPLINA</label>
                    <input type="text" id="materiaNome" name="nome" placeholder="Ex: Filosofia, Robótica..." required>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-modal-cancel" id="closeMateriaModal">CANCELAR</button>
                    <button type="submit" class="btn-modal-save">SALVAR DISCIPLINA</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
