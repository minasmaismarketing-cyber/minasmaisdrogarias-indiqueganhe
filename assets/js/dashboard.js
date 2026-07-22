document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-dismiss-status-card');
    const dismissBtn = document.getElementById('btn-dismiss-status-card');
    if (form && dismissBtn) {
        form.addEventListener('submit', () => {
            dismissBtn.disabled = true;
            dismissBtn.textContent = 'Salvando...';
        });
    }
});
