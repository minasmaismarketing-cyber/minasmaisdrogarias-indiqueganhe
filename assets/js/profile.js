/**
 * Perfil — modais acessíveis (editar / senha / excluir)
 */
(function () {
    const FOCUSABLE =
        'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';

    let activeModal = null;
    let lastFocus = null;

    function getModalByKey(key) {
        if (key === 'edit-profile') return document.getElementById('edit-profile-modal');
        if (key === 'change-password') return document.getElementById('change-password-modal');
        if (key === 'delete-account') return document.getElementById('delete-account-modal');
        return null;
    }

    function getFocusable(modal) {
        return Array.from(modal.querySelectorAll(FOCUSABLE)).filter(
            (el) => el.offsetParent !== null || el === document.activeElement
        );
    }

    function openModal(modal) {
        if (!modal) return;
        if (activeModal && activeModal !== modal) {
            closeModal(activeModal, false);
        }

        lastFocus = document.activeElement;
        activeModal = modal;
        modal.hidden = false;
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';

        const focusables = getFocusable(modal);
        const firstInput = modal.querySelector('input:not([disabled]), button:not([data-close-modal])');
        (firstInput || focusables[0] || modal).focus();
    }

    function closeModal(modal, restoreFocus = true) {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.hidden = true;
        if (activeModal === modal) {
            activeModal = null;
            document.body.style.overflow = '';
        }
        if (restoreFocus && lastFocus && typeof lastFocus.focus === 'function') {
            lastFocus.focus();
        }
    }

    function onKeydown(e) {
        if (!activeModal) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            closeModal(activeModal);
            return;
        }

        if (e.key !== 'Tab') return;

        const focusables = getFocusable(activeModal);
        if (focusables.length === 0) {
            e.preventDefault();
            return;
        }

        const first = focusables[0];
        const last = focusables[focusables.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-open-modal]').forEach((btn) => {
            btn.addEventListener('click', () => {
                openModal(getModalByKey(btn.getAttribute('data-open-modal')));
            });
        });

        document.querySelectorAll('[data-modal]').forEach((modal) => {
            modal.querySelectorAll('[data-close-modal]').forEach((el) => {
                el.addEventListener('click', () => closeModal(modal));
            });
        });

        document.addEventListener('keydown', onKeydown);

        const autoOpen = window.__PERFIL__?.openModal;
        if (autoOpen) {
            openModal(getModalByKey(autoOpen));
        }
    });
})();
