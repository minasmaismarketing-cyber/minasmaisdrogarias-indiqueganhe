<?php



declare(strict_types=1);



$userFirstName = explode(' ', trim((string) $user['nome']))[0] ?? '';

$shareLink = Session::flash('share_link') ?? $inviteLink;

?>

<section class="page-hero animate-slide">

    <p class="page-hero__eyebrow">Olá, <?= e($userFirstName) ?></p>

    <h1 class="page-hero__title">Meu painel</h1>

    <p class="page-hero__subtitle">Indique amigos e acompanhe seu progresso.</p>

</section>



<section class="stats-grid" aria-label="Resumo das indicações">

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



<section class="mm-card mm-card--premium mm-card--highlight" id="meu-codigo">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Meu código de indicação</h2>
        <p class="mm-card__subtitle">Compartilhe seu código e convide amigos.</p>
    </div>
    
    <p class="dash-code dash-code--premium" id="referral-code"><?= e($codigo) ?></p>
    
    <div class="code-actions code-actions--premium">
        <button type="button" class="btn btn--block btn--primary" id="btn-copy-code" data-copy="<?= e($codigo) ?>">
            Copiar código
        </button>
        <button type="button" class="btn btn--block btn--outline" id="btn-share-native">
            Compartilhar
        </button>
    </div>
    
    <?php if ($linkStats['cliques'] > 0): ?>
        <div class="link-status-compact">
            <span class="link-status-compact__item">
                <span class="link-status-compact__icon">👆</span>
                <span class="link-status-compact__value"><?= (int) $linkStats['cliques'] ?> cliques</span>
            </span>
            <?php if ($linkStats['ultimo_acesso']): ?>
                <span class="link-status-compact__item">
                    <span class="link-status-compact__icon">🕒</span>
                    <span class="link-status-compact__value"><?= e(date('d/m', strtotime($linkStats['ultimo_acesso']))) ?></span>
                </span>
            <?php endif; ?>
            <span class="link-status-compact__item">
                <span class="link-status-compact__icon link-status-compact__icon--<?= $linkStats['ativo'] ? 'active' : 'inactive' ?>">
                    <?= $linkStats['ativo'] ? '🟢' : '🔴' ?>
                </span>
                <span class="link-status-compact__value"><?= $linkStats['ativo'] ? 'Ativo' : 'Inativo' ?></span>
            </span>
        </div>
    <?php endif; ?>
</section>



<?php if ($validacaoStats['total'] > 0): ?>
<section class="mm-card mm-card--info" id="validacoes-progress">
    <div class="mm-card__header">
        <h2 class="mm-card__title">Progresso de Indicações</h2>
        <p class="mm-card__subtitle">Acompanhe o status das suas indicações em validação.</p>
    </div>

    <div class="validacao-stats">
        <div class="validacao-stat">
            <span class="validacao-stat__value"><?= (int) $validacaoStats['total'] ?></span>
            <span class="validacao-stat__label">Total</span>
        </div>
        <div class="validacao-stat validacao-stat--success">
            <span class="validacao-stat__value"><?= (int) $validacaoStats['aprovados'] ?></span>
            <span class="validacao-stat__label">Aprovados</span>
        </div>
        <div class="validacao-stat validacao-stat--error">
            <span class="validacao-stat__value"><?= (int) $validacaoStats['reprovados'] ?></span>
            <span class="validacao-stat__label">Reprovados</span>
        </div>
        <div class="validacao-stat validacao-stat--premium">
            <span class="validacao-stat__value"><?= (int) $validacaoStats['beneficios_liberados'] ?></span>
            <span class="validacao-stat__label">Benefícios</span>
        </div>
    </div>
</section>
<?php endif; ?>






<p class="page-note">Cupom, WhatsApp e integrações serão liberados nas próximas etapas.</p>



<script>

    window.__DASHBOARD__ = {

        inviteLink: <?= json_encode($inviteLink, JSON_UNESCAPED_UNICODE) ?>,

        shareLink: <?= json_encode($shareLink, JSON_UNESCAPED_UNICODE) ?>

    };

</script>

