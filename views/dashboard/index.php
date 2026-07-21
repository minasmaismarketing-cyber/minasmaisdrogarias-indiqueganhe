<?php

declare(strict_types=1);

$userFirstName = explode(' ', trim((string) $user['nome']))[0] ?? '';
$shareLink = Session::flash('share_link') ?? $inviteLink;
?>

<section class="page-hero page-hero--compact animate-slide">
    <p class="page-hero__eyebrow">Olá, <?= e($userFirstName) ?></p>
    <h1 class="page-hero__title">Meu painel</h1>
</section>

<?php require BASE_PATH . '/views/partials/indicacao-status-card.php'; ?>

<section class="mm-card mm-card--premium mm-card--highlight" id="meu-codigo">
    <div class="mm-card__header mm-card__header--stack">
        <h2 class="mm-card__title">Meu link exclusivo</h2>
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

<script>
    window.__DASHBOARD__ = {
        inviteLink: <?= json_encode($inviteLink, JSON_UNESCAPED_UNICODE) ?>,
        shareLink: <?= json_encode($shareLink, JSON_UNESCAPED_UNICODE) ?>
    };
</script>
