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

// 4. Exclusão de Reserva Global
window.excluirReserva = async function(id) {
    if (confirm("Você tem certeza que deseja EXCLUIR essa reserva? Os equipamentos serão devolvidos ao estoque imediatamente.")) {
        try {
            const res = await fetch('excluir_reserva.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}`
            });
            const data = await res.json();
            if (data.sucesso) {
                alert("Reserva cancelada e removida com sucesso!");
                window.location.reload(); // Recarrega para ver a lista atualizada
            } else {
                alert("Falha ao excluir: " + data.erro);
            }
        } catch (e) {
            alert("Erro crítico de conexão ao tentar excluir a reserva.");
        }
    }
};
