<?php

declare(strict_types=1);

$currentPath = request_path();
$onDashboard = $currentPath === '/dashboard';
$onIndicacoes = $currentPath === '/indicacoes';
?>
<nav class="mm-bottom-nav" aria-label="Navegação principal">
    <a href="<?= url('/dashboard') ?>" class="mm-bottom-nav__item <?= $onDashboard ? 'is-active' : '' ?>">
        <span class="mm-bottom-nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5z"/></svg>
        </span>
        <span>Início</span>
    </a>
    <a href="<?= url('/indicacoes') ?>" class="mm-bottom-nav__item <?= $onIndicacoes ? 'is-active' : '' ?>">
        <span class="mm-bottom-nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </span>
        <span>Indicações</span>
    </a>
    <a href="<?= url('/meus-cupons') ?>" class="mm-bottom-nav__item <?= $currentPath === '/meus-cupons' ? 'is-active' : '' ?>">
        <span class="mm-bottom-nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V8H6a2 2 0 0 1-2-2 2 2 0 0 1 2-2h12v4"/><path d="M4 6v12a2 2 0 0 0 2 2h14v-8H6a2 2 0 0 1-2-2"/></svg>
        </span>
        <span>Cupom</span>
    </a>
    <a href="<?= url('/perfil') ?>" class="mm-bottom-nav__item <?= $currentPath === '/perfil' ? 'is-active' : '' ?>">
        <span class="mm-bottom-nav__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 4-6 8-6s8 2 8 6"/></svg>
        </span>
        <span>Perfil</span>
    </a>
    <?php if (Auth::isAdmin()): ?>
        <a href="<?= url('/admin') ?>" class="mm-bottom-nav__item">
            <span class="mm-bottom-nav__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            </span>
            <span>Admin</span>
        </a>
    <?php endif; ?>
</nav>
