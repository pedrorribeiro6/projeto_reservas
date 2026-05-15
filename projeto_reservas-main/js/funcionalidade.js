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

    const inicioInput = document.getElementById('horario_inicio');
    const fimInput = document.getElementById('horario_fim');
    const pcsInput = document.getElementById('qtd_computadores');
    const tabsInput = document.getElementById('qtd_tablets');
    const celsInput = document.getElementById('qtd_celulares');
    
    // As Tags HTML que exibem o "MÁX: X"
    const badgePcs = pcsInput?.previousElementSibling;
    const badgeTabs = tabsInput?.previousElementSibling;
    const badgeCels = celsInput?.previousElementSibling;

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
        if (!inicioInput.value || !fimInput.value) return true;
        
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
        if (!dataInput.value || !inicioInput.value || !fimInput.value) {
            setLimits(35, 24, 12); // Padrões se os campos estiverem vazios
            return;
        }

        if (!checkHorarios()) return;

        try {
            const res = await fetch(`verifica_disponibilidade.php?data=${dataInput.value}&inicio=${inicioInput.value}&fim=${fimInput.value}`);
            const data = await res.json();

            if (data.sucesso) {
                setLimits(data.computadores, data.tablets, data.celulares);
            }
        } catch (e) {
            console.error("Erro no servidor ao buscar limite de estoque:", e);
        }
    }

    function setLimits(maxPcs, maxTabs, maxCels) {
        if (pcsInput) {
            pcsInput.max = maxPcs;
            badgePcs.textContent = `RESTAM: ${maxPcs}`;
            if (parseInt(pcsInput.value) > maxPcs) pcsInput.value = maxPcs; // Força redução automática
            pcsInput.parentElement.style.opacity = maxPcs === 0 ? '0.5' : '1';
        }
        if (tabsInput) {
            tabsInput.max = maxTabs;
            badgeTabs.textContent = `RESTAM: ${maxTabs}`;
            if (parseInt(tabsInput.value) > maxTabs) tabsInput.value = maxTabs;
            tabsInput.parentElement.style.opacity = maxTabs === 0 ? '0.5' : '1';
        }
        if (celsInput) {
            celsInput.max = maxCels;
            badgeCels.textContent = `RESTAM: ${maxCels}`;
            if (parseInt(celsInput.value) > maxCels) celsInput.value = maxCels;
            celsInput.parentElement.style.opacity = maxCels === 0 ? '0.5' : '1';
        }
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
    <!-- Modal de Confirmação -->
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

    <!-- Modal de Sucesso -->
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

        document.getElementById('modal-confirm-cancelar').addEventListener('click', () => {
            document.getElementById('modal-confirm-excluir').style.display = 'none';
        });

        document.getElementById('modal-sucesso-fechar').addEventListener('click', () => {
            document.getElementById('modal-sucesso-excluir').style.display = 'none';
            window.location.reload();
        });
    });

    // ── Função global chamada pelos botões do PHP ─────────────────────────
    window.confirmarExclusao = function (id) {
        const modalConfirm  = document.getElementById('modal-confirm-excluir');
        const modalSucesso  = document.getElementById('modal-sucesso-excluir');
        const btnOk         = document.getElementById('modal-confirm-ok');

        // Exibe o modal de confirmação
        modalConfirm.style.display = 'flex';

        // Remove listener anterior para evitar múltiplos disparos
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

                const rawText = await res.text();
                let data;
                try {
                    data = JSON.parse(rawText);
                } catch {
                    alert('ERRO INTERNO: Resposta inválida do servidor. Contacte o suporte.');
                    console.error('Resposta bruta:', rawText);
                    return;
                }

                if (data.sucesso) {
                    modalSucesso.style.display = 'flex';
                } else {
                    alert('Falha ao excluir: ' + data.erro);
                }
            } catch (e) {
                alert('ERRO DE REDE: Verifique sua conexão e tente novamente.');
            } finally {
                novoBtn.disabled = false;
            }
        });
    };
})();

