document.addEventListener('DOMContentLoaded', () => {
    // 1. Bloqueio de Data Passada
    const dataInput = document.getElementById('data_reserva');
    if (dataInput) {
        const hoje = new Date();
        const yyyy = hoje.getFullYear();
        const mm = String(hoje.getMonth() + 1).padStart(2, '0');
        const dd = String(hoje.getDate()).padStart(2, '0');
        dataInput.setAttribute('min', `${yyyy}-${mm}-${dd}`);
    }

    const equipInputs = document.querySelectorAll('.equip-input');

    // Utilidade para converter '07:30' em número 730 para checagem matemática
    function formatTime(timeStr) {
        return parseInt(timeStr.replace(':', ''), 10);
    }

    // 2. Regras de Horário e Intervalo
    function isHorarioPermitido(horaString) {
        if (!horaString) return false;
        const h = formatTime(horaString);
        // Turno 1: 07:00 a 12:20 | Turno 2: 12:40 a 18:00
        return (h >= 700 && h <= 1220) || (h >= 1240 && h <= 1800);
    }

    function checkHorarios() {
        if (!inicioInput?.value || !fimInput?.value) return true;
        
        const ini = formatTime(inicioInput.value);
        const fim = formatTime(fimInput.value);

        if (ini >= fim) {
            alert("Atenção: O horário de término deve ser posterior ao horário de início.");
            fimInput.value = '';
            return false;
        }

        if (!isHorarioPermitido(inicioInput.value) || !isHorarioPermitido(fimInput.value)) {
            alert("Atenção: Horários permitidos são das 07:00 às 12:20, ou 12:40 às 18:00.");
            inicioInput.value = '';
            fimInput.value = '';
            return false;
        }

        // Bloquear invasão da janela de intervalo (12:20 até 12:40)
        if (ini < 1240 && fim > 1220) {
            alert("Atenção: A reserva não pode atravessar o intervalo escolar (12:20 às 12:40).");
            inicioInput.value = '';
            fimInput.value = '';
            return false;
        }

        return true;
    }

    // 3. Checagem Assíncrona de Estoque Real
    async function updateDisponibilidade() {
        if (!dataInput?.value || !inicioInput?.value || !fimInput?.value) {
            resetLimits();
            return;
        }

        if (!checkHorarios()) return;

        try {
            const res = await fetch(`verifica_disponibilidade.php?data=${dataInput.value}&inicio=${inicioInput.value}&fim=${fimInput.value}`);
            const data = await res.json();

            if (data.sucesso) {
                setLimits(data.estoque);
            }
        } catch (e) {
            console.error("Erro no servidor ao buscar limite de estoque:", e);
        }
    }

    function setLimits(estoqueReal) {
        equipInputs.forEach(input => {
            const id = input.id.replace('equip_', '');
            const maxReal = estoqueReal[id] !== undefined ? estoqueReal[id] : parseInt(input.getAttribute('data-max-original'));
            
            input.max = maxReal;
            const badge = input.previousElementSibling;
            if (badge && badge.classList.contains('limit-badge')) {
                badge.textContent = `RESTAM: ${maxReal}`;
            }
            
            if (parseInt(input.value) > maxReal) input.value = maxReal;
            input.parentElement.style.opacity = maxReal === 0 ? '0.5' : '1';
        });
    }

    function resetLimits() {
        equipInputs.forEach(input => {
            const maxOriginal = input.getAttribute('data-max-original');
            input.max = maxOriginal;
            const badge = input.previousElementSibling;
            if (badge && badge.classList.contains('limit-badge')) {
                badge.textContent = `MÁX: ${maxOriginal}`;
            }
            input.parentElement.style.opacity = '1';
        });
    }

    // Monitores de input
    if (dataInput) dataInput.addEventListener('change', updateDisponibilidade);
    if (inicioInput) inicioInput.addEventListener('change', updateDisponibilidade);
    if (fimInput) fimInput.addEventListener('change', updateDisponibilidade);
});

// 4. Exclusão de Reserva Global — Modal Premium
(function () {
    // ── Injeta os modais no DOM quando a página carregar ──────────────────
    const html = `
    <!-- Modal de Confirmação para DISPOSITIVOS (Vermelho) -->
    <div id="modal-confirm-disp" style="
        display:none; position:fixed; inset:0; z-index:10010;
        background:rgba(0,0,0,0.85); backdrop-filter:blur(8px);
        align-items:center; justify-content:center;">
      <div style="
          background:#0f0f0f; border:2px solid #FA1E4E;
          border-radius:12px; padding:2.5rem 2rem; max-width:440px; width:90%;
          text-align:center; box-shadow:0 0 40px rgba(250,30,78,0.25);
          animation:modalInDel .3s both;">
        <div style="font-size:2.5rem; margin-bottom:.5rem;">⚠️</div>
        <h2 style="color:#FA1E4E; font-family:'Rajdhani',sans-serif;
            font-size:1.6rem; letter-spacing:.08em; margin:0 0 .6rem; text-transform:uppercase;">
            EXCLUIR DISPOSITIVO?
        </h2>
        <p style="color:#aaa; font-size:.9rem; margin:0 0 1.6rem; line-height:1.5;">
            Isso impedirá que novos agendamentos sejam feitos para este item.<br>
            <strong style="color:#fff;">Essa ação não pode ser desfeita.</strong>
        </p>
        <div style="display:flex; gap:.8rem; justify-content:center;">
          <button id="modal-confirm-disp-cancelar" style="
              background:transparent; color:#aaa; border:1px solid #444;
              border-radius:6px; padding:.7rem 1.8rem; font-family:'Rajdhani',sans-serif;
              font-size:1rem; font-weight:700; cursor:pointer;">
              CANCELAR
          </button>
          <button id="modal-confirm-disp-ok" style="
              background:#FA1E4E; color:#fff; border:none;
              border-radius:6px; padding:.7rem 1.8rem; font-family:'Rajdhani',sans-serif;
              font-size:1rem; font-weight:700; cursor:pointer;">
              SIM, EXCLUIR
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Sucesso para DISPOSITIVOS (Ciano) -->
    <div id="modal-sucesso-disp" style="
        display:none; position:fixed; inset:0; z-index:10011;
        background:rgba(0,0,0,0.85); backdrop-filter:blur(8px);
        align-items:center; justify-content:center;">
      <div style="
          background:#0f0f0f; border:2px solid #00F0FF;
          border-radius:12px; padding:2.5rem 2rem; max-width:400px; width:90%;
          text-align:center; box-shadow:0 0 40px rgba(0,240,255,0.20);
          animation:modalInDel .35s both;">
        <div style="font-size:3rem; margin-bottom:.5rem;">🗑️</div>
        <h2 style="color:#00F0FF; font-family:'Rajdhani',sans-serif;
            font-size:1.6rem; letter-spacing:.1em; margin:0 0 .5rem; text-transform:uppercase;">
            DISPOSITIVO REMOVIDO!
        </h2>
        <p style="color:#ccc; font-size:.9rem; margin:0 0 1.8rem; line-height:1.4;">
            O equipamento foi excluído com sucesso do inventário da escola.
        </p>
        <button id="modal-sucesso-disp-fechar" style="
            background:#00F0FF; color:#000; border:none; border-radius:6px;
            padding:.7rem 2.5rem; font-family:'Rajdhani',sans-serif;
            font-size:1rem; font-weight:700; letter-spacing:.08em; cursor:pointer;">
            FECHAR
        </button>
      </div>
    </div>

    <!-- Modal de Falha/Erro para DISPOSITIVOS (Vermelho) -->
    <div id="modal-erro-disp" style="
        display:none; position:fixed; inset:0; z-index:10012;
        background:rgba(0,0,0,0.85); backdrop-filter:blur(8px);
        align-items:center; justify-content:center;">
      <div style="
          background:#0f0f0f; border:2px solid #FA1E4E;
          border-radius:12px; padding:2.5rem 2rem; max-width:400px; width:90%;
          text-align:center; box-shadow:0 0 40px rgba(250,30,78,0.25);
          animation:modalInDel .3s both;">
        <div style="font-size:2.5rem; margin-bottom:.5rem;">🚫</div>
        <h2 style="color:#FA1E4E; font-family:'Rajdhani',sans-serif;
            font-size:1.5rem; letter-spacing:.08em; margin:0 0 .6rem; text-transform:uppercase;">
            FALHA NA EXCLUSÃO
        </h2>
        <p id="modal-erro-disp-msg" style="color:#aaa; font-size:.9rem; margin:0 0 1.6rem; line-height:1.5;"></p>
        <button id="modal-erro-disp-fechar" style="
            background:#FA1E4E; color:#fff; border:none;
            border-radius:6px; padding:.6rem 2.5rem; font-family:'Rajdhani',sans-serif;
            font-size:1rem; font-weight:700; cursor:pointer;">
            ENTENDI
        </button>
      </div>
    </div>

    <!-- Modais Legados de Reserva -->
    <div id="modal-confirm-excluir" style="
        display:none; position:fixed; inset:0; z-index:9998;
        background:rgba(0,0,0,0.80); backdrop-filter:blur(6px);
        align-items:center; justify-content:center;">
      <div style="
          background:#0f0f0f; border:2px solid #e5003a;
          border-radius:12px; padding:2.5rem 2rem; max-width:420px; width:90%;
          text-align:center; box-shadow:0 0 40px rgba(229,0,58,0.30);
          animation:modalInDel .3s cubic-bezier(.22,1,.36,1) both;">
        <div style="font-size:2.5rem; margin-bottom:.5rem;">⚠️</div>
        <h2 style="color:#e5003a; font-family:'Rajdhani',sans-serif;
            font-size:1.5rem; letter-spacing:.08em; margin:0 0 .6rem;">
            EXCLUIR RESERVA?
        </h2>
        <p style="color:#aaa; font-size:.9rem; margin:0 0 1.6rem; line-height:1.5;">
            Os equipamentos serão devolvidos ao estoque imediatamente.<br>
            <strong style="color:#fff;">Essa ação não pode ser desfeita.</strong>
        </p>
        <div style="display:flex; gap:.8rem; justify-content:center;">
          <button id="modal-confirm-cancelar" style="
              background:transparent; color:#aaa; border:1px solid #444;
              border-radius:6px; padding:.6rem 1.6rem; font-family:'Rajdhani',sans-serif;
              font-size:1rem; font-weight:700; cursor:pointer; letter-spacing:.06em;">
              CANCELAR
          </button>
          <button id="modal-confirm-ok" style="
              background:#e5003a; color:#fff; border:none;
              border-radius:6px; padding:.6rem 1.6rem; font-family:'Rajdhani',sans-serif;
              font-size:1rem; font-weight:700; cursor:pointer; letter-spacing:.06em;">
              SIM, EXCLUIR
          </button>
        </div>
      </div>
    </div>

    <div id="modal-sucesso-excluir" style="
        display:none; position:fixed; inset:0; z-index:9999;
        background:rgba(0,0,0,0.80); backdrop-filter:blur(6px);
        align-items:center; justify-content:center;">
      <div style="
          background:#0f0f0f; border:2px solid #00e5ff;
          border-radius:12px; padding:2.5rem 2rem; max-width:380px; width:90%;
          text-align:center; box-shadow:0 0 40px rgba(0,229,255,0.25);
          animation:modalInDel .35s cubic-bezier(.22,1,.36,1) both;">
        <div style="font-size:3rem; margin-bottom:.5rem;">🗑️</div>
        <h2 style="color:#00e5ff; font-family:'Rajdhani',sans-serif;
            font-size:1.5rem; letter-spacing:.1em; margin:0 0 .5rem;">
            RESERVA EXCLUÍDA!
        </h2>
        <p style="color:#ccc; font-size:.9rem; margin:0 0 1.5rem;">
            O agendamento foi removido e os equipamentos devolvidos ao estoque.
        </p>
        <button id="modal-sucesso-fechar" style="
            background:#00e5ff; color:#000; border:none; border-radius:6px;
            padding:.65rem 2rem; font-family:'Rajdhani',sans-serif;
            font-size:1rem; font-weight:700; letter-spacing:.08em; cursor:pointer;">
            FECHAR
        </button>
      </div>
    </div>

    <style>
      @keyframes modalInDel {
        from { opacity:0; transform:scale(.85); }
        to   { opacity:1; transform:scale(1); }
      }
    </style>`;

    document.addEventListener('DOMContentLoaded', () => {
        document.body.insertAdjacentHTML('beforeend', html);

        // Listeners Modais Reservas
        document.getElementById('modal-confirm-cancelar').addEventListener('click', () => {
            document.getElementById('modal-confirm-excluir').style.display = 'none';
        });

        document.getElementById('modal-sucesso-fechar').addEventListener('click', () => {
            document.getElementById('modal-sucesso-excluir').style.display = 'none';
            window.location.reload();
        });

        // Listeners Modais Dispositivos
        document.getElementById('modal-confirm-disp-cancelar').addEventListener('click', () => {
            document.getElementById('modal-confirm-disp').style.display = 'none';
        });

        document.getElementById('modal-sucesso-disp-fechar').addEventListener('click', () => {
            document.getElementById('modal-sucesso-disp').style.display = 'none';
            window.location.reload();
        });

        document.getElementById('modal-erro-disp-fechar').addEventListener('click', () => {
            document.getElementById('modal-erro-disp').style.display = 'none';
        });
    });

    // ── FUNÇÕES GLOBAIS DE UTILIDADE ─────────────────────────────────────
    
    window.confirmarExclusaoDispositivo = function (id) {
        const modalConfirm  = document.getElementById('modal-confirm-disp');
        const modalSucesso  = document.getElementById('modal-sucesso-disp');
        const modalErro     = document.getElementById('modal-erro-disp');
        const btnOk         = document.getElementById('modal-confirm-disp-ok');

        modalConfirm.style.display = 'flex';

        const novoBtn = btnOk.cloneNode(true);
        btnOk.parentNode.replaceChild(novoBtn, btnOk);

        novoBtn.addEventListener('click', async () => {
            modalConfirm.style.display = 'none';
            novoBtn.disabled = true;

            try {
                const res = await fetch('processa_dispositivo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `acao=excluir&id=${id}`
                });

                const data = await res.json();
                if (data.sucesso) {
                    modalSucesso.style.display = 'flex';
                } else {
                    document.getElementById('modal-erro-disp-msg').textContent = data.erro;
                    modalErro.style.display = 'flex';
                }
            } catch (e) {
                document.getElementById('modal-erro-disp-msg').textContent = 'Erro de conexão com o servidor.';
                modalErro.style.display = 'flex';
            } finally {
                novoBtn.disabled = false;
            }
        });
    };

    window.confirmarExclusao = function (id) {
        const modalConfirm  = document.getElementById('modal-confirm-excluir');
        const modalSucesso  = document.getElementById('modal-sucesso-excluir');
        const btnOk         = document.getElementById('modal-confirm-ok');

        modalConfirm.style.display = 'flex';

        const novoBtn = btnOk.cloneNode(true);
        btnOk.parentNode.replaceChild(novoBtn, btnOk);

        novoBtn.addEventListener('click', async () => {
            modalConfirm.style.display = 'none';
            novoBtn.disabled = true;

            try {
                const res = await fetch('excluir_reserva.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                });

                const data = await res.json();
                if (data.sucesso) {
                    modalSucesso.style.display = 'flex';
                } else {
                    window.exibirAlerta('FALHA NA EXCLUSÃO', data.erro);
                }
            } catch (e) {
                window.exibirAlerta('ERRO DE CONEXÃO', 'Não foi possível contatar o servidor.');
            } finally {
                novoBtn.disabled = false;
            }
        });
    };
})();

