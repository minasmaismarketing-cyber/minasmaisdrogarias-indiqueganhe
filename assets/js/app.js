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



    // Native share (mobile) / Copy (desktop) — Home + Meus Cupons
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const shareButtons = document.querySelectorAll('#btn-share-native, [data-share-native]');

    async function logShareIntent() {
        const endpoint = window.__DASHBOARD__?.shareUrl;
        const csrf = window.__DASHBOARD__?.csrfToken;
        if (!endpoint || !csrf) {
            return;
        }
        try {
            const body = new URLSearchParams();
            body.set('_csrf_token', csrf);
            await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: body.toString(),
                credentials: 'same-origin',
            });
        } catch (err) {
            // Registro falhou, mas o compartilhamento do link ainda pode seguir.
        }
    }

    shareButtons.forEach((shareNativeBtn) => {
        shareNativeBtn.addEventListener('click', async () => {
            const shareUrl = window.__DASHBOARD__?.inviteLink || window.__DASHBOARD__?.shareLink;
            if (!shareUrl) return;

            const shareData = {
                title: 'Indique e Ganhe Minas Mais',
                text: 'Compartilhe e ganhe 10% OFF em qualquer compra no App Minas Mais!',
                url: shareUrl
            };

            const labelEl = shareNativeBtn.querySelector('.js-share-label');
            const originalText = (labelEl ? labelEl.textContent : shareNativeBtn.textContent) || '';
            if (labelEl) {
                labelEl.textContent = 'Carregando...';
            } else {
                shareNativeBtn.textContent = 'Carregando...';
            }
            shareNativeBtn.disabled = true;

            try {
                await logShareIntent();

                if (navigator.share && isMobile) {
                    try {
                        await navigator.share(shareData);
                        showToast('Compartilhamento iniciado', 'success');
                    } catch (err) {
                        if (err.name !== 'AbortError') {
                            console.error('Share failed:', err);
                            showToast('Erro ao compartilhar', 'error');
                        }
                    }
                } else {
                    try {
                        await navigator.clipboard.writeText(shareUrl);
                        showToast('Compartilhamento iniciado — link copiado', 'success');
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
                if (labelEl) {
                    labelEl.textContent = originalText;
                } else {
                    shareNativeBtn.textContent = originalText;
                }
                shareNativeBtn.disabled = false;
            }
        });
    });

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
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: calc(88px + env(safe-area-inset-bottom, 0px));
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 24px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            border-radius: 8px;
            font-weight: 500;
            z-index: 9999;
            animation: slideUp 0.3s ease;
            max-width: calc(100vw - 32px);
            text-align: center;
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideDown 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }
});
