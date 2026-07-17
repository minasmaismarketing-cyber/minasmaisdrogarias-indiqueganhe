<?php declare(strict_types=1); ?>
<?php require BASE_PATH . '/views/partials/alerts.php'; ?>

<section class="page-hero animate-slide">
    <h1 class="page-hero__title">Meus Cupons</h1>
    <p class="page-hero__subtitle">Acompanhe seus cupons de indicação.</p>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <span class="stat-card__value"><?= count(array_filter($cupons, fn($c) => $c['status'] === Cupom::STATUS_DISPONIVEL)) ?></span>
        <span class="stat-card__label">Disponíveis</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= count(array_filter($cupons, fn($c) => $c['status'] === Cupom::STATUS_UTILIZADO)) ?></span>
        <span class="stat-card__label">Utilizados</span>
    </article>
    <article class="stat-card">
        <span class="stat-card__value"><?= count(array_filter($cupons, fn($c) => $c['status'] === Cupom::STATUS_EXPIRADO)) ?></span>
        <span class="stat-card__label">Expirados</span>
    </article>
</section>

<section class="coupons-list">
    <?php if ($cupons === []): ?>
        <div class="mm-card">
            <div class="mm-empty-state">
                <span class="mm-empty-state__icon" aria-hidden="true">%</span>
                <p class="mm-empty-state__title">Você ainda não possui cupons</p>
                <p class="mm-empty-state__text">Indique amigos e ganhe cupons quando suas indicações forem validadas.</p>
                <a href="<?= url('/dashboard') ?>" class="btn btn--block btn--primary">Ir para Dashboard</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($cupons as $cupom): ?>
            <?php
            $valor = (float) ($cupom['valor'] ?? 0);
            $beneficio = ($cupom['tipo'] ?? '') === Cupom::TIPO_PERCENTUAL
                ? rtrim(rtrim(number_format($valor, 2, ',', ''), '0'), ',') . '% OFF'
                : 'R$ ' . number_format($valor, 2, ',', '.');
            ?>
            <article class="coupon-card coupon-card--<?= strtolower($cupom['status']) ?>">
                <div class="coupon-card__header">
                    <span class="coupon-card__icon"><?= Cupom::statusIcon($cupom['status']) ?></span>
                    <div class="coupon-card__status">
                        <?= e(Cupom::statusLabel($cupom['status'])) ?>
                    </div>
                </div>

                <div class="coupon-card__details">
                    <div class="coupon-card__detail">
                        <span class="coupon-card__label">Benefício</span>
                        <span class="coupon-card__value"><?= e($beneficio) ?></span>
                    </div>
                    <div class="coupon-card__detail">
                        <span class="coupon-card__label">Campanha</span>
                        <span class="coupon-card__value"><?= e($cupom['campanha_nome'] ?? 'N/A') ?></span>
                    </div>
                    <?php if ($cupom['validade']): ?>
                        <div class="coupon-card__detail">
                            <span class="coupon-card__label">Validade</span>
                            <span class="coupon-card__value">
                                <?= e(date('d/m/Y', strtotime($cupom['validade']))) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($cupom['status'] === Cupom::STATUS_DISPONIVEL): ?>
                    <div class="coupon-card__code" style="flex-direction: column; align-items: stretch; gap: 0.5rem;">
                        <button
                            type="button"
                            class="btn btn--block btn--primary coupon-card__copy"
                            data-copy="<?= e($cupom['codigo']) ?>"
                            data-copy-success="Cupom copiado"
                            data-copy-toast="Cupom copiado"
                        >
                            Copiar meu cupom
                        </button>
                        <p class="coupon-card__hint" style="margin:0; font-size:0.75rem; opacity:0.7; line-height:1.35;">
                            Uso único e intransferível. Não compartilhe: quem usar primeiro consome o cupom.
                        </p>
                    </div>
                <?php endif; ?>

                <div class="coupon-card__footer">
                    <span class="coupon-card__date">
                        Gerado em <?= e(date('d/m/Y', strtotime($cupom['created_at']))) ?>
                    </span>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
