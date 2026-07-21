<?php declare(strict_types=1); ?>
<?php require BASE_PATH . '/views/partials/alerts.php'; ?>

<section class="page-hero page-hero--compact animate-slide">
    <h1 class="page-hero__title">Meus Cupons</h1>
    <p class="page-hero__subtitle">Seus benefícios de indicação.</p>
</section>

<section class="coupons-list">
    <?php if ($cupons === []): ?>
        <div class="mm-card">
            <div class="mm-empty-state">
                <span class="mm-empty-state__icon" aria-hidden="true">%</span>
                <p class="mm-empty-state__title">Você ainda não possui cupons</p>
                <p class="mm-empty-state__text">Indique amigos e ganhe cupons quando suas indicações forem aprovadas.</p>
                <a href="<?= url('/dashboard') ?>" class="btn btn--block btn--primary">Ir para o painel</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($cupons as $cupom): ?>
            <?php
            $status = (string) ($cupom['status'] ?? '');
            $isDisponivel = $status === Cupom::STATUS_DISPONIVEL;
            $isUtilizado = $status === Cupom::STATUS_UTILIZADO;
            $valor = (float) ($cupom['valor'] ?? 0);
            $beneficio = ($cupom['tipo'] ?? '') === Cupom::TIPO_PERCENTUAL
                ? rtrim(rtrim(number_format($valor, 2, ',', ''), '0'), ',') . '% OFF'
                : 'R$ ' . number_format($valor, 2, ',', '.');
            $utilizadoEm = !empty($cupom['utilizado_em'])
                ? date('d/m/Y H:i', strtotime((string) $cupom['utilizado_em']))
                : null;
            ?>
            <article
                class="coupon-card coupon-card--<?= e(strtolower($status)) ?> coupon-card--focus"
                aria-label="<?= $isUtilizado ? 'Cupom utilizado' : 'Cupom disponível' ?>"
            >
                <header class="coupon-card__intro">
                    <h2 class="coupon-card__title">Seu cupom exclusivo</h2>
                    <p class="coupon-card__beneficio"><?= e($beneficio) ?></p>
                </header>

                <p class="coupon-card__codigo-display" aria-label="Código do cupom">
                    <?= e((string) $cupom['codigo']) ?>
                </p>

                <?php if ($isDisponivel): ?>
                    <div class="coupon-card__actions">
                        <button
                            type="button"
                            class="btn btn--block btn--primary coupon-card__copy"
                            data-copy="<?= e((string) $cupom['codigo']) ?>"
                            data-copy-success="Cupom copiado"
                            data-copy-toast="Cupom copiado"
                        >
                            Copiar cupom
                        </button>
                        <p class="coupon-card__warning" role="note">
                            <span aria-hidden="true">⚠</span>
                            Uso único. Não compartilhe este código.
                            Caso outra pessoa utilize antes de você, ele perderá a validade para sua compra.
                        </p>
                    </div>
                <?php elseif ($isUtilizado): ?>
                    <div class="coupon-card__used" role="status">
                        <p class="coupon-card__used-label">
                            <span aria-hidden="true">✓</span> Cupom utilizado
                        </p>
                        <?php if ($utilizadoEm !== null): ?>
                            <p class="coupon-card__used-date">Utilizado em: <?= e($utilizadoEm) ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="coupon-card__status-line" role="status">
                        <?= Cupom::statusIcon($status) ?>
                        <?= e(Cupom::statusLabel($status)) ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
