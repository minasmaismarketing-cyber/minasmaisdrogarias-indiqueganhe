<?php

declare(strict_types=1);

/** @var string $homeUrl */
/** @var string $logoUrl */
/** @var bool $showAuth */
/** @var bool $isGuest */
/** @var string|null $userName */
?>
<header class="mm-header">
    <div class="container mm-header__inner">
        <a href="<?= e($homeUrl) ?>" class="mm-header__logo" aria-label="Início">
            <img
                src="<?= e($logoUrl) ?>"
                alt=""
                class="mm-header__logo-img"
                height="36"
                loading="eager"
            >
        </a>

        <?php if ($showAuth): ?>
            <div class="mm-header__user">
                <?php if ($isGuest): ?>
                    <a href="<?= url('/login') ?>" class="mm-header__link">Entrar</a>
                    <a href="<?= url('/cadastro') ?>" class="btn btn--sm">Cadastrar</a>
                <?php else: ?>
                    <nav class="mm-header__nav-desktop" aria-label="Menu principal">
                        <a href="<?= url('/dashboard') ?>" class="<?= request_path() === '/dashboard' ? 'is-active' : '' ?>">Início</a>
                        <a href="<?= url('/indicacoes') ?>" class="<?= request_path() === '/indicacoes' ? 'is-active' : '' ?>">Indicações</a>
                        <a href="<?= url('/perfil') ?>" class="<?= request_path() === '/perfil' ? 'is-active' : '' ?>">Perfil</a>
                    </nav>
                    <div class="mm-header__user-info">
                        <?php if ($userName): ?>
                            <span class="mm-header__name"><?= e($userName) ?></span>
                        <?php endif; ?>
                        <a href="<?= url('/perfil') ?>" class="mm-header__avatar" aria-label="Meu perfil">
                            <span><?= e(mb_strtoupper(mb_substr($userName ?: 'U', 0, 1))) ?></span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</header>
