document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchReserva');
    const bookingCards = document.querySelectorAll('.booking-card');
    const grid = document.getElementById('bookingsGrid');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase().trim();
            let visibleCount = 0;

            bookingCards.forEach(card => {
                const professor = card.getAttribute('data-professor');

                if (professor.includes(searchTerm)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Gerenciamento da mensagem de "Sem resultados"
            const oldNoResults = document.getElementById('noResultsReserva');
            if (visibleCount === 0 && searchTerm !== '') {
                if (!oldNoResults) {
                    const msg = document.createElement('div');
                    msg.id = 'noResultsReserva';
                    msg.className = 'empty-state';
                    msg.style.width = '100%';
                    msg.innerHTML = `<h2>NENHUMA RESERVA ENCONTRADA</h2><p>Não encontramos agendamentos para o professor "${e.target.value}".</p>`;
                    grid.appendChild(msg);
                }
            } else {
                if (oldNoResults) oldNoResults.remove();
            }
        });
    }
});
