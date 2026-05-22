// --- Custom Modais de Exclusão Neo-Brutalistas ---
function customConfirm(titulo, mensagem) {
    return new Promise((resolve) => {
        // Remove modal anterior se houver
        const modalExistente = document.getElementById('custom-confirm-modal');
        if (modalExistente) modalExistente.remove();

        const modalHTML = `
        <div id="custom-confirm-modal" style="
            display: flex; position: fixed; inset: 0; z-index: 20000;
            background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(8px);
            align-items: center; justify-content: center;
            animation: fadeIn 0.2s ease-out both;">
            <div class="custom-modal-content" style="
                background: #0F0F13; border: 3px solid var(--accent-red);
                padding: 2.5rem 2rem; max-width: 500px; width: 90%;
                box-shadow: 10px 10px 0 #000; position: relative;
                font-family: 'Rajdhani', sans-serif;
                animation: scaleIn 0.25s cubic-bezier(0.22, 1, 0.36, 1) both;">
                
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--accent-red); font-weight: 700;">⚠️ ATENÇÃO</div>
                <h2 style="color: var(--text-color); font-size: 1.6rem; letter-spacing: 0.05em; text-transform: uppercase; margin: 0 0 1rem; border-bottom: 2px solid var(--border-dark); padding-bottom: 0.5rem; font-weight: 700;">
                    ${titulo}
                </h2>
                <p style="color: #ccc; font-size: 1.1rem; line-height: 1.4; margin: 0 0 2rem; font-weight: 500;">
                    ${mensagem}
                </p>
                <div style="display: flex; gap: 1rem;">
                    <button id="confirm-btn-cancel" style="
                        flex: 1; background: transparent; color: var(--text-color);
                        border: 2px solid var(--border-dark); padding: 0.8rem;
                        font-family: 'Rajdhani', sans-serif; font-weight: 700;
                        font-size: 1rem; text-transform: uppercase; cursor: pointer;
                        transition: all 0.2s ease;">
                        CANCELAR
                    </button>
                    <button id="confirm-btn-ok" style="
                        flex: 2; background: var(--accent-red); color: #fff;
                        border: none; padding: 0.8rem;
                        font-family: 'Rajdhani', sans-serif; font-weight: 700;
                        font-size: 1rem; text-transform: uppercase; cursor: pointer;
                        box-shadow: 4px 4px 0 #000; transition: all 0.2s ease;">
                        EXCLUIR
                    </button>
                </div>
            </div>
        </div>
        <style>
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes scaleIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
            
            #confirm-btn-cancel:hover {
                border-color: var(--text-color);
                background: rgba(255,255,255,0.05);
            }
            #confirm-btn-ok:hover {
                transform: translate(-2px, -2px);
                box-shadow: 6px 6px 0 #000;
            }
            
            /* Light theme support */
            .light-theme #custom-confirm-modal .custom-modal-content {
                background: #FFFFFF !important;
            }
            .light-theme #custom-confirm-modal p {
                color: #333 !important;
            }
        </style>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        const modal = document.getElementById('custom-confirm-modal');
        const btnCancel = document.getElementById('confirm-btn-cancel');
        const btnOk = document.getElementById('confirm-btn-ok');

        btnCancel.addEventListener('click', () => {
            modal.remove();
            resolve(false);
        });

        btnOk.addEventListener('click', () => {
            modal.remove();
            resolve(true);
        });
    });
}

function customAlert(titulo, mensagem) {
    return new Promise((resolve) => {
        // Remove modal anterior se houver
        const modalExistente = document.getElementById('custom-alert-modal');
        if (modalExistente) modalExistente.remove();

        const modalHTML = `
        <div id="custom-alert-modal" style="
            display: flex; position: fixed; inset: 0; z-index: 20000;
            background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(8px);
            align-items: center; justify-content: center;
            animation: fadeIn 0.2s ease-out both;">
            <div class="custom-modal-content" style="
                background: #0F0F13; border: 3px solid #00FF66;
                padding: 2.5rem 2rem; max-width: 450px; width: 90%;
                box-shadow: 10px 10px 0 #000; position: relative;
                font-family: 'Rajdhani', sans-serif; text-align: center;
                animation: scaleIn 0.25s cubic-bezier(0.22, 1, 0.36, 1) both;">
                
                <div style="font-size: 3rem; margin-bottom: 0.5rem; color: #00FF66;">⚡</div>
                <h2 style="color: #00FF66; font-size: 1.8rem; letter-spacing: 0.05em; text-transform: uppercase; margin: 0 0 1rem; font-weight: 700;">
                    ${titulo}
                </h2>
                <p style="color: #ccc; font-size: 1.1rem; line-height: 1.4; margin: 0 0 2rem; font-weight: 500;">
                    ${mensagem}
                </p>
                <button id="alert-btn-ok" style="
                    width: 100%; background: #00FF66; color: #000;
                    border: none; padding: 0.8rem;
                    font-family: 'Rajdhani', sans-serif; font-weight: 700;
                    font-size: 1.1rem; text-transform: uppercase; cursor: pointer;
                    box-shadow: 4px 4px 0 #000; transition: all 0.2s ease;">
                    OK, ENTENDIDO
                </button>
            </div>
        </div>
        <style>
            #alert-btn-ok:hover {
                transform: translate(-2px, -2px);
                box-shadow: 6px 6px 0 #000;
            }
            
            /* Light theme support */
            .light-theme #custom-alert-modal .custom-modal-content {
                background: #FFFFFF !important;
            }
            .light-theme #custom-alert-modal p {
                color: #333 !important;
            }
        </style>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        const modal = document.getElementById('custom-alert-modal');
        const btnOk = document.getElementById('alert-btn-ok');

        btnOk.addEventListener('click', () => {
            modal.remove();
            resolve();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // ── Elementos do Modal de Turma ───────────────────
    const turmaModal = document.getElementById('turmaModal');
    const openAddTurmaBtn = document.getElementById('openAddTurmaModal');
    const closeTurmaBtn = document.getElementById('closeTurmaModal');
    const turmaForm = document.getElementById('turmaForm');
    const turmaModalTitle = document.getElementById('turmaModalTitle');
    const turmaIdInput = document.getElementById('turmaId');
    const turmaAcaoInput = document.getElementById('turmaAcao');
    const turmaNomeInput = document.getElementById('turmaNome');
    const turmaSegmentoSelect = document.getElementById('turmaSegmento');
    const turmaPeriodoSelect = document.getElementById('turmaPeriodo');
    const segmentoWrapper = document.getElementById('segmentoWrapper');
    const checkboxes = document.querySelectorAll('.materia-checkbox');

    // ── Elementos do Modal de Matéria ─────────────────
    const materiaModal = document.getElementById('materiaModal');
    const openAddMateriaBtn = document.getElementById('openAddMateriaModal');
    const closeMateriaBtn = document.getElementById('closeMateriaModal');
    const materiaForm = document.getElementById('materiaForm');
    const materiaModalTitle = document.getElementById('materiaModalTitle');
    const materiaIdInput = document.getElementById('materiaId');
    const materiaAcaoInput = document.getElementById('materiaAcao');
    const materiaNomeInput = document.getElementById('materiaNome');

    // ── Lógica do Modal de Turma ──────────────────────
    if (openAddTurmaBtn) {
        openAddTurmaBtn.addEventListener('click', () => {
            turmaForm.reset();
            turmaModalTitle.textContent = "ADICIONAR NOVA TURMA";
            turmaIdInput.value = "";
            turmaAcaoInput.value = "criar_turma";
            segmentoWrapper.style.display = "flex";
            turmaSegmentoSelect.disabled = false;
            
            // Desmarca todos os checkboxes
            checkboxes.forEach(cb => cb.checked = false);

            turmaModal.style.display = "flex";
        });
    }

    if (closeTurmaBtn) {
        closeTurmaBtn.addEventListener('click', () => {
            turmaModal.style.display = "none";
        });
    }

    // Função global para abrir o modal de edição de turma
    window.abrirEditarTurma = (id, nome, segmento, periodo, materiasSelecionadas) => {
        turmaForm.reset();
        turmaModalTitle.textContent = "EDITAR TURMA: " + nome.toUpperCase();
        turmaIdInput.value = id;
        turmaAcaoInput.value = "editar_turma";
        
        turmaNomeInput.value = nome;
        turmaSegmentoSelect.value = segmento;
        // O segmento é fixo para evitar inconsistências nos agendamentos passados
        segmentoWrapper.style.display = "none";
        turmaSegmentoSelect.disabled = true;

        turmaPeriodoSelect.value = periodo;

        // Marca as matérias associadas
        checkboxes.forEach(cb => {
            const val = parseInt(cb.value);
            cb.checked = materiasSelecionadas.includes(val);
        });

        turmaModal.style.display = "flex";
    };

    // Submissão do Formulário de Turma via AJAX
    if (turmaForm) {
        turmaForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Ativa temporariamente o select desabilitado para enviar no form
            turmaSegmentoSelect.disabled = false;
            const formData = new FormData(turmaForm);
            if (turmaAcaoInput.value === 'editar_turma') {
                // se for edição, re-desabilita visualmente se der erro
                turmaSegmentoSelect.disabled = true;
            }

            try {
                const response = await fetch('processa_turma.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.sucesso) {
                    await customAlert('TURMA SALVA', 'As alterações na turma foram salvas com sucesso!');
                    window.location.reload();
                } else {
                    await customAlert('FALHA OPERACIONAL', 'Erro: ' + res.erro);
                }
            } catch (err) {
                console.error(err);
                await customAlert('FALHA DE CONEXÃO', 'Erro de comunicação com o servidor.');
            }
        });
    }

    // Função global para excluir turma com confirmação
    window.excluirTurma = async (id) => {
        const confirmado = await customConfirm(
            'CONFIRMAR EXCLUSÃO DE TURMA',
            'ATENÇÃO: Você tem certeza que deseja excluir esta turma? Isso removerá a turma do sistema (histórico de reservas antigas não será excluído, mas novos agendamentos para ela estarão indisponíveis).'
        );
        if (!confirmado) {
            return;
        }

        const formData = new FormData();
        formData.append('acao', 'excluir_turma');
        formData.append('id', id);

        try {
            const response = await fetch('processa_turma.php', {
                method: 'POST',
                body: formData
            });
            const res = await response.json();

            if (res.sucesso) {
                await customAlert('EXCLUSÃO CONCLUÍDA', 'A turma foi excluída com sucesso do sistema!');
                window.location.reload();
            } else {
                await customAlert('ERRO AO EXCLUIR', 'Erro ao excluir: ' + res.erro);
            }
        } catch (err) {
            console.error(err);
            await customAlert('ERRO DE CONEXÃO', 'Erro de comunicação ao tentar excluir.');
        }
    };

    // ── Lógica do Modal de Matéria ────────────────────
    if (openAddMateriaBtn) {
        openAddMateriaBtn.addEventListener('click', () => {
            materiaForm.reset();
            materiaModalTitle.textContent = "CADASTRAR NOVA DISCIPLINA";
            materiaIdInput.value = "";
            materiaAcaoInput.value = "criar_materia";

            materiaModal.style.display = "flex";
        });
    }

    if (closeMateriaBtn) {
        closeMateriaBtn.addEventListener('click', () => {
            materiaModal.style.display = "none";
        });
    }

    // Função global para abrir o modal de edição de matéria
    window.editarMateria = (id, nome) => {
        materiaForm.reset();
        materiaModalTitle.textContent = "EDITAR DISCIPLINA";
        materiaIdInput.value = id;
        materiaAcaoInput.value = "editar_materia";
        materiaNomeInput.value = nome;

        materiaModal.style.display = "flex";
    };

    // Submissão do Formulário de Matéria via AJAX
    if (materiaForm) {
        materiaForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(materiaForm);

            try {
                const response = await fetch('processa_turma.php', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.sucesso) {
                    await customAlert('DISCIPLINA SALVA', 'A disciplina foi salva com sucesso no pool global!');
                    window.location.reload();
                } else {
                    await customAlert('FALHA OPERACIONAL', 'Erro: ' + res.erro);
                }
            } catch (err) {
                console.error(err);
                await customAlert('FALHA DE CONEXÃO', 'Erro de comunicação com o servidor.');
            }
        });
    }

    // Função global para excluir matéria com confirmação
    window.excluirMateria = async (id) => {
        const confirmado = await customConfirm(
            'CONFIRMAR EXCLUSÃO DE DISCIPLINA',
            'ATENÇÃO: Tem certeza que deseja excluir esta disciplina do pool global? Isso removerá a matéria de todas as turmas que a possuem vinculada.'
        );
        if (!confirmado) {
            return;
        }

        const formData = new FormData();
        formData.append('acao', 'excluir_materia');
        formData.append('id', id);

        try {
            const response = await fetch('processa_turma.php', {
                method: 'POST',
                body: formData
            });
            const res = await response.json();

            if (res.sucesso) {
                await customAlert('EXCLUSÃO CONCLUÍDA', 'A disciplina foi excluída com sucesso do pool global!');
                window.location.reload();
            } else {
                await customAlert('ERRO AO EXCLUIR', 'Erro ao excluir: ' + res.erro);
            }
        } catch (err) {
            console.error(err);
            await customAlert('ERRO DE CONEXÃO', 'Erro de comunicação ao tentar excluir.');
        }
    };

    // Fechar modais ao clicar fora do conteúdo
    window.addEventListener('click', (e) => {
        if (e.target === turmaModal) {
            turmaModal.style.display = "none";
        }
        if (e.target === materiaModal) {
            materiaModal.style.display = "none";
        }
    });
});
