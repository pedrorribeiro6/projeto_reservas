document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formAgendamento');
    if (!form) return;

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
    });

    // ── Envio do formulário ───────────────────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const feedback = document.getElementById('booking-feedback');
        feedback.className = 'feedback-msg hidden';

        // Validação de Fim de Semana
        const dataInput = document.getElementById('data_reserva').value;
        if (dataInput) {
            const dataSelecionada = new Date(dataInput + 'T12:00:00');
            const diaSemana = dataSelecionada.getDay(); // 0 = Domingo, 6 = Sábado
            if (diaSemana === 0 || diaSemana === 6) {
                showFeedback('ERRO: Agendamentos não são permitidos aos finais de semana (Sábado e Domingo).', 'error');
                return;
            }
        }

        // Validação Dinâmica de Quantidade
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

        // Captura valores antes do reset para exibir no modal de sucesso
        const horaInicio = document.getElementById('horario_inicio').value;
        const horaFim    = document.getElementById('horario_fim').value;

        const formData = new FormData(form);
        const btn = form.querySelector('.btn-submit-booking');

        try {
            btn.disabled = true;
            btn.textContent = 'PROCESSANDO...';

            const response = await fetch('processa_reserva.php', { method: 'POST', body: formData });

            // Lê o texto bruto primeiro para detectar respostas corrompidas
            const rawText = await response.text();
            let result;
            try {
                result = JSON.parse(rawText);
            } catch {
                // Se o JSON veio corrompido mas o status HTTP é 200, algo quebrou no back-end
                showFeedback('ERRO INTERNO: Resposta inválida do servidor. Contacte o suporte.', 'error');
                console.error('Resposta bruta do servidor:', rawText);
                return;
            }

            if (result.sucesso) {
                form.reset();
                // Reseta aviso de fds
                const aviso = document.getElementById('aviso-fds');
                if (aviso) aviso.style.display = 'none';
                document.getElementById('data_reserva').style.borderColor = '';

                // Exibe modal com detalhe
                const dataFormatada = dataInput
                    ? new Date(dataInput + 'T12:00:00').toLocaleDateString('pt-BR', { weekday:'long', day:'2-digit', month:'long', year:'numeric' })
                    : '';
                modalDetalhe.textContent = dataFormatada
                    ? `${dataFormatada} · ${horaInicio} – ${horaFim}`
                    : 'Agendamento registrado com sucesso.';
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
