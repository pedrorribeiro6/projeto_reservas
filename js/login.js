document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form[action="processa_login.php"]');
    
    // Função para criar/recuperar a caixa de erro dentro do formulário
    const getOrCreateErrorMsg = (form) => {
        let errDiv = form.querySelector('.login-error-msg');
        if (!errDiv) {
            errDiv = document.createElement('div');
            errDiv.className = 'login-error-msg';
            
            // Estilização Neo-Brutalista / Premium condizente com o projeto
            errDiv.style.color = '#FA1E4E';
            errDiv.style.background = 'rgba(250, 30, 78, 0.1)';
            errDiv.style.border = '2px solid #FA1E4E';
            errDiv.style.padding = '10px';
            errDiv.style.marginBottom = '20px';
            errDiv.style.borderRadius = '6px';
            errDiv.style.textAlign = 'center';
            errDiv.style.fontFamily = "'Rajdhani', sans-serif";
            errDiv.style.fontWeight = '700';
            errDiv.style.letterSpacing = '0.05em';
            errDiv.style.textTransform = 'uppercase';
            errDiv.style.display = 'none';
            errDiv.style.boxShadow = '0 0 15px rgba(250, 30, 78, 0.2)';
            
            // Insere antes do primeiro campo de input
            const firstInput = form.querySelector('.input-group-prof, .input-group-adm');
            if (firstInput) {
                form.insertBefore(errDiv, firstInput);
            } else {
                form.prepend(errDiv);
            }
        }
        return errDiv;
    };

    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault(); // Impede o recarregamento da página
            const btnSubmit = form.querySelector('button[type="submit"]');
            
            const errDiv = getOrCreateErrorMsg(form);
            errDiv.style.display = 'none'; // Esconde erros anteriores
            
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.style.opacity = '0.7';
            }

            const formData = new FormData(form);
            formData.append('ajax', 'true'); // Flag para o backend saber que é requisição AJAX

            try {
                const res = await fetch('processa_login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await res.json();
                
                if (data.sucesso) {
                    // Redireciona de acordo com o perfil
                    window.location.href = data.redirect;
                } else {
                    // Exibe a mensagem de erro fornecida pelo backend na própria tela
                    errDiv.textContent = data.erro || "Usuário ou senha incorreta. Tente Novamente";
                    errDiv.style.display = 'block';
                }
            } catch (error) {
                errDiv.textContent = "Erro de conexão com o servidor. Tente novamente.";
                errDiv.style.display = 'block';
            } finally {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.style.opacity = '1';
                }
            }
        });
    });
});
