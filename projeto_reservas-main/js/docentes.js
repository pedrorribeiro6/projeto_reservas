document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchDocente');
    const docenteCards = document.querySelectorAll('.docente-card');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase().trim();

            docenteCards.forEach(card => {
                const nome = card.getAttribute('data-nome');
                const email = card.getAttribute('data-email');

                if (nome.includes(searchTerm) || email.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });

            // Opcional: Mostrar mensagem se nenhum resultado for encontrado
            const visibleCards = document.querySelectorAll('.docente-card[style="display: flex;"]').length;
            const grid = document.getElementById('docentesGrid');
            const noResults = document.getElementById('noResultsMsg');

            if (visibleCards === 0 && searchTerm !== '') {
                if (!noResults) {
                    const msg = document.createElement('div');
                    msg.id = 'noResultsMsg';
                    msg.className = 'empty-state';
                    msg.innerHTML = `<h2>SEM RESULTADOS</h2><p>Nenhum docente corresponde à sua busca.</p>`;
                    grid.appendChild(msg);
                }
            } else {
                if (noResults) noResults.remove();
            }
        });
    }
});
