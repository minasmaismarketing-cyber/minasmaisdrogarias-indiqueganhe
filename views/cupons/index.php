<?php declare(strict_types=1); ?>
<?php require BASE_PATH . '/views/partials/alerts.php'; ?>

<section class="page-hero">
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
            <p class="mm-card__placeholder">Você ainda não possui cupons.</p>
            <p class="mm-card__text">Indique amigos e ganhe cupons quando suas indicações forem validadas!</p>
            <a href="<?= url('/dashboard') ?>" class="btn btn--block btn--primary">Ir para Dashboard</a>
        </div>
    <?php else: ?>
        <?php foreach ($cupons as $cupom): ?>
            <article class="coupon-card coupon-card--<?= strtolower($cupom['status']) ?>">
                <div class="coupon-card__header">
                    <span class="coupon-card__icon"><?= Cupom::statusIcon($cupom['status']) ?></span>
                    <div class="coupon-card__status">
                        <?= e(Cupom::statusLabel($cupom['status'])) ?>
                    </div>
                </div>
                
                <div class="coupon-card__code">
                    <span class="coupon-card__label">Código</span>
                    <span class="coupon-card__value"><?= e($cupom['codigo']) ?></span>
                    <?php if ($cupom['status'] === Cupom::STATUS_DISPONIVEL): ?>
                        <button type="button" class="btn btn--sm btn--ghost coupon-card__copy" data-code="<?= e($cupom['codigo']) ?>">
                            Copiar
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="coupon-card__details">
                    <div class="coupon-card__detail">
                        <span class="coupon-card__label">Valor</span>
                        <span class="coupon-card__value">
                            <?= e($cupom['valor']) ?> <?= e(Cupom::tipoLabel($cupom['tipo'])) ?>
                        </span>
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
                
                <div class="coupon-card__footer">
                    <span class="coupon-card__date">
                        Gerado em <?= e(date('d/m/Y', strtotime($cupom['created_at']))) ?>
                    </span>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<script>
document.querySelectorAll('.coupon-card__copy').forEach(button => {
    button.addEventListener('click', async () => {
        const code = button.dataset.code;
        try {
            await navigator.clipboard.writeText(code);
            button.textContent = 'Copiado!';
            setTimeout(() => {
                button.textContent = 'Copiar';
            }, 2000);
        } catch (err) {
            alert('Erro ao copiar código');
        }
    });
});
</script>
