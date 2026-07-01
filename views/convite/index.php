<?php declare(strict_types=1); ?>
<?php
$indicadorUsuarioId = '';
$smartScriptPayload = ['enabled' => false, 'scriptUrl' => ''];

if ($valid && $ref !== '') {
    $indicadorRow = (new Usuario())->findByCodigo($ref);
    $indicadorUsuarioId = $indicadorRow ? (string) ($indicadorRow['id'] ?? '') : '';
}
?>
<section class="convite-landing animate-slide">
    <?php if (!$valid): ?>
        <div class="convite-landing__card convite-landing__card--error">
            <div class="convite-landing__logo">
                <img
                    src="<?= e(brand_logo_url()) ?>"
                    alt="Minas Mais Drogaria e Perfumaria"
                    class="convite-landing__logo-img"
                    width="180"
                    height="48"
                    loading="eager"
                >
            </div>
            <h1 class="convite-landing__title convite-landing__title--error">Convite indisponível</h1>
            <p class="convite-landing__text"><?= e($error) ?></p>
            <a href="<?= url('/') ?>" class="convite-landing__cta convite-landing__cta--secondary">Voltar ao início</a>
        </div>
    <?php else: ?>
        <div class="convite-landing__card">
            <div class="convite-landing__logo">
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

            <p class="convite-landing__badge">🎁 Você foi indicado!</p>

            <h1 class="convite-landing__title">
                <?php if ($indicadorPrimeiroNome !== ''): ?>
                    <span class="convite-landing__referrer"><?= e($indicadorPrimeiroNome) ?></span>
                    quer dividir um benefício com você!
                <?php else: ?>
                    Alguém especial quer dividir um benefício com você!
                <?php endif; ?>
            </h1>

            <section class="convite-landing__benefits" aria-labelledby="convite-benefits-title">
                <h2 id="convite-benefits-title" class="convite-landing__section-title">Você ganha:</h2>
                <ul class="convite-landing__benefit-list">
                    <li class="convite-landing__benefit-item">
                        <span class="convite-landing__benefit-icon" aria-hidden="true">✔</span>
                        <span>5% OFF na primeira compra</span>
                    </li>
                    <li class="convite-landing__benefit-item">
                        <span class="convite-landing__benefit-icon" aria-hidden="true">✔</span>
                        <span>Entrega rápida em até 30 minutos</span>
                    </li>
                    <li class="convite-landing__benefit-item">
                        <span class="convite-landing__benefit-icon" aria-hidden="true">✔</span>
                        <span>Promoções exclusivas no aplicativo</span>
                    </li>
                </ul>
            </section>

            <section class="convite-landing__steps" aria-labelledby="convite-steps-title">
                <h2 id="convite-steps-title" class="convite-landing__section-title">Como funciona?</h2>
                <ol class="convite-stepper">
                    <li class="convite-stepper__step">
                        <span class="convite-stepper__index" aria-hidden="true">①</span>
                        <span class="convite-stepper__label">Baixe o aplicativo</span>
                    </li>
                    <li class="convite-stepper__step">
                        <span class="convite-stepper__index" aria-hidden="true">②</span>
                        <span class="convite-stepper__label"><strong>Faça seu cadastro</strong></span>
                    </li>
                    <li class="convite-stepper__step">
                        <span class="convite-stepper__index" aria-hidden="true">③</span>
                        <span class="convite-stepper__label">Seu desconto será liberado automaticamente</span>
                    </li>
                </ol>
            </section>

            <a
                id="convite-download-btn"
                href="<?= e($appDownloadUrl) ?>"
                class="convite-landing__cta"
                data-fallback-url="<?= e($appDownloadUrl) ?>"
                <?= $appDownloadUrl === '#' ? 'aria-disabled="true"' : 'target="_blank" rel="noopener noreferrer"' ?>
            >
                BAIXAR O APP
            </a>

            <p class="convite-landing__footnote">
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
