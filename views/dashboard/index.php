<?php

declare(strict_types=1);

$userFirstName = explode(' ', trim((string) $user['nome']))[0] ?? '';
$shareLink = Session::flash('share_link') ?? $inviteLink;
$stats = $stats ?? ['total' => 0, 'validadas' => 0, 'pendentes' => 0, 'liberadas' => 0];
$showRetryCta = !empty($showRetryCta);
?>

<div class="home-stack">
    <section class="page-hero page-hero--compact animate-slide">
        <p class="page-hero__eyebrow">Olá, <?= e($userFirstName) ?></p>
        <h1 class="page-hero__title">Meu painel</h1>
    </section>

    <?php require BASE_PATH . '/views/partials/indicacao-status-card.php'; ?>

    <?php if ($showRetryCta): ?>
        <section
            class="indicacao-status-card indicacao-status-card--info indicacao-status-card--standalone"
            id="indicacao-retry-cta"
            role="region"
            aria-label="Tentar nova indicação"
        >
            <div class="indicacao-status-card__inner">
                <div class="indicacao-status-card__heading">
                    <h2 class="indicacao-status-card__title">Que tal tentar novamente?</h2>
                </div>
                <div class="indicacao-status-card__content">
                    <p class="indicacao-status-card__motivo">
                        Compartilhe seu link com outra pessoa. Quando a indicação for aprovada, seu
                        benefício será liberado.
                    </p>
                    <button type="button" class="btn btn--block btn--primary" id="btn-retry-share" data-share-native>
                        Fazer nova indicação
                    </button>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="stats-grid stats-grid--home" aria-label="Resumo das indicações">
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) $stats['total'] ?></span>
            <span class="stat-card__label">Total</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) $stats['validadas'] ?></span>
            <span class="stat-card__label">Validadas</span>
        </article>
        <article class="stat-card">
            <span class="stat-card__value"><?= (int) $stats['pendentes'] ?></span>
            <span class="stat-card__label">Pendentes</span>
        </article>
        <article class="stat-card stat-card--accent">
            <span class="stat-card__value"><?= (int) $stats['liberadas'] ?></span>
            <span class="stat-card__label">Liberadas</span>
        </article>
    </section>

    <section class="mm-card dash-invite-card" id="meu-codigo">
        <div class="mm-card__header mm-card__header--stack">
            <h2 class="mm-card__title dash-invite-card__title">Meu link exclusivo</h2>
            <p class="mm-card__subtitle dash-invite-copy">
                Compartilhe seu link exclusivo e ganhe
                <span class="dash-invite-off">10% OFF</span>
                em qualquer compra no App Minas Mais!
            </p>
        </div>

        <div class="code-actions code-actions--premium">
            <button type="button" class="btn btn--block btn--primary" id="btn-share-native">
                Compartilhar
            </button>
        </div>
    </section>
</div>

<script>
    window.__DASHBOARD__ = {
        inviteLink: <?= json_encode($inviteLink, JSON_UNESCAPED_UNICODE) ?>,
        shareLink: <?= json_encode($shareLink, JSON_UNESCAPED_UNICODE) ?>,
        shareUrl: <?= json_encode(url('/dashboard/compartilhar'), JSON_UNESCAPED_UNICODE) ?>,
        csrfToken: <?= json_encode($csrfToken ?? Csrf::token(), JSON_UNESCAPED_UNICODE) ?>
    };
</script>
