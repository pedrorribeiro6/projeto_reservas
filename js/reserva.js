document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formAgendamento');
    if (!form) return;

    // Elementos de Controle do Formulário
    const segmentoSelect = document.getElementById('segmento');
    const anoTurmaSelect = document.getElementById('ano_turma');
    const disciplinaSelect = document.getElementById('disciplina');
    const aulaInicioSelect = document.getElementById('aula_inicio');
    const duracaoSelect = document.getElementById('duracao_aulas');
    const dataInput = document.getElementById('data_reserva');
    const hInicioInput = document.getElementById('horario_inicio');
    const hFimInput = document.getElementById('horario_fim');
    let turmasCarregadas = [];

    // ── Bloqueio de Data Mínima (hoje) e Aviso Visual de Fim de Semana ────
    if (dataInput) {
        const hoje = new Date();
        const yyyy = hoje.getFullYear();
        const mm = String(hoje.getMonth() + 1).padStart(2, '0');
        const dd = String(hoje.getDate()).padStart(2, '0');
        dataInput.setAttribute('min', `${yyyy}-${mm}-${dd}`);

        dataInput.addEventListener('change', function () {
            const aviso = document.getElementById('aviso-fds');
            if (!this.value) { if (aviso) aviso.style.display = 'none'; return; }
            const dia = new Date(this.value + 'T12:00:00').getDay();
            if (dia === 0 || dia === 6) {
                if (aviso) aviso.style.display = 'block';
                this.style.borderColor = '#ff4d4d';
            } else {
                if (aviso) aviso.style.display = 'none';
                this.style.borderColor = '';
            }
        });
    }

    // ── Definição dos Slots de Aulas e Regras de Negócio ─────────────────

    // Grade do Ensino Fundamental II (Manhã) - Aulas de 50 minutos e intervalo 10:20-10:40
    const slotsFundamentalManha = [
        { id: 'FM1', label: '1ª Aula (07:00 - 07:50)', inicio: '07:00', fim1: '07:50', fim2: '08:40', canDouble: true },
        { id: 'FM2', label: '2ª Aula (07:50 - 08:40)', inicio: '07:50', fim1: '08:40', fim2: '09:30', canDouble: true },
        { id: 'FM3', label: '3ª Aula (08:40 - 09:30)', inicio: '08:40', fim1: '09:30', fim2: '10:20', canDouble: true },
        { id: 'FM4', label: '4ª Aula (09:30 - 10:20)', inicio: '09:30', fim1: '10:20', fim2: '', canDouble: false }, // Intervalo a seguir (10:20 - 10:40)
        { id: 'FM5', label: '5ª Aula (10:40 - 11:30)', inicio: '10:40', fim1: '11:30', fim2: '12:20', canDouble: true },
        { id: 'FM6', label: '6ª Aula (11:30 - 12:20)', inicio: '11:30', fim1: '12:20', fim2: '', canDouble: false }
    ];

    // Grade do Ensino Fundamental II (Tarde) - Aulas com duração variada e intervalo 15:10-15:30
    const slotsFundamentalTarde = [
        { id: 'FT1', label: '1ª Aula (12:40 - 13:30)', inicio: '12:40', fim1: '13:30', fim2: '14:40', canDouble: true },
        { id: 'FT2', label: '2ª Aula (13:30 - 14:40)', inicio: '13:30', fim1: '14:40', fim2: '15:10', canDouble: true },
        { id: 'FT3', label: '3ª Aula (14:40 - 15:10)', inicio: '14:40', fim1: '15:10', fim2: '', canDouble: false }, // Intervalo a seguir (15:10 - 15:30)
        { id: 'FT4', label: '4ª Aula (15:30 - 16:20)', inicio: '15:30', fim1: '16:20', fim2: '17:10', canDouble: true },
        { id: 'FT5', label: '5ª Aula (16:20 - 17:10)', inicio: '16:20', fim1: '17:10', fim2: '18:00', canDouble: true },
        { id: 'FT6', label: '6ª Aula (17:10 - 18:00)', inicio: '17:10', fim1: '18:00', fim2: '', canDouble: false }
    ];

    // Grade do Ensino Médio (Manhã) - Aulas de 50 minutos e intervalo 10:20-10:40
    const slotsMedio = [
        { id: 'M1', label: '1ª Aula (07:00 - 07:50)', inicio: '07:00', fim1: '07:50', fim2: '08:40', canDouble: true },
        { id: 'M2', label: '2ª Aula (07:50 - 08:40)', inicio: '07:50', fim1: '08:40', fim2: '09:30', canDouble: true },
        { id: 'M3', label: '3ª Aula (08:40 - 09:30)', inicio: '08:40', fim1: '09:30', fim2: '10:20', canDouble: true },
        { id: 'M4', label: '4ª Aula (09:30 - 10:20)', inicio: '09:30', fim1: '10:20', fim2: '', canDouble: false }, // Intervalo a seguir (10:20 - 10:40)
        { id: 'M5', label: '5ª Aula (10:40 - 11:30)', inicio: '10:40', fim1: '11:30', fim2: '12:20', canDouble: true },
        { id: 'M6', label: '6ª Aula (11:30 - 12:20)', inicio: '11:30', fim1: '12:20', fim2: '', canDouble: false }
    ];

    // Função utilitária para resolver os slots baseados no segmento e na turma
    function obterSlots(segmento, turma) {
        if (segmento === 'fundamental') {
            const turmaInfo = turmasCarregadas.find(t => t.nome === turma);
            const isManha = turmaInfo ? (turmaInfo.periodo === 'manha') : true;
            return isManha ? slotsFundamentalManha : slotsFundamentalTarde;
        } else if (segmento === 'medio') {
            return slotsMedio;
        }
        return [];
    }

    // ── Injeta o modal de sucesso no DOM ─────────────────────────────────
    const modalHTML = `
    <div id="modal-sucesso" style="
        display:none; position:fixed; inset:0; z-index:9999;
        background:rgba(0,0,0,0.75); backdrop-filter:blur(6px);
        align-items:center; justify-content:center;">
      <div style="
          background:#0f0f0f; border:2px solid #00e5ff;
          border-radius:12px; padding:2.5rem 2rem; max-width:420px; width:90%;
          text-align:center; box-shadow:0 0 40px rgba(0,229,255,0.25);
          animation:modalIn .35s cubic-bezier(.22,1,.36,1) both;">
        <div style="font-size:3rem; margin-bottom:0.5rem;">✅</div>
        <h2 style="color:#00e5ff; font-family:'Rajdhani',sans-serif;
            font-size:1.6rem; letter-spacing:.1em; margin:0 0 .5rem;">
            RESERVA CONFIRMADA!
        </h2>
        <p id="modal-detalhe" style="color:#ccc; font-size:.9rem; margin:0 0 1.5rem;"></p>
        <button id="modal-fechar" style="
            background:#00e5ff; color:#000; border:none; border-radius:6px;
            padding:.65rem 2rem; font-family:'Rajdhani',sans-serif;
            font-size:1rem; font-weight:700; letter-spacing:.08em;
            cursor:pointer; transition:opacity .2s;">
            FECHAR
        </button>
      </div>
    </div>
    <style>
      @keyframes modalIn {
        from { opacity:0; transform:scale(.85); }
        to   { opacity:1; transform:scale(1); }
      }
    </style>`;
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    const modal = document.getElementById('modal-sucesso');
    const modalDetalhe = document.getElementById('modal-detalhe');
    document.getElementById('modal-fechar').addEventListener('click', () => {
        modal.style.display = 'none';
        window.location.href = 'agendamentos_prof.php'; // Redireciona para listagem após confirmação
    });

    // ── Lógica Dinâmica dos Filtros ──────────────────────────────────────

    segmentoSelect.addEventListener('change', async () => {
        const segmento = segmentoSelect.value;
        
        // Limpa e desabilita selects filhos
        anoTurmaSelect.innerHTML = '<option value="">SELECIONE...</option>';
        disciplinaSelect.innerHTML = '<option value="">SELECIONE A TURMA...</option>';
        aulaInicioSelect.innerHTML = '<option value="">SELECIONE A TURMA...</option>';
        
        anoTurmaSelect.disabled = true;
        disciplinaSelect.disabled = true;
        aulaInicioSelect.disabled = true;
        duracaoSelect.disabled = true;
        
        hInicioInput.value = '';
        hFimInput.value = '';
        turmasCarregadas = [];

        if (!segmento) return;

        try {
            const response = await fetch(`obter_dados_agendamento.php?segmento=${segmento}`);
            const data = await response.json();
            if (data.sucesso && data.turmas) {
                turmasCarregadas = data.turmas;
                anoTurmaSelect.disabled = false;
                turmasCarregadas.forEach(t => {
                    const labelPeriodo = t.periodo === 'manha' ? 'Manhã' : 'Tarde';
                    anoTurmaSelect.innerHTML += `<option value="${t.nome}">${t.nome} (${labelPeriodo})</option>`;
                });
            } else {
                console.error('Erro ao carregar turmas:', data.erro);
            }
        } catch (err) {
            console.error('Erro de rede ao carregar turmas:', err);
        }
    });

    anoTurmaSelect.addEventListener('change', () => {
        const segmento = segmentoSelect.value;
        const turma = anoTurmaSelect.value;

        disciplinaSelect.innerHTML = '<option value="">SELECIONE...</option>';
        aulaInicioSelect.innerHTML = '<option value="">SELECIONE...</option>';
        
        disciplinaSelect.disabled = true;
        aulaInicioSelect.disabled = true;
        duracaoSelect.disabled = true;

        hInicioInput.value = '';
        hFimInput.value = '';

        if (!turma) return;

        const turmaInfo = turmasCarregadas.find(t => t.nome === turma);
        if (!turmaInfo) return;

        disciplinaSelect.disabled = false;
        aulaInicioSelect.disabled = false;

        // 1. Carrega as disciplinas com base no relacionamento da turma no banco
        if (turmaInfo.materias && turmaInfo.materias.length > 0) {
            turmaInfo.materias.forEach(d => {
                disciplinaSelect.innerHTML += `<option value="${d}">${d}</option>`;
            });
        }

        // 2. Carrega os slots de aula
        const slots = obterSlots(segmento, turma);
        slots.forEach(s => {
            aulaInicioSelect.innerHTML += `<option value="${s.id}">${s.label}</option>`;
        });
    });

    aulaInicioSelect.addEventListener('change', () => {
        const segmento = segmentoSelect.value;
        const turma = anoTurmaSelect.value;
        const slotId = aulaInicioSelect.value;

        duracaoSelect.innerHTML = '';
        duracaoSelect.disabled = true;
        hInicioInput.value = '';
        hFimInput.value = '';

        if (!slotId) return;

        const slots = obterSlots(segmento, turma);
        const slot = slots.find(s => s.id === slotId);

        if (slot) {
            duracaoSelect.disabled = false;
            duracaoSelect.innerHTML += '<option value="1">1 Aula (50 minutos)</option>';
            
            if (slot.canDouble) {
                duracaoSelect.innerHTML += '<option value="2">2 Aulas (até 1h 40m)</option>';
            }
            
            calcularHorarios();
        }
    });

    duracaoSelect.addEventListener('change', calcularHorarios);
    dataInput.addEventListener('change', verificarDisponibilidadeEquipamentos);

    function calcularHorarios() {
        const segmento = segmentoSelect.value;
        const turma = anoTurmaSelect.value;
        const slotId = aulaInicioSelect.value;
        const duracao = duracaoSelect.value;

        if (!slotId) return;

        const slots = obterSlots(segmento, turma);
        const slot = slots.find(s => s.id === slotId);

        if (slot) {
            hInicioInput.value = slot.inicio;
            hFimInput.value = (duracao === '2' && slot.canDouble) ? slot.fim2 : slot.fim1;

            // Uma vez que os horários ocultos foram atualizados, re-verifica disponibilidade real
            verificarDisponibilidadeEquipamentos();
        }
    }

    // ── Lógica Dinâmica de Verificação de Estoque via API ────────────────

    async function verificarDisponibilidadeEquipamentos() {
        const data = dataInput.value;
        const inicio = hInicioInput.value;
        const fim = hFimInput.value;

        if (!data || !inicio || !fim) return;

        try {
            const url = `verifica_disponibilidade.php?data=${data}&inicio=${inicio}&fim=${fim}`;
            const response = await fetch(url);
            const res = await response.json();

            if (res.sucesso && res.estoque) {
                const equipCards = document.querySelectorAll('.equip-card');
                equipCards.forEach(card => {
                    const id = card.getAttribute('data-id');
                    const input = card.querySelector('.equip-input');
                    const badge = card.querySelector('.limit-badge');
                    
                    if (input && id in res.estoque) {
                        const estoqueDisponivel = res.estoque[id];
                        
                        input.max = estoqueDisponivel;
                        badge.textContent = `DISP: ${estoqueDisponivel}`;
                        badge.style.background = estoqueDisponivel === 0 ? 'var(--accent-red)' : '#00FF66';
                        
                        // Se o valor digitado atualmente for maior que o disponível, ajusta automaticamente
                        if (parseInt(input.value) > estoqueDisponivel) {
                            input.value = estoqueDisponivel;
                        }
                    }
                });
            }
        } catch (err) {
            console.error('Erro ao verificar estoque disponível:', err);
        }
    }

    // ── Submissão e Validações Gerais do Formulário ───────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const feedback = document.getElementById('booking-feedback');
        feedback.className = 'feedback-msg hidden';

        // 1. Validação de Fim de Semana no cliente
        const dataSelecionada = dataInput.value;
        if (dataSelecionada) {
            const dateObj = new Date(dataSelecionada + 'T12:00:00');
            const diaSemana = dateObj.getDay(); // 0 = Domingo, 6 = Sábado
            if (diaSemana === 0 || diaSemana === 6) {
                showFeedback('ERRO: Agendamentos não são permitidos aos finais de semana (Sábado e Domingo).', 'error');
                return;
            }
        }

        // 2. Validação de Horário do Passado (comparação no timezone local do cliente)
        const dataReservaStr = dataInput.value;
        const horaInicioStr = hInicioInput.value;
        
        if (dataReservaStr && horaInicioStr) {
            const agora = new Date();
            const [ano, mes, dia] = dataReservaStr.split('-');
            const [hora, min] = horaInicioStr.split(':');
            const dataHoraReserva = new Date(ano, mes - 1, dia, hora, min, 0);

            if (agora > dataHoraReserva) {
                showFeedback('ERRO: Horários já passados não estão disponíveis.', 'error');
                return;
            }
        }

        // 3. Validação Dinâmica de Quantidade e Seleção
        const equipInputs = document.querySelectorAll('.equip-input');
        let totalEquipamentos = 0;
        let erroEstoque = false;

        equipInputs.forEach(input => {
            const qtd = parseInt(input.value) || 0;
            const max = parseInt(input.max) || 0;
            const nome = input.parentElement.querySelector('h3').textContent;

            if (qtd > max) {
                showFeedback(`ERRO: O limite de ${nome} para este horário é ${max}.`, 'error');
                erroEstoque = true;
            }
            totalEquipamentos += qtd;
        });

        if (erroEstoque) return;

        if (totalEquipamentos === 0) {
            showFeedback('ERRO: Você precisa selecionar pelo menos um equipamento para reservar.', 'error');
            return;
        }

        // Monta o envio do formulário
        const formData = new FormData(form);
        const btn = form.querySelector('.btn-submit-booking');

        try {
            btn.disabled = true;
            btn.textContent = 'PROCESSANDO...';

            const response = await fetch('processa_reserva.php', { method: 'POST', body: formData });
            const rawText = await response.text();
            let result;

            try {
                result = JSON.parse(rawText);
            } catch {
                showFeedback('ERRO INTERNO: Resposta inválida do servidor. Contate o administrador.', 'error');
                console.error('Resposta corrompida do servidor:', rawText);
                return;
            }

            if (result.sucesso) {
                form.reset();
                
                // Reseta os estados dos selects para padrão
                anoTurmaSelect.disabled = true;
                disciplinaSelect.disabled = true;
                aulaInicioSelect.disabled = true;
                duracaoSelect.disabled = true;
                
                const aviso = document.getElementById('aviso-fds');
                if (aviso) aviso.style.display = 'none';
                dataInput.style.borderColor = '';

                // Formata dados de confirmação no modal
                const dataFormatada = dataSelecionada
                    ? new Date(dataSelecionada + 'T12:00:00').toLocaleDateString('pt-BR', { weekday:'long', day:'2-digit', month:'long', year:'numeric' })
                    : '';
                
                modalDetalhe.innerHTML = `
                    <strong>Série:</strong> ${anoTurmaSelect.value || ''}<br>
                    <strong>Disciplina:</strong> ${disciplinaSelect.value || ''}<br>
                    <strong>Data:</strong> ${dataFormatada}<br>
                    <strong>Período:</strong> ${horaInicioStr} às ${hFimInput.value}
                `;
                
                modal.style.display = 'flex';
            } else {
                showFeedback(`FALHA: ${result.erro}`, 'error');
            }
        } catch (networkError) {
            showFeedback('ERRO DE REDE: Verifique sua conexão e tente novamente.', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'CONFIRMAR AGENDAMENTO';
        }
    });

    function showFeedback(msg, type) {
        const feedback = document.getElementById('booking-feedback');
        feedback.textContent = msg;
        feedback.className = `feedback-msg ${type}`;
    }
});
