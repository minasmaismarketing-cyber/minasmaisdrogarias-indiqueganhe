<?php declare(strict_types=1); ?>

<section class="page-hero">
    <h1 class="page-hero__title">Notificações</h1>
    <p class="page-hero__subtitle">Acompanhe as atualizações do sistema.</p>
</section>

<section class="mm-card">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Central de Notificações</h2>
        <button type="button" class="btn btn--sm btn--ghost" id="btn-mark-all-read">Marcar todas como lidas</button>
    </div>

    <div class="notifications-list">
        <div class="notification-item notification-item--unread">
            <div class="notification-item__icon notification-item__icon--info">ℹ️</div>
            <div class="notification-item__content">
                <h3 class="notification-item__title">Campanha iniciada</h3>
                <p class="notification-item__text">A campanha de indicações foi iniciada. Comece a compartilhar seu código!</p>
                <span class="notification-item__date">Hoje, 10:00</span>
            </div>
        </div>

        <div class="notification-item notification-item--unread">
            <div class="notification-item__icon notification-item__icon--success">✅</div>
            <div class="notification-item__content">
                <h3 class="notification-item__title">Benefício disponível</h3>
                <p class="notification-item__text">Você tem um benefício disponível para resgate!</p>
                <span class="notification-item__date">Ontem, 15:30</span>
            </div>
        </div>

        <div class="notification-item">
            <div class="notification-item__icon notification-item__icon--alert">⚡</div>
            <div class="notification-item__content">
                <h3 class="notification-item__title">Sistema atualizado</h3>
                <p class="notification-item__text">O sistema foi atualizado com novas funcionalidades.</p>
                <span class="notification-item__date">25/06/2026, 09:00</span>
            </div>
        </div>
    </div>

    <p class="mm-card__text">As notificações são simuladas. Integrações com AppsFlyer, VTEX e WhatsApp serão implementadas futuramente.</p>
</section>

<script>
document.getElementById('btn-mark-all-read').addEventListener('click', () => {
    document.querySelectorAll('.notification-item--unread').forEach(item => {
        item.classList.remove('notification-item--unread');
    });
});
</script>
