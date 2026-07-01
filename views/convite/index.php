<?php declare(strict_types=1); ?>
<?php
$indicadorUsuarioId = '';
$smartScriptPayload = ['enabled' => false, 'scriptUrl' => ''];

if ($valid && $ref !== '') {
    $indicadorRow = (new Usuario())->findByCodigo($ref);
    $indicadorUsuarioId = $indicadorRow ? (string) ($indicadorRow['id'] ?? '') : '';
}
?>
<div class="convite-landing__curve" aria-hidden="true"></div>

<section class="convite-page convite-landing">
    <?php if (!$valid): ?>
        <div class="convite-card convite-landing__card convite-landing__card--error">
            <div class="convite-logo convite-landing__logo">
                <img
                    src="<?= e(brand_logo_url()) ?>"
                    alt="Minas Mais Drogaria e Perfumaria"
                    class="convite-landing__logo-img"
                    width="220"
                    height="56"
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
                    width="220"
                    height="56"
                    loading="eager"
                >
            </div>

            <p class="convite-badge convite-landing__badge">🎁 Você foi indicado!</p>

            <h1 class="convite-title convite-landing__hero-title">
                <?php if ($indicadorPrimeiroNome !== ''): ?>
                    <span class="convite-highlight convite-landing__hero-name"><?= e($indicadorPrimeiroNome) ?></span>
                    <span class="convite-landing__hero-line">quer dividir um</span>
                    <span class="convite-landing__hero-line">benefício com você!</span>
                <?php else: ?>
                    <span class="convite-landing__hero-line">Alguém especial quer dividir um</span>
                    <span class="convite-landing__hero-line">benefício com você!</span>
                <?php endif; ?>
            </h1>

            <div class="convite-landing__benefits">
                <div class="convite-landing__divider convite-landing__divider--neutral">
                    <span class="convite-landing__divider-line" aria-hidden="true"></span>
                    <h2 id="convite-benefits-title" class="convite-landing__section-title">Você ganha</h2>
                    <span class="convite-landing__divider-line" aria-hidden="true"></span>
                </div>

                <div class="benefits-grid convite-landing__benefit-list">
                    <article class="benefit-card convite-landing__benefit-item" data-animate="benefit">
                        <div class="convite-landing__benefit-emoji" aria-hidden="true">🏷</div>
                        <h3 class="benefit-title convite-landing__benefit-title">5% OFF</h3>
                        <p class="benefit-description convite-landing__benefit-desc">na primeira compra</p>
                    </article>
                    <article class="benefit-card convite-landing__benefit-item" data-animate="benefit">
                        <div class="convite-landing__benefit-emoji" aria-hidden="true">🛵</div>
                        <h3 class="benefit-title convite-landing__benefit-title">Entrega rápida</h3>
                        <p class="benefit-description convite-landing__benefit-desc">em até 30 minutos</p>
                    </article>
                    <article class="benefit-card convite-landing__benefit-item" data-animate="benefit">
                        <div class="convite-landing__benefit-emoji" aria-hidden="true">⭐</div>
                        <h3 class="benefit-title convite-landing__benefit-title">Promoções exclusivas</h3>
                        <p class="benefit-description convite-landing__benefit-desc">no aplicativo</p>
                    </article>
                </div>
            </div>

            <div class="convite-landing__steps">
                <div class="convite-landing__divider convite-landing__divider--accent">
                    <span class="convite-landing__divider-line" aria-hidden="true"></span>
                    <h2 id="convite-steps-title" class="convite-landing__section-title convite-landing__section-title--accent">Como funciona?</h2>
                    <span class="convite-landing__divider-line" aria-hidden="true"></span>
                </div>

                <div class="stepper convite-stepper">
                    <div class="step convite-stepper__step" data-animate="step">
                        <div class="convite-stepper__track">
                            <span class="step-number convite-stepper__index">1</span>
                            <span class="convite-stepper__connector" aria-hidden="true"></span>
                        </div>
                        <div class="step-content convite-stepper__content">
                            <span class="convite-stepper__icon" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3v10m0 0l4-4m-4 4l-4-4M5 19h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div class="convite-stepper__copy">
                                <h3 class="convite-stepper__title">Baixe o aplicativo</h3>
                                <p class="convite-stepper__desc">Clique no botão abaixo para baixar o app.</p>
                            </div>
                        </div>
                    </div>
                    <div class="step convite-stepper__step" data-animate="step">
                        <div class="convite-stepper__track">
                            <span class="step-number convite-stepper__index">2</span>
                            <span class="convite-stepper__connector" aria-hidden="true"></span>
                        </div>
                        <div class="step-content convite-stepper__content">
                            <span class="convite-stepper__icon" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 21a8 8 0 10-16 0M12 11a4 4 0 100-8 4 4 0 000 8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div class="convite-stepper__copy">
                                <h3 class="convite-stepper__title"><strong>Faça seu cadastro</strong></h3>
                                <p class="convite-stepper__desc">Cadastre-se utilizando seus dados.</p>
                            </div>
                        </div>
                    </div>
                    <div class="step convite-stepper__step" data-animate="step">
                        <div class="convite-stepper__track">
                            <span class="step-number convite-stepper__index">3</span>
                        </div>
                        <div class="step-content convite-stepper__content">
                            <span class="convite-stepper__icon" aria-hidden="true">🎁</span>
                            <div class="convite-stepper__copy">
                                <h3 class="convite-stepper__title">Seu desconto será liberado automaticamente</h3>
                                <p class="convite-stepper__desc">Finalize seu cadastro e aproveite seu benefício.</p>
                            </div>
                        </div>
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
                ⬇ BAIXAR O APP
            </a>

            <p class="convite-footer convite-landing__footnote">
                <span class="convite-landing__footnote-icon" aria-hidden="true">🛡</span>
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
