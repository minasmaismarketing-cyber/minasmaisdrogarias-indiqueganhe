document.addEventListener('DOMContentLoaded', () => {
    const card = document.getElementById('indicacao-status-card');
    if (!card) {
        return;
    }

    const validacaoId = card.getAttribute('data-validacao-id') || '';
    const storageKey = 'indicacao_status_dismissed_' + validacaoId;

    try {
        if (validacaoId && localStorage.getItem(storageKey) === '1') {
            card.hidden = true;
            return;
        }
    } catch (e) {
        // localStorage indisponível — mantém o card
    }

    const dismissBtn = document.getElementById('btn-dismiss-status-card');
    if (dismissBtn) {
        dismissBtn.addEventListener('click', () => {
            card.hidden = true;
            try {
                if (validacaoId) {
                    localStorage.setItem(storageKey, '1');
                }
            } catch (e) {
                // ignore
            }
        });
    }
});
