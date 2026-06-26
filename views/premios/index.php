<?php

declare(strict_types=1);
?>
<section class="page-hero animate-slide">
    <h1 class="page-hero__title">Meus prêmios</h1>
    <p class="page-hero__subtitle">Acompanhe seus benefícios disponíveis.</p>
</section>

<section class="premio-card premio-card--<?= $premioState ?>">
    <div class="premio-card__icon">
        <?php if ($premioState === 'sem_beneficio'): ?>
            🎁
        <?php elseif ($premioState === 'disponivel'): ?>
            🎉
        <?php else: ?>
            ✅
        <?php endif; ?>
    </div>
    
    <h2 class="premio-card__title"><?= e($premioLabel) ?></h2>
    
    <?php if ($premioState === 'sem_beneficio'): ?>
        <p class="premio-card__text">
            Indique amigos e ganhe benefícios exclusivos quando suas indicações forem validadas.
        </p>
    <?php elseif ($premioState === 'disponivel'): ?>
        <p class="premio-card__text">
            Você tem <?= $liberadas ?> benefício(s) disponível(is) para resgate.
        </p>
        
        <div class="coupon-box">
            <div class="coupon-code">
                <span class="coupon-code__prefix">MINAS-</span>
                <span class="coupon-code__hidden">••••</span>
            </div>
        </div>
        
        <div class="premio-card__actions">
            <button type="button" class="btn btn--block btn--disabled" disabled>
                Revelar benefício
            </button>
            <p class="premio-card__note">Em breve</p>
        </div>
    <?php else: ?>
        <p class="premio-card__text">
            Benefício já resgatado.
        </p>
    <?php endif; ?>
    
    <?php if ($premioState !== 'disponivel'): ?>
        <div class="premio-card__actions">
            <?php if ($premioState === 'sem_beneficio'): ?>
                <a href="<?= url('/dashboard') ?>" class="btn btn--block btn--outline">
                    Ver indicações
                </a>
            <?php else: ?>
                <a href="<?= url('/dashboard') ?>" class="btn btn--block btn--outline">
                    Voltar ao painel
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<section class="mm-card">
    <h3 class="mm-card__title">Informações</h3>
    <ul class="info-list">
        <li class="info-list__item">
            <span class="info-list__label">Benefícios liberados:</span>
            <span class="info-list__value"><?= $liberadas ?></span>
        </li>
        <li class="info-list__item">
            <span class="info-list__label">Status atual:</span>
            <span class="info-list__value"><?= e($premioLabel) ?></span>
        </li>
    </ul>
</section>

<p class="page-note">O sistema de resgate de benefícios será implementado em breve.</p>
