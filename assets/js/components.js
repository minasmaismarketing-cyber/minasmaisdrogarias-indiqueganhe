/**
 * UI Components — Minas Mais Design System
 * Toast · Modal · Loader
 */
const AppUI = {
    toastRoot: null,
    modalEl: null,
    loaderEl: null,

    init() {
        this.toastRoot = document.getElementById('mm-toast-root');
        this.modalEl = document.getElementById('mm-modal');
        this.loaderEl = document.getElementById('mm-loader');

        this.bindModal();
        this.promoteAlertsToToast();
        this.bindFormLoaders();
    },

    toast(message, type = 'info', duration = 4000) {
        if (!this.toastRoot || !message) return;

        const el = document.createElement('div');
        el.className = 'mm-toast' + (type !== 'info' ? ` mm-toast--${type}` : '');
        el.setAttribute('role', 'status');
        el.textContent = message;

        this.toastRoot.appendChild(el);

        setTimeout(() => {
            el.classList.add('is-leaving');
            el.addEventListener('animationend', () => el.remove(), { once: true });
        }, duration);
    },

    modal({ title = '', body = '', confirmText = 'OK', onConfirm = null }) {
        if (!this.modalEl) return;

        const titleEl = this.modalEl.querySelector('.mm-modal__title');
        const bodyEl = this.modalEl.querySelector('.mm-modal__body');
        const footerEl = this.modalEl.querySelector('.mm-modal__footer');

        titleEl.textContent = title;
        bodyEl.innerHTML = body;
        footerEl.innerHTML = '';

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn--block';
        btn.textContent = confirmText;
        btn.addEventListener('click', () => {
            this.closeModal();
            if (typeof onConfirm === 'function') onConfirm();
        }, { once: true });
        footerEl.appendChild(btn);

        this.modalEl.hidden = false;
        this.modalEl.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    },

    closeModal() {
        if (!this.modalEl) return;
        this.modalEl.hidden = true;
        this.modalEl.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    },

    loader(show = true) {
        if (!this.loaderEl) return;
        this.loaderEl.hidden = !show;
        this.loaderEl.setAttribute('aria-hidden', show ? 'false' : 'true');
    },

    bindModal() {
        if (!this.modalEl) return;

        this.modalEl.querySelectorAll('[data-modal-close]').forEach((el) => {
            el.addEventListener('click', () => this.closeModal());
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modalEl && !this.modalEl.hidden) {
                this.closeModal();
            }
        });
    },

    promoteAlertsToToast() {
        document.querySelectorAll('.alert--success, .alert--error').forEach((alert) => {
            const type = alert.classList.contains('alert--success') ? 'success' : 'error';
            const text = alert.textContent.trim();
            if (text) {
                this.toast(text, type);
            }
            alert.remove();
        });
    },

    bindFormLoaders() {
        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', () => {
                if (form.checkValidity !== undefined && !form.checkValidity()) return;
                this.loader(true);
            });
        });
    },
};

window.AppUI = AppUI;

document.addEventListener('DOMContentLoaded', () => AppUI.init());
