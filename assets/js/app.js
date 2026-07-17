document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('[data-modal-trigger]').forEach((trigger) => {

        trigger.addEventListener('click', () => {

            AppUI.modal({

                title: trigger.dataset.modalTitle || 'Aviso',

                body: trigger.dataset.modalBody || 'Em breve.',

                confirmText: 'Entendi',

            });

        });

    });



    // Native share (mobile) / Copy (desktop)
    const shareNativeBtn = document.getElementById('btn-share-native');
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    if (shareNativeBtn) {
        shareNativeBtn.addEventListener('click', async () => {
            const shareUrl = window.__DASHBOARD__?.inviteLink || window.__DASHBOARD__?.shareLink;
            if (!shareUrl) return;

            const shareData = {
                title: 'Indique e Ganhe Minas Mais',
                text: 'Participe comigo da campanha Minas Mais.',
                url: shareUrl
            };

            const originalText = shareNativeBtn.textContent;
            shareNativeBtn.textContent = 'Carregando...';
            shareNativeBtn.disabled = true;

            try {
                if (navigator.share && isMobile) {
                    try {
                        await navigator.share(shareData);
                        showToast('Compartilhado com sucesso!', 'success');
                    } catch (err) {
                        if (err.name !== 'AbortError') {
                            console.error('Share failed:', err);
                            showToast('Erro ao compartilhar', 'error');
                        }
                    }
                } else {
                    try {
                        await navigator.clipboard.writeText(shareUrl);
                        showToast('Link copiado!', 'success');
                    } catch (err) {
                        console.error('Copy failed:', err);
                        AppUI.modal({
                            title: 'Link de Indicação',
                            body: `<input type="text" value="${shareUrl}" readonly style="width:100%;padding:0.5rem;border:1px solid #ddd;border-radius:4px;margin-bottom:1rem;">`,
                            confirmText: 'Fechar',
                            onConfirm: () => {
                                navigator.clipboard.writeText(shareUrl).catch(() => {});
                            }
                        });
                    }
                }
            } finally {
                shareNativeBtn.textContent = originalText;
                shareNativeBtn.disabled = false;
            }
        });
    }

    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const text = btn.dataset.copy;
            if (!text) return;

            try {
                await navigator.clipboard.writeText(text);
                const originalText = btn.textContent;
                const successText = btn.dataset.copySuccess || 'Copiado!';
                const toastText = btn.dataset.copyToast || successText;
                btn.textContent = successText;
                btn.classList.add('btn--success');
                showToast(toastText, 'success');
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.classList.remove('btn--success');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy:', err);
                showToast('Não foi possível copiar.', 'error');
            }
        });
    });

    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast--${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 24px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            border-radius: 8px;
            font-weight: 500;
            z-index: 9999;
            animation: slideUp 0.3s ease;
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }
});

