document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formAgendamento');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const feedback = document.getElementById('booking-feedback');
        feedback.className = 'feedback-msg hidden';

        // Pega valores
        const pcs = parseInt(document.getElementById('qtd_computadores').value) || 0;
        const tabs = parseInt(document.getElementById('qtd_tablets').value) || 0;
        const cels = parseInt(document.getElementById('qtd_celulares').value) || 0;

        // Validação Frontend de Quantidade Máxima
        if (pcs > 35) {
            showFeedback('ERRO: O limite de Computadores no estoque é 35.', 'error');
            return;
        }
        if (tabs > 24) {
            showFeedback('ERRO: O limite de Tablets no estoque é 24.', 'error');
            return;
        }
        if (cels > 12) {
            showFeedback('ERRO: O limite de Celulares no estoque é 12.', 'error');
            return;
        }
        if (pcs === 0 && tabs === 0 && cels === 0) {
            showFeedback('ERRO: Você precisa selecionar pelo menos um equipamento para reservar.', 'error');
            return;
        }

        // Preparar requisição Assíncrona
        const formData = new FormData(form);
        const btn = form.querySelector('.btn-submit-booking');

        try {
            // Desabilitar botão para evitar duplo clique
            btn.disabled = true;
            btn.textContent = "PROCESSANDO...";

            const response = await fetch('processa_reserva.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.sucesso) {
                showFeedback('AGENDAMENTO REALIZADO COM SUCESSO!', 'success');
                form.reset(); // Limpa o form
            } else {
                showFeedback(`FALHA: ${result.erro}`, 'error');
            }
        } catch (error) {
            showFeedback('ERRO CRÍTICO: Falha de conexão com o servidor.', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = "CONFIRMAR AGENDAMENTO";
        }
    });

    function showFeedback(msg, type) {
        const feedback = document.getElementById('booking-feedback');
        feedback.textContent = msg;
        feedback.className = `feedback-msg ${type}`;
    }
});
