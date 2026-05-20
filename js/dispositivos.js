document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deviceModal');
    const openAddModal = document.getElementById('openAddModal');
    const closeModal = document.getElementById('closeModal');
    const deviceForm = document.getElementById('deviceForm');
    
    const modalTitle = document.getElementById('modalTitle');
    const deviceId = document.getElementById('deviceId');
    const deviceName = document.getElementById('deviceName');
    const deviceQty = document.getElementById('deviceQty');

    // Abre modal para adicionar
    if (openAddModal) {
        openAddModal.addEventListener('click', () => {
            modalTitle.textContent = 'ADICIONAR DISPOSITIVO';
            deviceForm.reset();
            deviceId.value = '';
            modal.style.display = 'flex';
        });
    }

    // Fecha modal
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    // Fecha modal ao clicar fora
    window.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
    });

    // Submete formulário
    if (deviceForm) {
        deviceForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(deviceForm);
            formData.append('acao', 'salvar');

            try {
                const res = await fetch('processa_dispositivo.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.sucesso) {
                    window.location.reload();
                } else {
                    document.getElementById('modal-erro-disp-msg').textContent = data.erro;
                    document.getElementById('modal-erro-disp').style.display = 'flex';
                }
            } catch (error) {
                document.getElementById('modal-erro-disp-msg').textContent = 'Não foi possível salvar o dispositivo.';
                document.getElementById('modal-erro-disp').style.display = 'flex';
            }
        });
    }

    // Função Global de Edição
    window.editDevice = (id, nome, qtd) => {
        modalTitle.textContent = 'EDITAR DISPOSITIVO';
        deviceId.value = id;
        deviceName.value = nome;
        deviceQty.value = qtd;
        modal.style.display = 'flex';
    };

    // Função Global de Exclusão
    window.deleteDevice = (id) => {
        window.confirmarExclusaoDispositivo(id);
    };
});
