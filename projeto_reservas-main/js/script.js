document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle');
    
    // Verifica o armazenamento local para obter a preferência do usuário
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'light') {
        document.documentElement.classList.add('light-theme');
        document.body.classList.add('light-theme');
        themeToggleBtn.textContent = '🌙';
    } else {
        themeToggleBtn.textContent = '🌞';
    }

    // Função de clique para alternar
    themeToggleBtn.addEventListener('click', () => {
        document.documentElement.classList.toggle('light-theme');
        document.body.classList.toggle('light-theme');
        
        let theme = 'dark';
        if (document.documentElement.classList.contains('light-theme')) {
            theme = 'light';
            themeToggleBtn.textContent = '🌙';
        } else {
            themeToggleBtn.textContent = '🌞';
        }
        
        localStorage.setItem('theme', theme);
    });
});
