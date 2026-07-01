<?php declare(strict_types=1); ?>
<?php
$indicadorUsuarioId = '';
$smartScriptPayload = ['enabled' => false, 'scriptUrl' => ''];

if ($valid && $ref !== '') {
    $indicadorRow = (new Usuario())->findByCodigo($ref);
    $indicadorUsuarioId = $indicadorRow ? (string) ($indicadorRow['id'] ?? '') : '';
}
?>
<section class="convite-page convite-landing animate-slide">
    <?php if (!$valid): ?>
        <div class="convite-card convite-landing__card convite-landing__card--error">
            <div class="convite-logo convite-landing__logo">
                <img
                    src="<?= e(brand_logo_url()) ?>"
                    alt="Minas Mais Drogaria e Perfumaria"
                    class="convite-landing__logo-img"
                    width="180"
                    height="48"
                    loading="eager"
                >
            </div>
            <h1 class="convite-title convite-landing__title convite-landing__title--error">Convite indisponível</h1>
            <p class="convite-landing__text"><?= e($error) ?></p>
            <a href="<?= url('/') ?>" class="convite-download-btn convite-landing__cta convite-landing__cta--secondary">Voltar ao início</a>
        </div>
    <?php else: ?>
        <div class="convite-card convite-landing__card">
            <div class="convite-logo convite-landing__logo">
                <img
                    src="<?= e(brand_logo_url()) ?>"
                    alt="Minas Mais Drogaria e Perfumaria"
                    class="convite-landing__logo-img"
                    width="180"
                    height="48"
                    loading="eager"
                >
            </div>

            <div class="convite-landing__gift-wrap" aria-hidden="true">
                <span class="convite-landing__gift-icon">🎁</span>
            </div>

            <p class="convite-badge convite-landing__badge">🎁 Você foi indicado!</p>

            <h1 class="convite-title convite-landing__title">
                <?php if ($indicadorPrimeiroNome !== ''): ?>
                    <span class="convite-highlight convite-landing__referrer"><?= e($indicadorPrimeiroNome) ?></span>
                    quer dividir um benefício com você!
                <?php else: ?>
                    Alguém especial quer dividir um benefício com você!
                <?php endif; ?>
            </h1>

            <div class="convite-landing__benefits">
                <div class="convite-landing__section-header">
                    <h2 id="convite-benefits-title" class="convite-landing__section-title">Você ganha:</h2>
                </div>
                <div class="benefits-grid convite-landing__benefit-list" role="list">
                    <div class="benefit-card convite-landing__benefit-item" role="listitem">
                        <span class="benefit-icon convite-landing__benefit-icon" aria-hidden="true">✔</span>
                        <span class="benefit-description">5% OFF na primeira compra</span>
                    </div>
                    <div class="benefit-card convite-landing__benefit-item" role="listitem">
                        <span class="benefit-icon convite-landing__benefit-icon" aria-hidden="true">✔</span>
                        <span class="benefit-description">Entrega rápida em até 30 minutos</span>
                    </div>
                    <div class="benefit-card convite-landing__benefit-item" role="listitem">
                        <span class="benefit-icon convite-landing__benefit-icon" aria-hidden="true">✔</span>
                        <span class="benefit-description">Promoções exclusivas no aplicativo</span>
                    </div>
                </div>
            </div>

            <div class="convite-landing__steps">
                <div class="convite-landing__section-header">
                    <h2 id="convite-steps-title" class="convite-landing__section-title">Como funciona?</h2>
                </div>
                <div class="stepper convite-stepper" role="list">
                    <div class="step convite-stepper__step" role="listitem">
                        <span class="step-number convite-stepper__index" aria-hidden="true">①</span>
                        <span class="step-content convite-stepper__label">Baixe o aplicativo</span>
                    </div>
                    <div class="step convite-stepper__step" role="listitem">
                        <span class="step-number convite-stepper__index" aria-hidden="true">②</span>
                        <span class="step-content convite-stepper__label"><strong>Faça seu cadastro</strong></span>
                    </div>
                    <div class="step convite-stepper__step" role="listitem">
                        <span class="step-number convite-stepper__index" aria-hidden="true">③</span>
                        <span class="step-content convite-stepper__label">Seu desconto será liberado automaticamente</span>
                    </div>
                </div>
            </div>

            <a
                id="convite-download-btn"
                href="<?= e($appDownloadUrl) ?>"
                class="convite-download-btn convite-landing__cta"
                data-fallback-url="<?= e($appDownloadUrl) ?>"
                <?= $appDownloadUrl === '#' ? 'aria-disabled="true"' : 'target="_blank" rel="noopener noreferrer"' ?>
            >
                BAIXAR O APP
            </a>

            <p class="convite-footer convite-landing__footnote">
                Oferta válida para novos cadastros realizados através desta indicação.
            </p>
        </div>

        <?php
        $smartScriptPayload = convite_smart_script_payload($ref, $indicadorUsuarioId);
        ?>
        <script>
            window.__CONVITE_AF__ = <?= json_encode($smartScriptPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        </script>
    <?php endif; ?>
</section>
