document.addEventListener('DOMContentLoaded', () => {
    // Frequência de verificação em background (60 segundos)
    const INTERVALO_VERIFICACAO = 60000;

    function verificarReservasExpiradas() {
        fetch('../php/auto_cleanup_endpoint.php')
            .then(response => response.json())
            .then(data => {
                if (data.sucesso) {
                    // Quando a limpeza ocorre com sucesso no banco de dados,
                    // fazemos a limpeza visual dos cards expirados na tela em tempo real
                    const agora = new Date();
                    const cards = document.querySelectorAll('.booking-card');

                    cards.forEach(card => {
                        const dateSpan = card.querySelector('.booking-date');
                        const timeSpan = card.querySelector('.booking-time');

                        if (dateSpan && timeSpan) {
                            // Extrai a data no formato dd/mm/aaaa
                            const [dia, mes, ano] = dateSpan.textContent.trim().split('/');
                            
                            // Extrai os horários (ex: "07:00 - 07:50")
                            const tempos = timeSpan.textContent.trim().split('-');
                            if (tempos.length === 2) {
                                const fimTempo = tempos[1].trim(); // "07:50"
                                const [hora, minuto] = fimTempo.split(':');

                                // Cria o objeto Date para o encerramento da reserva
                                // Nota: Mês no JavaScript é indexado em 0 (Janeiro = 0, Maio = 4)
                                const dataFim = new Date(ano, mes - 1, dia, hora, minuto, 0);

                                // Se o horário atual já passou do horário de término do agendamento
                                if (agora >= dataFim) {
                                    // Remove o card com um efeito de esvanecimento premium
                                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                                    card.style.opacity = '0';
                                    card.style.transform = 'scale(0.9) translateY(-10px)';
                                    
                                    setTimeout(() => {
                                        card.remove();
                                        verificarSeVazio();
                                    }, 600);
                                }
                            }
                        }
                    });
                }
            })
            .catch(error => console.error('Erro no processamento do auto-cleanup:', error));
    }

    function verificarSeVazio() {
        const grid = document.getElementById('bookingsGrid');
        if (grid) {
            const cardsRestantes = grid.querySelectorAll('.booking-card');
            if (cardsRestantes.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state">
                        <h2>SISTEMA VAZIO</h2>
                        <p>Nenhuma reserva ativa foi encontrada no banco de dados da escola.</p>
                    </div>
                `;
            }
        }
    }

    // Executa a primeira limpeza na interface após 5 segundos da inicialização da página
    setTimeout(verificarReservasExpiradas, 5000);

    // Agenda execuções periódicas em segundo plano
    setInterval(verificarReservasExpiradas, INTERVALO_VERIFICACAO);
});
